<?php

namespace App\Http\Controllers;

use App\Mail\ReservationConfirmed;
use App\Mail\AppointmentScheduled;
use App\Models\Reservation;
use App\Models\Property;
use App\Models\Client;
use App\Models\Agent;
use App\Models\EstateNotification;
use App\Models\User;
use App\Services\DocumentCheckerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class ReservationController extends Controller
{
    public function index(Request $request)
    {
        $query = Reservation::with(['property', 'client', 'agent']);

        if ($request->filled('search')) {
            $query->whereHas('client', function ($q) use ($request) {
                $q->where('first_name', 'like', '%' . $request->search . '%')
                  ->orWhere('last_name', 'like', '%' . $request->search . '%');
            })->orWhereHas('property', function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $reservations       = $query->latest()->paginate(15)->withQueryString();
        $totalReservations  = Reservation::count();
        $pendingCount       = Reservation::where('status', 'pending')->count();
        $confirmedCount     = Reservation::where('status', 'confirmed')->count();
        $completedCount     = Reservation::where('status', 'completed')->count();

        return view('reservations.index', compact(
            'reservations', 'totalReservations', 'pendingCount', 'confirmedCount', 'completedCount'
        ));
    }

    public function create(Request $request)
    {
        $properties = Property::where('status', 'available')->get();
        $clients    = Client::where('status', 'active')->get();
        $agents     = Agent::where('status', 'active')->get();

        // Pre-selected property
        $selectedProperty = $request->filled('property_id')
            ? Property::with('propertyType')->find($request->property_id)
            : null;

        // If logged-in user is a client, find their client record
        $myClientRecord = null;
        if (auth()->user()->isClient()) {
            $myClientRecord = Client::where('user_id', auth()->id())->first();
        }

        $view = auth()->user()->isClient() ? 'reservations.create-client' : 'reservations.create';

        return view($view, compact(
            'properties', 'clients', 'agents', 'selectedProperty', 'myClientRecord'
        ));
    }

    public function store(Request $request)
    {
        // If client is submitting, force their own client_id
        if (auth()->user()->isClient()) {
            $clientRecord = Client::where('user_id', auth()->id())->first();
            if (!$clientRecord) {
                return back()->with('error', 'Your client profile is not set up. Please contact an administrator.');
            }
            $request->merge([
                'client_id' => $clientRecord->id,
                'status'    => 'pending', // client reservations always start as pending
            ]);
        }

        $request->validate([
            'property_id'      => 'required|exists:properties,id',
            'client_id'        => 'required|exists:clients,id',
            'agent_id'         => auth()->user()->isClient() ? 'required|exists:agents,id' : 'nullable|exists:agents,id',
            'reservation_date' => 'required|date',
            'expiry_date'      => 'nullable|date|after:reservation_date',
            'reservation_fee'  => 'nullable|numeric|min:0',
            'status'           => 'required|in:pending,confirmed,cancelled,expired,completed',
            'notes'            => 'nullable|string',
            'payment_scheme'   => 'required|in:cash_bank,pagibig',
            'employment_type'  => 'required_if:payment_scheme,pagibig|nullable|in:locally_employed,locally_employed_coborrower,self_employed,ofw,ofw_coborrower',
            'coborrower_name'         => 'required_if:employment_type,locally_employed_coborrower,ofw_coborrower|nullable|string|max:255',
            'coborrower_relationship' => 'required_if:employment_type,locally_employed_coborrower,ofw_coborrower|nullable|string|max:255',
            'coborrower_contact'      => 'required_if:employment_type,locally_employed_coborrower,ofw_coborrower|nullable|string|max:50',
        ]);

        $reservation = Reservation::create($request->only([
            'property_id', 'client_id', 'agent_id', 'reservation_date',
            'expiry_date', 'reservation_fee', 'status', 'notes',
            'payment_scheme', 'employment_type',
            'coborrower_name', 'coborrower_relationship', 'coborrower_contact',
        ]));

        if ($request->status === 'confirmed') {
            $reservation->property->update(['status' => 'reserved']);
        }

        // Send appointment scheduled email + in-app notification
        $reservation->load(['client.user', 'property', 'agent']);
        $clientUser = $reservation->client?->user;
        if ($clientUser?->email) {
            try {
                Mail::to($clientUser->email)->send(new AppointmentScheduled($reservation));
                Log::info("Appointment scheduled email sent to {$clientUser->email} for Reservation #{$reservation->id}");
            } catch (\Exception $e) {
                Log::error("Failed to send appointment email: " . $e->getMessage());
            }

            EstateNotification::create([
                'notifiable_type' => User::class,
                'notifiable_id'   => $clientUser->id,
                'type'            => 'appointment_scheduled',
                'data'            => [
                    'title'   => 'Appointment Scheduled',
                    'message' => "Your viewing appointment for {$reservation->property->title} is scheduled on {$reservation->reservation_date->format('F j, Y')}.",
                ],
                'priority' => 'high',
                'is_read'  => false,
            ]);
        }

        // Redirect client back to their reservations page
        if (auth()->user()->isClient()) {
            return redirect()->route('client.reservations')
                ->with('success', 'Your reservation request has been submitted! An agent will confirm it shortly.');
        }

        return redirect()->route('reservations.index')->with('success', 'Reservation created successfully.');
    }

    public function show(Reservation $reservation)
    {
        $reservation->load(['property', 'client', 'agent', 'payments', 'paymentSchedules', 'commission']);
        $docCheck = DocumentCheckerService::check($reservation);
        return view('reservations.show', compact('reservation', 'docCheck'));
    }

    public function edit(Reservation $reservation)
    {
        // Agent can only edit their own reservations
        if (auth()->user()->isAgent()) {
            $agentRecord = Agent::where('user_id', auth()->id())->first();
            if (!$agentRecord || $reservation->agent_id !== $agentRecord->id) {
                abort(403, 'You can only edit your own reservations.');
            }
        }

        $properties = Property::whereIn('status', ['available', 'reserved'])->get();
        $clients    = Client::where('status', 'active')->get();
        $agents     = Agent::where('status', 'active')->get();
        return view('reservations.edit', compact('reservation', 'properties', 'clients', 'agents'));
    }

    public function update(Request $request, Reservation $reservation)
    {
        // Agent can only update their own reservations
        if (auth()->user()->isAgent()) {
            $agentRecord = Agent::where('user_id', auth()->id())->first();
            if (!$agentRecord || $reservation->agent_id !== $agentRecord->id) {
                abort(403, 'You can only edit your own reservations.');
            }

            // Agent can only change status and notes
            $request->validate([
                'status' => 'required|in:pending,confirmed,expired,cancelled',
                'notes'  => 'nullable|string',
            ]);

            $oldStatus = $reservation->status;
            $reservation->update($request->only('status', 'notes'));

        } else {
            $request->validate([
                'property_id'      => 'required|exists:properties,id',
                'client_id'        => 'required|exists:clients,id',
                'agent_id'         => 'nullable|exists:agents,id',
                'reservation_date' => 'required|date',
                'expiry_date'      => 'nullable|date',
                'reservation_fee'  => 'nullable|numeric|min:0',
                'status'           => 'required|in:pending,confirmed,cancelled,expired,completed',
                'notes'            => 'nullable|string',
            ]);

            $oldStatus = $reservation->status;
            $reservation->update($request->all());
        }

        // Update property status based on reservation status change
        if ($request->status === 'confirmed' && $oldStatus !== 'confirmed') {
            $reservation->property->update(['status' => 'reserved']);
            // Mark viewing as verified when confirmed
            if (in_array($reservation->viewing_status, ['payment_uploaded'])) {
                $reservation->update(['viewing_status' => 'verified']);
            }

            // Send confirmation email to client
            $reservation->load(['client', 'property', 'agent']);
            $clientUser = \App\Models\User::find($reservation->client->user_id ?? null);
            if ($clientUser?->email) {
                try {
                    Mail::to($clientUser->email)->send(new ReservationConfirmed($reservation));
                } catch (\Exception $e) {
                    // Mail failure should not block the update
                }
            }
        } elseif (in_array($request->status, ['cancelled', 'expired']) && $oldStatus === 'confirmed') {
            $reservation->property->update(['status' => 'available']);
        } elseif ($request->status === 'completed') {
            $reservation->property->update(['status' => 'sold']);
        }

        // Redirect agent back to their scoped reservations page
        if (auth()->user()->isAgent()) {
            return redirect()->route('agent.reservations')->with('success', 'Reservation updated successfully.');
        }

        return redirect()->route('reservations.index')->with('success', 'Reservation updated successfully.');
    }

    public function markViewed(Reservation $reservation)
    {
        $reservation->update([
            'viewing_status'   => 'viewed',
            'viewed_at'        => now(),
            'reservation_fee'  => 10000,
        ]);

        // Notify client that viewing is done and they can now upload proof of payment
        $clientUser = $reservation->client?->user;
        if ($clientUser) {
            EstateNotification::create([
                'notifiable_type' => User::class,
                'notifiable_id'   => $clientUser->id,
                'type'            => 'viewing_completed',
                'data'            => [
                    'title'   => 'Viewing Completed — Upload Proof of Payment',
                    'message' => "Your viewing for {$reservation->property->title} has been marked as completed. You may now upload your Proof of Payment to proceed with the reservation.",
                ],
                'priority' => 'high',
                'is_read'  => false,
            ]);
        }

        return back()->with('success', 'Appointment marked as viewed. Client has been notified to upload proof of payment.');
    }

    public function uploadProof(Request $request, Reservation $reservation)
    {
        // Only the client who owns this reservation can upload
        $clientRecord = Client::where('user_id', auth()->id())->first();
        if (!$clientRecord || $reservation->client_id !== $clientRecord->id) {
            abort(403);
        }

        if ($reservation->viewing_status !== 'viewed') {
            return back()->with('error', 'You can only upload proof of payment after your viewing appointment is completed.');
        }

        $request->validate([
            'proof_of_payment' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        $path = $request->file('proof_of_payment')->store('proofs', 'public');

        $reservation->update([
            'proof_of_payment'    => $path,
            'viewing_status'      => 'payment_uploaded',
            'payment_uploaded_at' => now(),
        ]);

        Log::info("Proof of payment uploaded for Reservation #{$reservation->id} by Client #{$clientRecord->id}");

        // Notify admin/finance
        $admins = User::whereIn('role', ['admin', 'finance'])->where('is_active', true)->get();
        foreach ($admins as $admin) {
            EstateNotification::create([
                'notifiable_type' => User::class,
                'notifiable_id'   => $admin->id,
                'type'            => 'proof_uploaded',
                'data'            => [
                    'title'   => 'Proof of Payment Uploaded',
                    'message' => "{$reservation->client->full_name} uploaded proof of payment for {$reservation->property->title}. Please verify.",
                ],
                'priority' => 'high',
                'is_read'  => false,
            ]);
        }

        return back()->with('success', 'Proof of payment uploaded successfully. Our team will verify it shortly.');
    }

    public function destroy(Reservation $reservation)
    {
        $reservation->delete();
        return back()->with('success', 'Reservation deleted successfully.');
    }

    // Client uploads a document for a specific checklist item
    public function uploadChecklistItem(Request $request, Reservation $reservation, int $index)
    {
        $clientRecord = Client::where('user_id', auth()->id())->first();
        if (!$clientRecord || $reservation->client_id !== $clientRecord->id) {
            abort(403);
        }

        if ($reservation->status !== 'reservation_paid') {
            return back()->with('error', 'Documents can only be uploaded after your Reservation Fee is verified.');
        }

        $request->validate([
            'document' => 'required|file|mimes:jpg,jpeg,png,pdf|max:10240',
        ]);

        $checklist = $reservation->document_checklist ?? [];

        if (!isset($checklist[$index])) {
            return back()->with('error', 'Invalid document item.');
        }

        $path = $request->file('document')->store('checklist/' . $reservation->id, 'public');

        $checklist[$index]['uploaded']         = true;
        $checklist[$index]['file_path']        = $path;
        $checklist[$index]['verified']         = false;
        $checklist[$index]['rejected']         = false;
        $checklist[$index]['rejection_reason'] = null;

        $reservation->update(['document_checklist' => $checklist]);

        // Notify admin
        $admins = User::where('role', 'admin')->where('is_active', true)->get();
        foreach ($admins as $admin) {
            EstateNotification::create([
                'notifiable_type' => User::class,
                'notifiable_id'   => $admin->id,
                'type'            => 'checklist_document_uploaded',
                'data'            => [
                    'title'   => 'Document Uploaded — ' . $reservation->client->full_name,
                    'message' => $reservation->client->full_name . ' uploaded "' . $checklist[$index]['label'] . '" for ' . $reservation->property->title . '.',
                ],
                'priority' => 'normal',
                'is_read'  => false,
            ]);
        }

        return back()->with('success', '"' . $checklist[$index]['label'] . '" uploaded successfully.');
    }

    // Admin rejects a checklist item with a reason
    public function rejectChecklistItem(Request $request, Reservation $reservation, int $index)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $checklist = $reservation->document_checklist ?? [];
        if (!isset($checklist[$index])) {
            return back()->with('error', 'Invalid document item.');
        }

        $checklist[$index]['verified']         = false;
        $checklist[$index]['rejected']         = true;
        $checklist[$index]['rejection_reason'] = $request->rejection_reason;
        $checklist[$index]['uploaded']         = false;
        $checklist[$index]['file_path']        = null;

        $reservation->update(['document_checklist' => $checklist]);

        $clientUser = $reservation->client?->user;
        if ($clientUser) {
            EstateNotification::create([
                'notifiable_type' => User::class,
                'notifiable_id'   => $clientUser->id,
                'type'            => 'checklist_document_rejected',
                'data'            => [
                    'title'   => 'Document Rejected — Resubmission Required',
                    'message' => '"' . $checklist[$index]['label'] . '" for ' . $reservation->property->title . ' was rejected. Reason: ' . $request->rejection_reason,
                ],
                'priority' => 'high',
                'is_read'  => false,
            ]);
        }

        return back()->with('success', '"' . $checklist[$index]['label'] . '" rejected. Client has been notified.');
    }

    // Client marks a conditional document as Not Applicable
    public function markChecklistNotApplicable(Request $request, Reservation $reservation, int $index)
    {
        $clientRecord = Client::where('user_id', auth()->id())->first();
        if (!$clientRecord || $reservation->client_id !== $clientRecord->id) {
            abort(403);
        }

        $request->validate([
            'na_reason' => 'required|string|max:300',
        ]);

        $checklist = $reservation->document_checklist ?? [];
        if (!isset($checklist[$index])) {
            return back()->with('error', 'Invalid document item.');
        }

        if (!($checklist[$index]['conditional'] ?? false)) {
            return back()->with('error', 'This document cannot be marked as Not Applicable.');
        }

        $checklist[$index]['not_applicable'] = true;
        $checklist[$index]['na_reason']      = $request->na_reason;
        $checklist[$index]['uploaded']       = false;
        $checklist[$index]['verified']       = false;

        $reservation->update(['document_checklist' => $checklist]);

        // Notify admin to review the N/A claim
        $admins = User::where('role', 'admin')->where('is_active', true)->get();
        foreach ($admins as $admin) {
            EstateNotification::create([
                'notifiable_type' => User::class,
                'notifiable_id'   => $admin->id,
                'type'            => 'checklist_not_applicable',
                'data'            => [
                    'title'   => 'Document Marked N/A — ' . $reservation->client->full_name,
                    'message' => $reservation->client->full_name . ' marked "' . $checklist[$index]['label'] . '" as Not Applicable. Reason: ' . $request->na_reason,
                ],
                'priority' => 'normal',
                'is_read'  => false,
            ]);
        }

        return back()->with('success', '"' . $checklist[$index]['label'] . '" marked as Not Applicable.');
    }

    // Client cancels their own reservation (before reservation_paid only)
    public function clientCancel(Request $request, Reservation $reservation)
    {
        $clientRecord = Client::where('user_id', auth()->id())->first();
        if (!$clientRecord || $reservation->client_id !== $clientRecord->id) {
            abort(403);
        }

        if (!in_array($reservation->status, ['pending', 'confirmed'])) {
            return back()->with('error', 'You can only cancel a reservation before the Reservation Fee is verified.');
        }

        if ($reservation->property) {
            $reservation->property->update(['status' => 'available']);
        }

        $reservation->update([
            'status'              => 'cancelled',
            'cancelled_at'        => now(),
            'cancellation_type'   => 'client_backout',
            'cancellation_reason' => 'Client requested cancellation.',
        ]);

        // Notify admins
        $admins = User::whereIn('role', ['admin', 'agent'])->where('is_active', true)->get();
        foreach ($admins as $admin) {
            EstateNotification::create([
                'notifiable_type' => User::class,
                'notifiable_id'   => $admin->id,
                'type'            => 'reservation_client_cancelled',
                'data'            => [
                    'title'   => 'Reservation Cancelled by Client',
                    'message' => ($reservation->client->full_name ?? 'Client') . ' cancelled their reservation for ' . ($reservation->property->title ?? '') . '.',
                ],
                'priority' => 'high',
                'is_read'  => false,
            ]);
        }

        return redirect()->route('client.reservations')
            ->with('success', 'Your reservation has been cancelled.');
    }

    // Admin verifies a checklist item
    public function verifyChecklistItem(Request $request, Reservation $reservation, int $index)
    {
        $checklist = $reservation->document_checklist ?? [];

        if (!isset($checklist[$index])) {
            return back()->with('error', 'Invalid document item.');
        }

        $checklist[$index]['verified'] = true;

        $reservation->update(['document_checklist' => $checklist]);

        // Notify client
        $clientUser = $reservation->client?->user;
        if ($clientUser) {
            EstateNotification::create([
                'notifiable_type' => User::class,
                'notifiable_id'   => $clientUser->id,
                'type'            => 'checklist_document_verified',
                'data'            => [
                    'title'   => 'Document Verified',
                    'message' => '"' . $checklist[$index]['label'] . '" for ' . $reservation->property->title . ' has been verified.',
                ],
                'priority' => 'normal',
                'is_read'  => false,
            ]);
        }

        return back()->with('success', '"' . $checklist[$index]['label'] . '" verified.');
    }

    public function updatePagibig(Request $request, Reservation $reservation)
    {
        $user = auth()->user();

        if ($user->isAgent()) {
            $agentRecord = Agent::where('user_id', $user->id)->first();
            if (!$agentRecord || $reservation->agent_id !== $agentRecord->id) {
                return back()->with('error', 'You can only update reservations assigned to you.');
            }
        }

        $request->validate([
            'pagibig_status'    => 'required|in:not_applied,applied,verified,approved,released',
            'pagibig_reference' => 'nullable|string|max:100',
        ]);

        $reservation->update([
            'pagibig_status'    => $request->pagibig_status,
            'pagibig_reference' => $request->pagibig_reference,
        ]);

        // Notify finance when Pag-IBIG is approved — payments are incoming
        if ($request->pagibig_status === 'approved') {
            $financeUsers = \App\Models\User::where('role', 'finance')->where('is_active', true)->get();
            foreach ($financeUsers as $financeUser) {
                \App\Models\EstateNotification::create([
                    'notifiable_type' => \App\Models\User::class,
                    'notifiable_id'   => $financeUser->id,
                    'type'            => 'pagibig_approved',
                    'title'           => 'Pag-IBIG Approved — ' . ($reservation->client->full_name ?? 'Client'),
                    'message'         => 'Pag-IBIG loan for ' . ($reservation->property->title ?? 'a property') . ' has been approved. Payments from Pag-IBIG are now expected.',
                    'data'            => json_encode(['reservation_id' => $reservation->id]),
                ]);
            }
        }

        return back()->with('success', 'Pag-IBIG status updated successfully.');
    }

    public function updateStatus(Request $request, Reservation $reservation)
    {
        $user = auth()->user();

        // Agent ownership check
        if ($user->isAgent()) {
            $agentRecord = Agent::where('user_id', $user->id)->first();
            if (!$agentRecord || $reservation->agent_id !== $agentRecord->id) {
                return back()->with('error', 'You can only update reservations assigned to you.');
            }
        }

        // Agents can change status to confirmed, expired, cancelled, or mark viewing as viewed
        if ($user->isAgent()) {
            $request->validate([
                'status'              => 'required|in:pending,confirmed,viewed,expired,cancelled',
                'cancellation_reason' => 'required_if:status,cancelled|nullable|string|max:500',
            ]);

            // 'viewed' triggers the markViewed logic, not a real reservation status
            if ($request->status === 'viewed') {
                $reservation->update([
                    'viewing_status'  => 'viewed',
                    'viewed_at'       => now(),
                    'reservation_fee' => 10000,
                ]);
                $clientUser = $reservation->client?->user;
                if ($clientUser) {
                    EstateNotification::create([
                        'notifiable_type' => User::class,
                        'notifiable_id'   => $clientUser->id,
                        'type'            => 'viewing_completed',
                        'data'            => [
                            'title'   => 'Viewing Completed — Upload Proof of Payment',
                            'message' => "Your viewing for {$reservation->property->title} has been marked as completed. You may now upload your Proof of Payment to proceed with the reservation.",
                        ],
                        'priority' => 'high',
                        'is_read'  => false,
                    ]);
                }
                return back()->with('success', 'Appointment marked as viewed. Client has been notified to upload proof of payment.');
            }
        } else {
            $request->validate([
                'status'              => 'required|in:pending,confirmed,reservation_paid,pagibig_applied,pagibig_approved,pagibig_takeout,pagibig_amortization,cancelled,expired,completed',
                'cancellation_reason' => 'required_if:status,cancelled|nullable|string|max:500',
            ]);
        }

        // Guards below only apply to admin/finance — agents handle pre-payment reservations only
        if (!$user->isAgent()) {
            // Guard: cannot cancel after reservation_paid
            if ($request->status === 'cancelled' && in_array($reservation->status, [
                'reservation_paid', 'pagibig_applied', 'pagibig_approved',
                'pagibig_takeout', 'pagibig_amortization', 'completed',
            ])) {
                return back()->with('error', 'Cancellation is not allowed after the Reservation Fee has been verified.');
            }

            // Guard: Pag-IBIG buyers can only be completed after amortization is started
            if ($request->status === 'completed' && $reservation->payment_scheme === 'pagibig') {
                if ($reservation->pagibig_loan_status !== 'amortization') {
                    return back()->with('error', 'Pag-IBIG reservations can only be completed after amortization has started.');
                }
            }

            // Guard: Cash/Bank buyers can only be completed after payment schedule is fully paid
            if ($request->status === 'completed' && $reservation->payment_scheme === 'cash_bank') {
                $reservation->load('paymentSchedules');
                $schedules = $reservation->paymentSchedules;
                if ($schedules->count() > 0 && !$schedules->every(fn($s) => $s->status === 'paid')) {
                    return back()->with('error', 'Cash reservations can only be completed after all installments are fully paid.');
                }
            }
        }

        $oldStatus = $reservation->status;

        $updateData = ['status' => $request->status];
        if ($request->status === 'cancelled') {
            $updateData['cancelled_at']        = now();
            $updateData['cancellation_reason'] = $request->cancellation_reason;
            $updateData['cancellation_type']   = 'manual_admin';
        }
        $reservation->update($updateData);

        if ($request->status === 'confirmed' && $oldStatus !== 'confirmed') {
            $reservation->property->update(['status' => 'reserved']);
            $reservation->load(['client', 'property', 'agent']);
            $clientUser = \App\Models\User::find($reservation->client->user_id ?? null);
            if ($clientUser?->email) {
                try {
                    Mail::to($clientUser->email)->send(new ReservationConfirmed($reservation));
                } catch (\Exception $e) {}
            }
        } elseif (in_array($request->status, ['cancelled', 'expired']) && !in_array($oldStatus, ['completed', 'cancelled', 'expired'])) {
            $reservation->property->update(['status' => 'available']);
        } elseif ($request->status === 'completed' && $oldStatus !== 'completed') {
            $reservation->property->update(['status' => 'sold']);
            if (in_array($reservation->pagibig_status, ['approved', 'verified', 'applied'])) {
                $reservation->update(['pagibig_status' => 'released']);
            }
        }

        return back()->with('success', 'Reservation marked as ' . ucfirst(str_replace('_', ' ', $request->status)) . '.');
    }

    // Agent/Admin sets RF deadline after viewing is done
    public function setRfDeadline(Request $request, Reservation $reservation)
    {
        $request->validate([
            'rf_deadline' => 'required|date|after:today',
        ]);

        $reservation->update(['rf_deadline' => $request->rf_deadline]);

        // Notify client
        $clientUser = $reservation->client?->user;
        if ($clientUser) {
            EstateNotification::create([
                'notifiable_type' => User::class,
                'notifiable_id'   => $clientUser->id,
                'type'            => 'rf_deadline_set',
                'data'            => [
                    'title'   => 'Reservation Fee Deadline Set',
                    'message' => "Please pay your Reservation Fee for {$reservation->property->title} on or before " . $reservation->rf_deadline->format('F j, Y') . '.',
                ],
                'priority' => 'high',
                'is_read'  => false,
            ]);
        }

        return back()->with('success', 'RF deadline set to ' . $reservation->rf_deadline->format('M d, Y') . '.');
    }

    public function updateCoborrower(Request $request, Reservation $reservation)
    {
        $request->validate([
            'coborrower_monthly_income'  => 'nullable|numeric|min:0',
            'coborrower_id_type'         => 'nullable|string|max:100',
            'coborrower_id_number'       => 'nullable|string|max:100',
            'coborrower_id_expiry'       => 'nullable|date',
            'coborrower_hdmf_mid'        => 'nullable|string|max:20',
            'coborrower_employment_type' => 'nullable|string|max:100',
        ]);

        $reservation->update($request->only([
            'coborrower_monthly_income', 'coborrower_id_type', 'coborrower_id_number',
            'coborrower_id_expiry', 'coborrower_hdmf_mid', 'coborrower_employment_type',
        ]));

        return back()->with('success', 'Co-borrower details updated.');
    }

    public function updateInsurance(Request $request, Reservation $reservation)
    {
        $request->validate([
            'mri_premium'                    => 'nullable|numeric|min:0',
            'mri_policy_number'              => 'nullable|string|max:100',
            'mri_expiry'                     => 'nullable|date',
            'fire_insurance_premium'         => 'nullable|numeric|min:0',
            'fire_insurance_policy_number'   => 'nullable|string|max:100',
            'fire_insurance_expiry'          => 'nullable|date',
        ]);

        $reservation->update($request->only([
            'mri_premium', 'mri_policy_number', 'mri_expiry',
            'fire_insurance_premium', 'fire_insurance_policy_number', 'fire_insurance_expiry',
        ]));

        return back()->with('success', 'Insurance details updated.');
    }

    public function updateRefund(Request $request, Reservation $reservation)
    {
        $request->validate([
            'refund_amount'       => 'nullable|numeric|min:0',
            'refund_status'       => 'required|in:pending,processed,waived',
            'refund_reference'    => 'nullable|string|max:100',
            'refund_processed_at' => 'nullable|date',
        ]);

        $reservation->update($request->only([
            'refund_amount', 'refund_status', 'refund_reference', 'refund_processed_at',
        ]));

        $clientUser = $reservation->client?->user;
        if ($clientUser && $request->refund_status === 'processed') {
            EstateNotification::create([
                'notifiable_type' => User::class,
                'notifiable_id'   => $clientUser->id,
                'type'            => 'refund_processed',
                'data'            => [
                    'title'   => 'Refund Processed',
                    'message' => '₱' . number_format($request->refund_amount, 2) . ' refund for ' . ($reservation->property->title ?? 'your property') . ' has been processed.' . ($request->refund_reference ? ' Ref# ' . $request->refund_reference : ''),
                ],
                'priority' => 'high',
                'is_read'  => false,
            ]);
        }

        return back()->with('success', 'Refund details updated.');
    }

    public function updateLoanReconciliation(Request $request, Reservation $reservation)
    {
        $request->validate([
            'pagibig_loan_amount' => 'nullable|numeric|min:0',
            'equity_amount'       => 'nullable|numeric|min:0',
        ]);

        $reservation->update($request->only(['pagibig_loan_amount', 'equity_amount']));

        return back()->with('success', 'Loan reconciliation updated.');
    }

    // Finance verifies RF payment → issues OR → triggers checklist generation
    public function verifyRf(Request $request, Reservation $reservation)
    {
        $request->validate([
            'rf_or_number' => 'required|string|max:100',
        ]);

        $reservation->update([
            'rf_paid_at'   => now(),
            'rf_or_number' => $request->rf_or_number,
            'status'       => 'reservation_paid',
            'viewing_status' => 'verified',
        ]);

        // Auto-generate document checklist
        $reservation->update([
            'document_checklist' => \App\Services\DocumentChecklistService::generate($reservation),
        ]);

        // Notify client
        $clientUser = $reservation->client?->user;
        if ($clientUser) {
            EstateNotification::create([
                'notifiable_type' => User::class,
                'notifiable_id'   => $clientUser->id,
                'type'            => 'rf_verified',
                'data'            => [
                    'title'   => 'Reservation Fee Verified — Documents Required',
                    'message' => "Your Reservation Fee for {$reservation->property->title} has been verified (OR# {$request->rf_or_number}). Please upload your required documents.",
                ],
                'priority' => 'high',
                'is_read'  => false,
            ]);
        }

        return back()->with('success', 'RF verified. OR# ' . $request->rf_or_number . ' issued. Document checklist generated.');
    }
}
