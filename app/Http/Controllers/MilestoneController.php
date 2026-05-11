<?php

namespace App\Http\Controllers;

use App\Models\Milestone;
use App\Models\Project;
use Illuminate\Http\Request;

class MilestoneController extends Controller
{
    public function index(Request $request)
    {
        $query = Milestone::with('project');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        if ($request->filled('status')) {
            $query->where('is_completed', $request->status === 'completed');
        }

        $milestones      = $query->latest()->paginate(15)->withQueryString();
        $totalMilestones = Milestone::count();
        $completedCount  = Milestone::where('is_completed', true)->count();
        $pendingCount    = Milestone::where('is_completed', false)->count();
        $projects        = Project::orderBy('name')->get();

        return view('milestones.index', compact(
            'milestones', 'totalMilestones', 'completedCount', 'pendingCount', 'projects'
        ));
    }

    public function create(Request $request)
    {
        $projects        = Project::orderBy('name')->get();
        $selectedProject = $request->filled('project_id') ? $request->project_id : null;
        return view('milestones.create', compact('projects', 'selectedProject'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'project_id'           => 'required|exists:projects,id',
            'name'                 => 'required|string|max:255',
            'description'          => 'nullable|string',
            'target_date'          => 'required|date',
            'actual_date'          => 'nullable|date',
            'is_completed'         => 'boolean',
            'completion_percentage'=> 'nullable|integer|min:0|max:100',
            'notes'                => 'nullable|string',
        ]);

        Milestone::create($request->all());

        $redirect = $request->filled('project_id')
            ? route('projects.show', $request->project_id)
            : route('milestones.index');

        return redirect($redirect)->with('success', 'Milestone created successfully.');
    }

    public function show(Milestone $milestone)
    {
        $milestone->load('project');
        return view('milestones.show', compact('milestone'));
    }

    public function edit(Milestone $milestone)
    {
        $projects = Project::orderBy('name')->get();
        return view('milestones.edit', compact('milestone', 'projects'));
    }

    public function update(Request $request, Milestone $milestone)
    {
        $request->validate([
            'project_id'           => 'required|exists:projects,id',
            'name'                 => 'required|string|max:255',
            'description'          => 'nullable|string',
            'target_date'          => 'required|date',
            'actual_date'          => 'nullable|date',
            'is_completed'         => 'boolean',
            'completion_percentage'=> 'nullable|integer|min:0|max:100',
            'notes'                => 'nullable|string',
        ]);

        $data = $request->all();
        $data['is_completed'] = $request->boolean('is_completed');

        $milestone->update($data);

        return redirect()->route('milestones.index')->with('success', 'Milestone updated successfully.');
    }

    public function destroy(Milestone $milestone)
    {
        $milestone->delete();
        return back()->with('success', 'Milestone deleted successfully.');
    }
}
