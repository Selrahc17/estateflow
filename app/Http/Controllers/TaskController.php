<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $query = Task::with(['project', 'assignedTo']);

        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        $tasks          = $query->latest()->paginate(15)->withQueryString();
        $totalTasks     = Task::count();
        $pendingCount   = Task::where('status', 'pending')->count();
        $inProgressCount = Task::where('status', 'in_progress')->count();
        $completedCount = Task::where('status', 'completed')->count();
        $projects       = Project::orderBy('name')->get();

        return view('tasks.index', compact(
            'tasks', 'totalTasks', 'pendingCount', 'inProgressCount', 'completedCount', 'projects'
        ));
    }

    public function create(Request $request)
    {
        $projects = Project::orderBy('name')->get();
        $users    = User::where('is_active', true)->get();
        $selectedProject = $request->filled('project_id') ? $request->project_id : null;
        return view('tasks.create', compact('projects', 'users', 'selectedProject'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'project_id'      => 'required|exists:projects,id',
            'title'           => 'required|string|max:255',
            'description'     => 'nullable|string',
            'assigned_to'     => 'nullable|exists:users,id',
            'start_date'      => 'nullable|date',
            'due_date'        => 'nullable|date',
            'completed_date'  => 'nullable|date',
            'priority'        => 'required|in:low,medium,high,urgent',
            'status'          => 'required|in:pending,in_progress,completed,cancelled',
            'estimated_hours' => 'nullable|integer|min:0',
            'actual_hours'    => 'nullable|integer|min:0',
            'notes'           => 'nullable|string',
        ]);

        $data = $request->all();
        $data['assigned_by'] = auth()->id();

        Task::create($data);

        $redirect = $request->filled('project_id')
            ? route('projects.show', $request->project_id)
            : route('tasks.index');

        return redirect($redirect)->with('success', 'Task created successfully.');
    }

    public function show(Task $task)
    {
        $task->load(['project', 'assignedTo', 'assignedBy']);
        return view('tasks.show', compact('task'));
    }

    public function edit(Task $task)
    {
        $projects = Project::orderBy('name')->get();
        $users    = User::where('is_active', true)->get();
        return view('tasks.edit', compact('task', 'projects', 'users'));
    }

    public function update(Request $request, Task $task)
    {
        $request->validate([
            'project_id'      => 'required|exists:projects,id',
            'title'           => 'required|string|max:255',
            'description'     => 'nullable|string',
            'assigned_to'     => 'nullable|exists:users,id',
            'start_date'      => 'nullable|date',
            'due_date'        => 'nullable|date',
            'completed_date'  => 'nullable|date',
            'priority'        => 'required|in:low,medium,high,urgent',
            'status'          => 'required|in:pending,in_progress,completed,cancelled',
            'estimated_hours' => 'nullable|integer|min:0',
            'actual_hours'    => 'nullable|integer|min:0',
            'notes'           => 'nullable|string',
        ]);

        $task->update($request->all());

        return redirect()->route('tasks.index')->with('success', 'Task updated successfully.');
    }

    public function destroy(Task $task)
    {
        $projectId = $task->project_id;
        $task->delete();
        return back()->with('success', 'Task deleted successfully.');
    }
}
