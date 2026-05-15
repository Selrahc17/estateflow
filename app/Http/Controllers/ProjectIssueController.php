<?php

namespace App\Http\Controllers;

use App\Models\EstateNotification;
use App\Models\Project;
use App\Models\ProjectIssue;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Http\Request;

class ProjectIssueController extends Controller
{
    public function index(Request $request)
    {
        $user             = auth()->user();
        $contractorRecord = Staff::where('user_id', $user->id)->first();

        $query = ProjectIssue::with(['project', 'reportedBy', 'resolvedBy']);

        // Staff only see issues from their own projects
        if (!$user->isAdmin() && $contractorRecord) {
            $query->whereHas('project', fn($q) => $q->where('staff_id', $contractorRecord->id));
        }

        if ($request->filled('status'))   $query->where('status', $request->status);
        if ($request->filled('severity')) $query->where('severity', $request->severity);
        if ($request->filled('type'))     $query->where('type', $request->type);
        if ($request->filled('project_id')) $query->where('project_id', $request->project_id);

        $issues       = $query->latest()->paginate(15)->withQueryString();
        $openCount    = ProjectIssue::where('status', 'open')->count();
        $criticalCount = ProjectIssue::where('severity', 'critical')->whereIn('status', ['open', 'in_progress'])->count();
        $projects     = Project::orderBy('name')->get();

        return view('project-issues.index', compact('issues', 'openCount', 'criticalCount', 'projects'));
    }

    public function store(Request $request, Project $project)
    {
        $request->validate([
            'type'        => 'required|in:delay,material_shortage,safety,quality,weather,other',
            'severity'    => 'required|in:low,medium,high,critical',
            'title'       => 'required|string|max:255',
            'description' => 'required|string',
            'impact_days' => 'nullable|integer|min:0',
        ]);

        $issue = ProjectIssue::create([
            'project_id'  => $project->id,
            'reported_by' => auth()->id(),
            'type'        => $request->type,
            'severity'    => $request->severity,
            'title'       => $request->title,
            'description' => $request->description,
            'impact_days' => $request->impact_days ?? 0,
            'status'      => 'open',
        ]);

        // Notify all admins
        $admins = User::where('role', 'admin')->where('is_active', true)->get();
        foreach ($admins as $admin) {
            EstateNotification::create([
                'notifiable_type' => User::class,
                'notifiable_id'   => $admin->id,
                'type'            => 'project_issue_reported',
                'title'           => '[' . strtoupper($request->severity) . '] Issue Reported — ' . $project->name,
                'message'         => auth()->user()->name . ' reported: "' . $request->title . '" on project ' . $project->name . '.' .
                    ($request->impact_days > 0 ? ' Estimated delay: ' . $request->impact_days . ' day(s).' : ''),
                'data'            => json_encode(['issue_id' => $issue->id, 'project_id' => $project->id]),
                'priority'        => in_array($request->severity, ['high', 'critical']) ? 'high' : 'normal',
            ]);
        }

        return redirect()->route('projects.show', $project)->with('success', 'Issue reported successfully.');
    }

    public function updateStatus(Request $request, ProjectIssue $issue)
    {
        $request->validate([
            'status'           => 'required|in:open,in_progress,resolved,dismissed',
            'resolution_notes' => 'nullable|string|max:1000',
        ]);

        $data = ['status' => $request->status];

        if (in_array($request->status, ['resolved', 'dismissed'])) {
            $data['resolved_by']       = auth()->id();
            $data['resolved_at']       = now();
            $data['resolution_notes']  = $request->resolution_notes;
        }

        $issue->update($data);

        // Notify reporter
        if (in_array($request->status, ['resolved', 'dismissed']) && $issue->reported_by !== auth()->id()) {
            EstateNotification::create([
                'notifiable_type' => User::class,
                'notifiable_id'   => $issue->reported_by,
                'type'            => 'project_issue_' . $request->status,
                'title'           => 'Issue ' . ucfirst($request->status) . ' — ' . $issue->title,
                'message'         => 'Your reported issue "' . $issue->title . '" on project ' . $issue->project->name . ' has been ' . $request->status . '.' .
                    ($request->resolution_notes ? ' Note: ' . $request->resolution_notes : ''),
                'data'            => json_encode(['issue_id' => $issue->id]),
            ]);
        }

        return back()->with('success', 'Issue status updated.');
    }

    public function destroy(ProjectIssue $issue)
    {
        $issue->delete();
        return back()->with('success', 'Issue deleted.');
    }
}
