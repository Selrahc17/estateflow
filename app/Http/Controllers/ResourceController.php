<?php

namespace App\Http\Controllers;

use App\Models\Resource;
use App\Models\Project;
use Illuminate\Http\Request;

class ResourceController extends Controller
{
    public function index(Request $request)
    {
        $query = Resource::with('project');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $resources    = $query->latest()->paginate(15)->withQueryString();
        $totalCount   = Resource::count();
        $totalCost    = Resource::selectRaw('SUM(quantity * unit_price) as total')->value('total') ?? 0;
        $pendingCount = Resource::where('status', 'pending')->count();
        $projects     = Project::orderBy('name')->get();

        return view('resources.index', compact('resources', 'totalCount', 'totalCost', 'pendingCount', 'projects'));
    }

    public function create(Request $request)
    {
        $projects        = Project::orderBy('name')->get();
        $selectedProject = $request->project_id;
        return view('resources.create', compact('projects', 'selectedProject'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'project_id'    => 'required|exists:projects,id',
            'name'          => 'required|string|max:255',
            'type'          => 'required|in:material,equipment,labor',
            'unit'          => 'required|string|max:50',
            'quantity'      => 'required|numeric|min:0',
            'unit_price'    => 'required|numeric|min:0',
            'status'        => 'required|in:pending,ordered,delivered,used,returned',
            'description'   => 'nullable|string',
            'currency'      => 'nullable|string|max:3',
            'delivery_date' => 'nullable|date',
            'notes'         => 'nullable|string',
        ]);

        Resource::create($request->all());

        return redirect()->route('resources.index')->with('success', 'Resource added successfully.');
    }

    public function show(Resource $resource)
    {
        $resource->load('project');
        return view('resources.show', compact('resource'));
    }

    public function edit(Resource $resource)
    {
        $projects = Project::orderBy('name')->get();
        return view('resources.edit', compact('resource', 'projects'));
    }

    public function update(Request $request, Resource $resource)
    {
        $request->validate([
            'project_id'    => 'required|exists:projects,id',
            'name'          => 'required|string|max:255',
            'type'          => 'required|in:material,equipment,labor',
            'unit'          => 'required|string|max:50',
            'quantity'      => 'required|numeric|min:0',
            'unit_price'    => 'required|numeric|min:0',
            'status'        => 'required|in:pending,ordered,delivered,used,returned',
            'description'   => 'nullable|string',
            'currency'      => 'nullable|string|max:3',
            'delivery_date' => 'nullable|date',
            'notes'         => 'nullable|string',
        ]);

        $resource->update($request->all());

        return redirect()->route('resources.index')->with('success', 'Resource updated successfully.');
    }

    public function destroy(Resource $resource)
    {
        $resource->delete();
        return back()->with('success', 'Resource deleted successfully.');
    }
}
