<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Property;
use App\Models\Client;
use App\Models\Staff;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $query = Project::with(['property', 'client', 'staff']);

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $projects          = $query->latest()->paginate(15)->withQueryString();
        $totalProjects     = Project::count();
        $inProgressCount   = Project::where('status', 'in_progress')->count();
        $completedCount    = Project::where('status', 'completed')->count();
        $planningCount     = Project::where('status', 'planning')->count();

        return view('projects.index', compact(
            'projects', 'totalProjects', 'inProgressCount', 'completedCount', 'planningCount'
        ));
    }

    public function create()
    {
        $properties  = Property::where('is_active', true)->get();
        $clients     = Client::where('status', 'active')->get();
        $contractors = Staff::where('status', 'active')->get();
        return view('projects.create', compact('properties', 'clients', 'contractors'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'                        => 'required|string|max:255',
            'description'                 => 'nullable|string',
            'property_id'                 => 'nullable|exists:properties,id',
            'client_id'                   => 'nullable|exists:clients,id',
            'staff_id'                    => 'nullable|exists:staff,id',
            'start_date'                  => 'nullable|date',
            'estimated_completion_date'   => 'nullable|date',
            'actual_completion_date'      => 'nullable|date',
            'budget'                      => 'nullable|numeric|min:0',
            'actual_cost'                 => 'nullable|numeric|min:0',
            'status'                      => 'required|in:planning,in_progress,on_hold,completed,cancelled',
            'completion_percentage'       => 'nullable|integer|min:0|max:100',
            'notes'                       => 'nullable|string',
        ]);

        Project::create($request->all());

        return redirect()->route('projects.index')->with('success', 'Project created successfully.');
    }

    public function show(Project $project)
    {
        $project->load(['property', 'client', 'staff', 'tasks', 'milestones', 'budgets', 'progressLogs', 'issues.reportedBy', 'issues.resolvedBy']);
        return view('projects.show', compact('project'));
    }

    public function edit(Project $project)
    {
        $properties  = Property::where('is_active', true)->get();
        $clients     = Client::where('status', 'active')->get();
        $contractors = Staff::where('status', 'active')->get();
        return view('projects.edit', compact('project', 'properties', 'clients', 'contractors'));
    }

    public function update(Request $request, Project $project)
    {
        $request->validate([
            'name'                        => 'required|string|max:255',
            'description'                 => 'nullable|string',
            'property_id'                 => 'nullable|exists:properties,id',
            'client_id'                   => 'nullable|exists:clients,id',
            'staff_id'                    => 'nullable|exists:staff,id',
            'start_date'                  => 'nullable|date',
            'estimated_completion_date'   => 'nullable|date',
            'actual_completion_date'      => 'nullable|date',
            'budget'                      => 'nullable|numeric|min:0',
            'actual_cost'                 => 'nullable|numeric|min:0',
            'status'                      => 'required|in:planning,in_progress,on_hold,completed,cancelled',
            'completion_percentage'       => 'nullable|integer|min:0|max:100',
            'notes'                       => 'nullable|string',
        ]);

        $project->update($request->all());

        return redirect()->route('projects.index')->with('success', 'Project updated successfully.');
    }

    public function destroy(Project $project)
    {
        $project->delete();
        return back()->with('success', "Project \"{$project->name}\" deleted successfully.");
    }
}
