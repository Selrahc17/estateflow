<?php

namespace App\Http\Controllers;

use App\Models\ProgressLog;
use App\Models\Project;
use Illuminate\Http\Request;

class ProgressLogController extends Controller
{
    public function index(Request $request)
    {
        $query = ProgressLog::with(['project', 'user']);

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        if ($request->filled('search')) {
            $query->where('description', 'like', '%' . $request->search . '%');
        }

        $logs         = $query->latest('log_date')->paginate(15)->withQueryString();
        $totalLogs    = ProgressLog::count();
        $projects     = Project::orderBy('name')->get();

        return view('progress-logs.index', compact('logs', 'totalLogs', 'projects'));
    }

    public function create(Request $request)
    {
        $user = auth()->user();

        // Staff only see their own projects
        if ($user->isContractor()) {
            $staffRecord = \App\Models\Staff::where('user_id', $user->id)->first();
            $projects = $staffRecord
                ? Project::where('staff_id', $staffRecord->id)->orderBy('name')->get()
                : collect();
        } else {
            $projects = Project::orderBy('name')->get();
        }

        $selectedProject = $request->filled('project_id') ? $request->project_id : null;
        return view('progress-logs.create', compact('projects', 'selectedProject'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'project_id'            => 'required|exists:projects,id',
            'log_date'              => 'required|date',
            'description'           => 'required|string',
            'completion_percentage' => 'required|integer|min:0|max:100',
            'issues'                => 'nullable|string',
            'weather_conditions'    => 'nullable|string',
            'workers_count'         => 'nullable|integer|min:0',
            'hours_worked'          => 'nullable|integer|min:0',
            'image_path'            => 'nullable|image|max:5120',
            'images.*'              => 'nullable|image|max:5120',
        ]);

        $data            = $request->except(['image_path', 'images']);
        $data['user_id'] = auth()->id();

        if ($request->hasFile('image_path')) {
            $data['image_path'] = $request->file('image_path')->store('progress-logs', 'public');
        }

        if ($request->hasFile('images')) {
            $data['images'] = collect($request->file('images'))
                ->map(fn($f) => $f->store('progress-logs', 'public'))
                ->toArray();
        }

        $log = ProgressLog::create($data);
        $log->project->update(['completion_percentage' => $request->completion_percentage]);

        $redirect = $request->filled('project_id')
            ? route('projects.show', $request->project_id)
            : route('progress-logs.index');

        return redirect($redirect)->with('success', 'Progress log added successfully.');
    }

    public function show(ProgressLog $progressLog)
    {
        $progressLog->load(['project', 'user']);
        return view('progress-logs.show', compact('progressLog'));
    }

    public function edit(ProgressLog $progressLog)
    {
        $projects = Project::orderBy('name')->get();
        return view('progress-logs.edit', compact('progressLog', 'projects'));
    }

    public function update(Request $request, ProgressLog $progressLog)
    {
        $request->validate([
            'project_id'            => 'required|exists:projects,id',
            'log_date'              => 'required|date',
            'description'           => 'required|string',
            'completion_percentage' => 'required|integer|min:0|max:100',
            'issues'                => 'nullable|string',
            'weather_conditions'    => 'nullable|string',
            'workers_count'         => 'nullable|integer|min:0',
            'hours_worked'          => 'nullable|integer|min:0',
            'image_path'            => 'nullable|image|max:5120',
            'images.*'              => 'nullable|image|max:5120',
        ]);

        $data = $request->except(['image_path', 'images']);

        if ($request->hasFile('image_path')) {
            if ($progressLog->image_path) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($progressLog->image_path);
            }
            $data['image_path'] = $request->file('image_path')->store('progress-logs', 'public');
        }

        if ($request->hasFile('images')) {
            $data['images'] = collect($request->file('images'))
                ->map(fn($f) => $f->store('progress-logs', 'public'))
                ->toArray();
        }

        $progressLog->update($data);

        return redirect()->route('progress-logs.index')->with('success', 'Progress log updated successfully.');
    }

    public function destroy(ProgressLog $progressLog)
    {
        $progressLog->delete();
        return back()->with('success', 'Progress log deleted successfully.');
    }
}
