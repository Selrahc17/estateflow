<?php

namespace App\Http\Controllers;

use App\Mail\ReservationConfirmed;
use App\Models\Reservation;
use App\Models\Property;
use App\Models\Client;
use App\Models\Agent;
use App\Services\DocumentCheckerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

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
            ? Property::find($request->property_id)
            : null;

        // If logged-in user is a client, find their client record
        $myClientRecord = null;
        if (auth()->user()->isClient()) {
            $myClientRecord = Client::where('user_id', auth()->id())->first();
        }

        return view('reservations.create', compact(
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
            'agent_id'         => 'nullable|exists:agents,id',
            'reservation_date' => 'required|date',
            'expiry_date'      => 'nullable|date|after:reservation_date',
            'reservation_fee'  => 'nullable|numeric|min:0',
            'status'           => 'required|in:pending,confirmed,cancelled,expired,completed',
            'notes'            => 'nullable|string',
        ]);

        $reservation = Reservation::create($request->all());

        if ($request->status === 'confirmed') {
            $reservation->property->update(['status' => 'reserved']);
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
        $reservation->load(['property', 'client', 'agent', 'payments']);
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
                'status' => 'required|in:pending,confirmed,cancelled,expired,completed',
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

    public function destroy(Reservation $reservation)
    {
        $reservation->delete();
        return back()->with('success', 'Reservation deleted successfully.');
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

        $request->validate([
            'status' => 'required|in:pending,confirmed,cancelled,expired,completed',
        ]);

        $oldStatus = $reservation->status;
        $reservation->update(['status' => $request->status]);

        if ($request->status === 'confirmed' && $oldStatus !== 'confirmed') {
            $reservation->property->update(['status' => 'reserved']);
            $reservation->load(['client', 'property', 'agent']);
            $clientUser = \App\Models\User::find($reservation->client->user_id ?? null);
            if ($clientUser?->email) {
                try {
                    Mail::to($clientUser->email)->send(new ReservationConfirmed($reservation));
                } catch (\Exception $e) {
                    // Mail failure should not block the status update
                }
            }
        } elseif (in_array($request->status, ['cancelled', 'expired']) && $oldStatus === 'confirmed') {
            $reservation->property->update(['status' => 'available']);
        } elseif ($request->status === 'completed' && $oldStatus !== 'completed') {
            $reservation->property->update(['status' => 'sold']);
            // Auto-set Pag-IBIG to released if it was approved
            if (in_array($reservation->pagibig_status, ['approved', 'verified', 'applied'])) {
                $reservation->update(['pagibig_status' => 'released']);
            }
        }

        return back()->with('success', 'Reservation marked as ' . ucfirst($request->status) . '.');
    }
}
