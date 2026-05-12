<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Property;
use App\Models\Client;
use App\Models\Project;
use App\Models\Reservation;
use App\Services\DocumentCheckerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    public function index(Request $request)
    {
        $query = Document::with('verifiedBy');

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
