<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Property;
use App\Models\Client;
use App\Models\Project;
use App\Models\Reservation;
use App\Models\EstateNotification;
use App\Models\User;
use App\Services\DocumentCheckerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    public function index(Request $request)
    {
        $query = Document::with(['verifiedBy', 'documentable']);

        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('document_type', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('document_type')) {
            $query->where('document_type', $request->document_type);
        }

        if ($request->filled('is_verified')) {
            $query->where('is_verified', $request->is_verified === '1');
        }

        if ($request->filled('expiry_status')) {
            if ($request->expiry_status === 'expired') {
                $query->whereNotNull('expiry_date')->where('expiry_date', '<', now());
            } elseif ($request->expiry_status === 'expiring_soon') {
                $query->whereNotNull('expiry_date')
                      ->where('expiry_date', '>=', now())
                      ->where('expiry_date', '<=', now()->addDays(30));
            }
        }

        $documents      = $query->latest()->paginate(15)->withQueryString();
        $totalDocuments = Document::count();
        $verifiedCount  = Document::where('is_verified', true)->count();
        $pendingCount   = Document::where('is_verified', false)->count();
        $expiredCount   = Document::whereNotNull('expiry_date')->where('expiry_date', '<', now())->count();
        $expiringSoon   = Document::whereNotNull('expiry_date')
                            ->where('expiry_date', '>=', now())
                            ->where('expiry_date', '<=', now()->addDays(30))
                            ->count();

        return view('documents.index', compact(
            'documents', 'totalDocuments', 'verifiedCount', 'pendingCount',
            'expiredCount', 'expiringSoon'
        ));
    }

    public function create(Request $request)
    {
        $properties   = Property::where('is_active', true)->get();
        $clients      = Client::where('status', 'active')->get();
        $projects     = Project::orderBy('name')->get();
        $reservations = Reservation::with(['property', 'client'])->get();

        return view('documents.create', compact('properties', 'clients', 'projects', 'reservations'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'             => 'required|string|max:255',
            'document_type'     => 'required|string|max:100',
            'description'       => 'nullable|string',
            'documentable_type' => 'required|in:property,client,project,reservation',
            'documentable_id'   => 'required|integer',
            'file'              => 'required|file|max:10240',
            'expiry_date'       => 'nullable|date',
        ]);

        $file     = $request->file('file');
        $fileName = time() . '_' . $file->getClientOriginalName();
        $filePath = $file->storeAs('documents', $fileName, 'public');

        $morphMap = [
            'property'    => Property::class,
            'client'      => Client::class,
            'project'     => Project::class,
            'reservation' => Reservation::class,
        ];

        Document::create([
            'title'              => $request->title,
            'document_type'      => $request->document_type,
            'description'        => $request->description,
            'expiry_date'        => $request->expiry_date,
            'documentable_type'  => $morphMap[$request->documentable_type],
            'documentable_id'    => $request->documentable_id,
            'file_path'          => $filePath,
            'file_name'          => $file->getClientOriginalName(),
            'file_type'          => $file->getMimeType(),
            'file_size'          => $file->getSize(),
            'is_verified'        => false,
        ]);

        return redirect()->route('documents.index')->with('success', 'Document uploaded successfully.');
    }

    public function show(Document $document)
    {
        $document->load(['documentable', 'verifiedBy']);
        return view('documents.show', compact('document'));
    }

    public function edit(Document $document)
    {
        return view('documents.edit', compact('document'));
    }

    public function update(Request $request, Document $document)
    {
        $request->validate([
            'title'         => 'required|string|max:255',
            'document_type' => 'required|string|max:100',
            'description'   => 'nullable|string',
        ]);

        $document->update($request->only('title', 'document_type', 'description'));

        return redirect()->route('documents.index')->with('success', 'Document updated successfully.');
    }

    public function destroy(Document $document)
    {
        Storage::disk('public')->delete($document->file_path);
        $document->delete();
        return back()->with('success', 'Document deleted successfully.');
    }

    public function verify(Document $document)
    {
        $document->update([
            'is_verified' => true,
            'verified_by' => auth()->id(),
        ]);
        return back()->with('success', 'Document verified successfully.');
    }

    public function download(Document $document)
    {
        return Storage::disk('public')->download($document->file_path, $document->file_name);
    }

    public function clientStore(Request $request)
    {
        $user         = auth()->user();
        $clientRecord = Client::where('user_id', $user->id)->first();

        if (!$clientRecord) {
            return back()->with('error', 'Your account is not linked to a client profile.');
        }

        $request->validate([
            'document_type'  => 'required|string|max:100',
            'title'          => 'required|string|max:255',
            'reservation_id' => 'nullable|exists:reservations,id',
            'file'           => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'expiry_date'    => 'nullable|date',
        ]);

        // If a reservation is selected, verify it belongs to this client
        if ($request->filled('reservation_id')) {
            $reservation = Reservation::where('id', $request->reservation_id)
                ->where('client_id', $clientRecord->id)
                ->firstOrFail();
            $documentableType = Reservation::class;
            $documentableId   = $reservation->id;
        } else {
            $documentableType = Client::class;
            $documentableId   = $clientRecord->id;
        }

        $file     = $request->file('file');
        $filePath = $file->storeAs('documents', time() . '_' . $file->getClientOriginalName(), 'public');

        Document::create([
            'title'             => $request->title,
            'document_type'     => $request->document_type,
            'expiry_date'       => $request->expiry_date,
            'documentable_type' => $documentableType,
            'documentable_id'   => $documentableId,
            'file_path'         => $filePath,
            'file_name'         => $file->getClientOriginalName(),
            'file_type'         => $file->getMimeType(),
            'file_size'         => $file->getSize(),
            'is_verified'       => false,
        ]);

        return back()->with('success', 'Document uploaded successfully. It will be reviewed by our team.');
    }

    public function uploadChecklistItem(Request $request, Reservation $reservation, string $key)
    {
        $clientRecord = Client::where('user_id', auth()->id())->first();
        if (!$clientRecord || $reservation->client_id !== $clientRecord->id) abort(403);

        $checklist = $reservation->document_checklist ?? [];
        $item      = collect($checklist)->firstWhere('key', $key);
        if (!$item) return back()->with('error', 'Invalid document slot.');

        $request->validate([
            'file' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        $file     = $request->file('file');
        $folder   = 'documents/' . $reservation->id;
        $filePath = $file->storeAs($folder, $key . '_' . time() . '.' . $file->getClientOriginalExtension(), 'public');

        // Check if a previous document exists for this slot — mark as resubmitted
        $existing = Document::where('documentable_type', Reservation::class)
            ->where('documentable_id', $reservation->id)
            ->where('checklist_key', $key)
            ->latest()->first();

        $status = $existing ? 'resubmitted' : 'submitted';

        Document::create([
            'title'             => $item['label'],
            'document_type'     => $key,
            'checklist_key'     => $key,
            'checklist_status'  => $status,
            'documentable_type' => Reservation::class,
            'documentable_id'   => $reservation->id,
            'file_path'         => $filePath,
            'file_name'         => $file->getClientOriginalName(),
            'file_type'         => $file->getMimeType(),
            'file_size'         => $file->getSize(),
            'is_verified'       => false,
        ]);

        Log::info("Checklist document uploaded: key={$key}, reservation={$reservation->id}, client={$clientRecord->id}");

        // Notify admins
        $admins = User::whereIn('role', ['admin', 'agent'])->where('is_active', true)->get();
        foreach ($admins as $admin) {
            EstateNotification::create([
                'notifiable_type' => User::class,
                'notifiable_id'   => $admin->id,
                'type'            => 'document_uploaded',
                'data'            => [
                    'title'   => 'Document Uploaded — ' . $clientRecord->full_name,
                    'message' => $clientRecord->full_name . ' uploaded "' . $item['label'] . '" for ' . ($reservation->property->title ?? 'a property') . '. Please review.',
                ],
                'priority' => 'normal',
                'is_read'  => false,
            ]);
        }

        if ($request->ajax()) {
            return response()->json([
                'success'   => true,
                'message'   => '"' . $item['label'] . '" uploaded successfully.',
                'status'    => $status,
                'file_name' => $file->getClientOriginalName(),
            ]);
        }

        return back()->with('success', '"' . $item['label'] . '" uploaded successfully. It will be reviewed by our team.');
    }

    public function markNotApplicable(Request $request, Reservation $reservation, string $key)
    {
        $clientRecord = Client::where('user_id', auth()->id())->first();
        if (!$clientRecord || $reservation->client_id !== $clientRecord->id) abort(403);

        $checklist = $reservation->document_checklist ?? [];
        $item      = collect($checklist)->firstWhere('key', $key);
        if (!$item || !($item['conditional'] ?? false)) {
            return back()->with('error', 'This document cannot be marked as not applicable.');
        }

        $request->validate(['reason' => 'required|string|max:255']);

        Document::create([
            'title'                  => $item['label'],
            'document_type'          => $key,
            'checklist_key'          => $key,
            'checklist_status'       => 'not_applicable',
            'not_applicable_reason'  => $request->reason,
            'documentable_type'      => Reservation::class,
            'documentable_id'        => $reservation->id,
            'file_path'              => '',
            'file_name'              => '',
            'file_type'              => '',
            'file_size'              => 0,
            'is_verified'            => false,
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success'              => true,
                'message'              => '"' . $item['label'] . '" marked as not applicable.',
                'status'               => 'not_applicable',
                'not_applicable_reason'=> $request->reason,
            ]);
        }

        return back()->with('success', '"' . $item['label'] . '" marked as not applicable.');
    }

    public function verifyChecklistItem(Request $request, Reservation $reservation, string $key)
    {
        $doc = Document::where('documentable_type', Reservation::class)
            ->where('documentable_id', $reservation->id)
            ->where('checklist_key', $key)
            ->latest()->firstOrFail();

        $doc->update(['checklist_status' => 'approved', 'is_verified' => true, 'verified_by' => auth()->id()]);

        $clientUser = $reservation->client?->user;
        if ($clientUser) {
            EstateNotification::create([
                'notifiable_type' => User::class,
                'notifiable_id'   => $clientUser->id,
                'type'            => 'checklist_document_verified',
                'data'            => [
                    'title'   => 'Document Approved',
                    'message' => '"' . $doc->title . '" for ' . $reservation->property->title . ' has been approved.',
                ],
                'priority' => 'normal',
                'is_read'  => false,
            ]);
        }

        return back()->with('success', '"' . $doc->title . '" approved.');
    }

    public function rejectChecklistItem(Request $request, Reservation $reservation, string $key)
    {
        $request->validate(['rejection_reason' => 'required|string|max:500']);

        $doc = Document::where('documentable_type', Reservation::class)
            ->where('documentable_id', $reservation->id)
            ->where('checklist_key', $key)
            ->latest()->firstOrFail();

        $doc->update(['checklist_status' => 'rejected', 'rejection_reason' => $request->rejection_reason]);

        $clientUser = $reservation->client?->user;
        if ($clientUser) {
            EstateNotification::create([
                'notifiable_type' => User::class,
                'notifiable_id'   => $clientUser->id,
                'type'            => 'checklist_document_rejected',
                'data'            => [
                    'title'   => 'Document Rejected — Resubmission Required',
                    'message' => '"' . $doc->title . '" for ' . $reservation->property->title . ' was rejected. Reason: ' . $request->rejection_reason,
                ],
                'priority' => 'high',
                'is_read'  => false,
            ]);
        }

        return back()->with('success', '"' . $doc->title . '" rejected. Client notified.');
    }

    public function pollChecklist()
    {
        $clientRecord = \App\Models\Client::where('user_id', auth()->id())->first();
        if (!$clientRecord) return response()->json([]);

        $reservation = \App\Models\Reservation::where('client_id', $clientRecord->id)
            ->whereIn('status', ['reservation_paid', 'pagibig_applied', 'pagibig_approved', 'pagibig_takeout', 'pagibig_amortization'])
            ->latest()->first();

        if (!$reservation) return response()->json([]);

        $docs = Document::where('documentable_type', \App\Models\Reservation::class)
            ->where('documentable_id', $reservation->id)
            ->whereNotNull('checklist_key')
            ->get()
            ->groupBy('checklist_key')
            ->map(fn($group) => $group->sortByDesc('created_at')->first())
            ->map(fn($doc) => [
                'status'           => $doc->checklist_status,
                'rejection_reason' => $doc->rejection_reason,
                'file_name'        => $doc->file_name,
            ]);

        return response()->json($docs);
    }

    public function checker(Request $request)
    {
        $query = Reservation::with(['property', 'client', 'agent']);

        if ($request->filled('search')) {
            $query->whereHas('client', fn($q) =>
                $q->where('first_name', 'like', '%' . $request->search . '%')
                  ->orWhere('last_name',  'like', '%' . $request->search . '%')
            );
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $reservations = $query->latest()->paginate(15)->withQueryString();

        $checklist = $reservations->getCollection()->mapWithKeys(fn($r) => [
            $r->id => DocumentCheckerService::check($r)
        ]);

        return view('documents.checker', compact('reservations', 'checklist'));
    }
}
