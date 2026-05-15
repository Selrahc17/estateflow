@extends('layouts.app')

@section('title', $project->name . ' - EstateFlow')
@section('page-title', $project->name)
@section('page-subtitle', 'Project Details')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Left: Project Info --}}
    <div class="lg:col-span-1 space-y-6">

        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-gray-800">Overview</h3>
                <span class="text-xs px-2.5 py-1 rounded-full font-medium
                    {{ $project->status === 'planning'    ? 'bg-gray-100 text-gray-600'     : '' }}
                    {{ $project->status === 'in_progress' ? 'bg-blue-100 text-blue-700'     : '' }}
                    {{ $project->status === 'on_hold'     ? 'bg-yellow-100 text-yellow-700' : '' }}
                    {{ $project->status === 'completed'   ? 'bg-green-100 text-green-700'   : '' }}
                    {{ $project->status === 'cancelled'   ? 'bg-red-100 text-red-700'       : '' }}">
                    {{ ucfirst(str_replace('_', ' ', $project->status)) }}
                </span>
            </div>

            {{-- Progress Bar --}}
            <div class="mb-4">
                <div class="flex justify-between text-xs text-gray-500 mb-1">
                    <span>Completion</span>
                    <span>{{ $project->completion_percentage }}%</span>
                </div>
                <div class="w-full bg-gray-100 rounded-full h-3">
                    <div class="h-3 rounded-full {{ $project->completion_percentage >= 100 ? 'bg-green-500' : 'bg-indigo-500' }}"
                        style="width: {{ $project->completion_percentage }}%"></div>
                </div>
            </div>

            <div class="space-y-3 text-sm">
                @if($project->start_date)
                <div class="flex justify-between">
                    <span class="text-gray-500">Start Date</span>
                    <span class="font-medium text-gray-800">{{ $project->start_date->format('M d, Y') }}</span>
                </div>
                @endif
                @if($project->estimated_completion_date)
                <div class="flex justify-between">
                    <span class="text-gray-500">Est. Completion</span>
                    <span class="font-medium text-gray-800">{{ $project->estimated_completion_date->format('M d, Y') }}</span>
                </div>
                @endif
                @if($project->actual_completion_date)
                <div class="flex justify-between">
                    <span class="text-gray-500">Actual Completion</span>
                    <span class="font-medium text-green-700">{{ $project->actual_completion_date->format('M d, Y') }}</span>
                </div>
                @endif
                <div class="flex justify-between pt-2 border-t border-gray-100">
                    <span class="text-gray-500">Budget</span>
                    <span class="font-bold text-gray-800">₱{{ number_format($project->budget, 2) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Actual Cost</span>
                    <span class="font-bold {{ $project->actual_cost > $project->budget ? 'text-red-600' : 'text-gray-800' }}">
                        ₱{{ number_format($project->actual_cost, 2) }}
                    </span>
                </div>
            </div>

            @if(auth()->user()->isAdmin())
            <div class="mt-6 flex gap-2">
                <a href="{{ route('projects.edit', $project) }}" class="flex-1 text-center bg-indigo-600 text-white py-2 rounded-lg text-sm hover:bg-indigo-700 transition">
                    <i class="fas fa-edit mr-1"></i> Edit
                </a>
                <form method="POST" action="{{ route('projects.destroy', $project) }}"
                    onsubmit="return confirm('Delete {{ $project->name }}?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="px-4 py-2 bg-red-50 text-red-600 rounded-lg text-sm hover:bg-red-100 transition">
                        <i class="fas fa-trash"></i>
                    </button>
                </form>
            </div>
            @endif
        </div>

        {{-- Contract Timeline & Delay Tracker --}}
        @php
            $start      = $project->start_date;
            $target     = $project->estimated_completion_date;
            $actual     = $project->actual_completion_date;
            $today      = now();
            $isComplete = $project->status === 'completed';
            $endDate    = $actual ?? ($isComplete ? $today : null);

            // Delay calculation
            $delayDays  = 0;
            $isDelayed  = false;
            if ($target) {
                if ($actual) {
                    // Already completed — compare actual vs target
                    $delayDays = $target->diffInDays($actual, false);
                    $isDelayed = $delayDays > 0;
                } elseif (!$isComplete && $target->isPast()) {
                    // Still ongoing but past target
                    $delayDays = $target->diffInDays($today);
                    $isDelayed = true;
                }
            }
        @endphp
        <div class="bg-white rounded-xl shadow-sm p-6 {{ $isDelayed ? 'border-l-4 border-red-400' : ($isComplete ? 'border-l-4 border-green-400' : '') }}">
            <h3 class="font-semibold text-gray-800 mb-4 flex items-center gap-2">
                <i class="fas fa-calendar-alt text-indigo-500"></i> Contract Timeline
            </h3>

            {{-- Timeline Dates --}}
            <div class="space-y-3 text-sm mb-4">
                <div class="flex justify-between items-center">
                    <span class="text-gray-500 flex items-center gap-2"><i class="fas fa-play-circle text-green-400 w-4"></i> Start Date</span>
                    <span class="font-semibold text-gray-800">{{ $start?->format('M d, Y') ?? '—' }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-500 flex items-center gap-2"><i class="fas fa-flag-checkered text-indigo-400 w-4"></i> Target Completion</span>
                    <span class="font-semibold {{ $isDelayed ? 'text-red-600' : 'text-gray-800' }}">{{ $target?->format('M d, Y') ?? '—' }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-500 flex items-center gap-2"><i class="fas fa-check-circle {{ $actual ? 'text-green-500' : 'text-gray-300' }} w-4"></i> Actual Completion</span>
                    <span class="font-semibold {{ $actual ? 'text-green-600' : 'text-gray-400' }}">{{ $actual?->format('M d, Y') ?? 'Not yet completed' }}</span>
                </div>
            </div>

            {{-- Delay / On-time Status --}}
            @if($target)
                @if($isDelayed)
                <div class="bg-red-50 border border-red-200 rounded-xl p-4">
                    <div class="flex items-center gap-2 mb-2">
                        <i class="fas fa-exclamation-triangle text-red-500"></i>
                        <p class="text-sm font-bold text-red-700">Project Delayed — {{ $delayDays }} day(s)</p>
                    </div>
                    <p class="text-xs text-red-600">
                        {{ $actual ? 'Completed ' . $delayDays . ' day(s) after the target date.' : 'Currently ' . $delayDays . ' day(s) past the target completion date.' }}
                    </p>
                    @if($actual)
                    <div class="mt-3 pt-3 border-t border-red-200">
                        <p class="text-xs font-semibold text-red-700 mb-1">Penalty / Deduction Note</p>
                        <p class="text-xs text-red-600">Construction company is <strong>{{ $delayDays }} day(s) late</strong> per contract. Review penalty clause for applicable deductions.</p>
                    </div>
                    @endif
                </div>
                @elseif($isComplete)
                @php $earlyDays = $target->diffInDays($actual ?? $today); @endphp
                <div class="bg-green-50 border border-green-200 rounded-xl p-4">
                    <div class="flex items-center gap-2">
                        <i class="fas fa-check-circle text-green-500"></i>
                        <p class="text-sm font-bold text-green-700">Completed On Time</p>
                    </div>
                    <p class="text-xs text-green-600 mt-1">Project was completed within the contract deadline. No penalties apply.</p>
                </div>
                @else
                @php $daysLeft = (int) $today->diffInDays($target, false); @endphp
                <div class="bg-indigo-50 border border-indigo-100 rounded-xl p-4">
                    <div class="flex items-center gap-2">
                        <i class="fas fa-clock text-indigo-500"></i>
                        <p class="text-sm font-semibold text-indigo-700">
                            {{ $daysLeft > 0 ? $daysLeft . ' days remaining' : 'Deadline today' }}
                        </p>
                    </div>
                    <p class="text-xs text-indigo-500 mt-1">Project is currently at {{ $project->completion_percentage }}% completion.</p>
                </div>
                @endif
            @endif
        </div>

        {{-- Client & Contractor --}}
        @if($project->client)
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="font-semibold text-gray-800 mb-3 text-sm">Client</h3>
            <p class="font-medium text-gray-800">{{ $project->client->full_name }}</p>
            <p class="text-xs text-gray-400 mt-1">{{ $project->client->phone }}</p>
            <a href="{{ route('clients.show', $project->client) }}" class="mt-3 block text-center text-xs bg-gray-50 text-gray-600 py-2 rounded-lg hover:bg-gray-100 transition">View Client</a>
        </div>
        @endif

        @if($project->contractor)
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="font-semibold text-gray-800 mb-3 text-sm">Staff</h3>
            <p class="font-medium text-gray-800">{{ $project->contractor->company_name }}</p>
            <p class="text-xs text-gray-400 mt-1">{{ $project->contractor->contact_person }}</p>
            <a href="{{ route('contractors.show', $project->contractor) }}" class="mt-3 block text-center text-xs bg-gray-50 text-gray-600 py-2 rounded-lg hover:bg-gray-100 transition">View Staff</a>
        </div>
        @endif

        @if($project->notes)
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="font-semibold text-gray-800 mb-2 text-sm">Notes</h3>
            <p class="text-sm text-gray-600">{{ $project->notes }}</p>
        </div>
        @endif
    </div>

    {{-- Right: Tasks, Milestones, Budgets, Progress Logs --}}
    <div class="lg:col-span-2 space-y-6">

        {{-- Tasks --}}
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-gray-800">Tasks ({{ $project->tasks->count() }})</h3>
                @if(auth()->user()->isAdmin() || auth()->user()->isContractor())
                    <a href="{{ route('tasks.create') }}?project_id={{ $project->id }}" class="text-xs bg-indigo-600 text-white px-3 py-1.5 rounded-lg hover:bg-indigo-700 transition">
                        <i class="fas fa-plus mr-1"></i> Add Task
                    </a>
                @endif
            </div>
            @forelse($project->tasks as $task)
            <div class="flex items-center justify-between py-3 border-b border-gray-50 last:border-0">
                <div>
                    <p class="text-sm font-medium text-gray-800">{{ $task->title }}</p>
                    <p class="text-xs text-gray-400">
                        Due: {{ $task->due_date ? \Carbon\Carbon::parse($task->due_date)->format('M d, Y') : 'No due date' }}
                        · Priority: {{ ucfirst($task->priority) }}
                    </p>
                </div>
                <span class="text-xs px-2.5 py-1 rounded-full font-medium
                    {{ $task->status === 'pending'     ? 'bg-gray-100 text-gray-600'     : '' }}
                    {{ $task->status === 'in_progress' ? 'bg-blue-100 text-blue-700'     : '' }}
                    {{ $task->status === 'completed'   ? 'bg-green-100 text-green-700'   : '' }}
                    {{ $task->status === 'cancelled'   ? 'bg-red-100 text-red-700'       : '' }}">
                    {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                </span>
            </div>
            @empty
            <p class="text-sm text-gray-400">No tasks yet.</p>
            @endforelse
        </div>

        {{-- Milestones --}}
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-gray-800">Milestones ({{ $project->milestones->count() }})</h3>
            </div>
            @forelse($project->milestones as $milestone)
            <div class="flex items-center justify-between py-3 border-b border-gray-50 last:border-0">
                <div>
                    <p class="text-sm font-medium text-gray-800">{{ $milestone->name }}</p>
                    <p class="text-xs text-gray-400">Target: {{ \Carbon\Carbon::parse($milestone->target_date)->format('M d, Y') }}</p>
                </div>
                <div class="flex items-center gap-3">
                    <div class="text-right">
                        <p class="text-xs text-gray-500">{{ $milestone->completion_percentage }}%</p>
                        <div class="w-16 bg-gray-100 rounded-full h-1.5 mt-1">
                            <div class="bg-indigo-500 h-1.5 rounded-full" style="width: {{ $milestone->completion_percentage }}%"></div>
                        </div>
                    </div>
                    <span class="text-xs px-2 py-1 rounded-full {{ $milestone->is_completed ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                        {{ $milestone->is_completed ? 'Done' : 'Pending' }}
                    </span>
                </div>
            </div>
            @empty
            <p class="text-sm text-gray-400">No milestones yet.</p>
            @endforelse
        </div>

        {{-- Budget Breakdown --}}
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="font-semibold text-gray-800 mb-4">Budget Breakdown ({{ $project->budgets->count() }})</h3>
            @forelse($project->budgets as $budget)
            <div class="flex items-center justify-between py-3 border-b border-gray-50 last:border-0">
                <div>
                    <p class="text-sm font-medium text-gray-800">{{ ucfirst($budget->category) }}</p>
                    <p class="text-xs text-gray-400">{{ $budget->description }}</p>
                </div>
                <div class="text-right">
                    <p class="text-sm font-bold text-gray-800">₱{{ number_format($budget->estimated_amount, 2) }}</p>
                    <p class="text-xs text-gray-400">Actual: ₱{{ number_format($budget->actual_amount, 2) }}</p>
                </div>
            </div>
            @empty
            <p class="text-sm text-gray-400">No budget entries yet.</p>
            @endforelse
        </div>

        {{-- Progress Logs --}}
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="font-semibold text-gray-800 mb-4">Progress Logs ({{ $project->progressLogs->count() }})</h3>
            @forelse($project->progressLogs->sortByDesc('log_date') as $log)
            <div class="py-3 border-b border-gray-50 last:border-0">
                <div class="flex items-center justify-between mb-1">
                    <p class="text-sm font-medium text-gray-800">{{ \Carbon\Carbon::parse($log->log_date)->format('M d, Y') }}</p>
                    <span class="text-xs bg-indigo-50 text-indigo-600 px-2 py-0.5 rounded-full">{{ $log->completion_percentage }}% complete</span>
                </div>
                <p class="text-sm text-gray-600">{{ $log->description }}</p>
                @if($log->workers_count || $log->hours_worked)
                <p class="text-xs text-gray-400 mt-1">
                    @if($log->workers_count) {{ $log->workers_count }} workers @endif
                    @if($log->hours_worked) · {{ $log->hours_worked }} hrs @endif
                    @if($log->weather_conditions) · {{ $log->weather_conditions }} @endif
                </p>
                @endif
            </div>
            @empty
            <p class="text-sm text-gray-400">No progress logs yet.</p>
            @endforelse
        </div>

        {{-- Issues & Delays --}}
        @php $openIssues = $project->issues->whereIn('status', ['open','in_progress']); @endphp
        <div class="bg-white rounded-xl shadow-sm p-6 {{ $openIssues->count() ? 'border-l-4 border-red-400' : '' }}">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-gray-800 flex items-center gap-2">
                    <i class="fas fa-exclamation-triangle {{ $openIssues->count() ? 'text-red-500' : 'text-gray-300' }}"></i>
                    Issues & Delays ({{ $project->issues->count() }})
                    @if($openIssues->count())
                        <span class="text-xs bg-red-100 text-red-700 px-2 py-0.5 rounded-full">{{ $openIssues->count() }} open</span>
                    @endif
                </h3>
                @if(auth()->user()->isAdmin())
                    <a href="{{ route('project-issues.index', ['project_id' => $project->id]) }}" class="text-xs text-indigo-600 hover:underline">View All</a>
                @endif
            </div>

            {{-- Total impact days warning --}}
            @php $totalImpact = $project->totalImpactDays(); @endphp
            @if($totalImpact > 0)
            <div class="bg-red-50 border border-red-200 rounded-xl p-3 mb-4 flex items-center gap-2">
                <i class="fas fa-clock text-red-500"></i>
                <p class="text-sm text-red-700 font-medium">Total estimated delay from open issues: <strong>{{ $totalImpact }} day(s)</strong></p>
            </div>
            @endif

            {{-- Existing issues list --}}
            @forelse($project->issues->sortByDesc('created_at')->take(5) as $issue)
            <div class="py-3 border-b border-gray-50 last:border-0">
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <p class="text-sm font-medium text-gray-800">{{ $issue->title }}</p>
                        <p class="text-xs text-gray-500 mt-0.5">{{ Str::limit($issue->description, 80) }}</p>
                        <div class="flex items-center gap-2 mt-1">
                            <span class="text-xs px-2 py-0.5 rounded-full font-medium
                                {{ $issue->severity === 'critical' ? 'bg-red-100 text-red-700'       : '' }}
                                {{ $issue->severity === 'high'     ? 'bg-orange-100 text-orange-700' : '' }}
                                {{ $issue->severity === 'medium'   ? 'bg-yellow-100 text-yellow-700' : '' }}
                                {{ $issue->severity === 'low'      ? 'bg-gray-100 text-gray-600'     : '' }}">
                                {{ ucfirst($issue->severity) }}
                            </span>
                            @if($issue->impact_days > 0)
                                <span class="text-xs text-red-500"><i class="fas fa-clock mr-0.5"></i>{{ $issue->impact_days }}d delay</span>
                            @endif
                        </div>
                    </div>
                    <span class="text-xs px-2 py-0.5 rounded-full flex-shrink-0
                        {{ $issue->status === 'open'        ? 'bg-red-100 text-red-700'     : '' }}
                        {{ $issue->status === 'in_progress' ? 'bg-blue-100 text-blue-700'   : '' }}
                        {{ $issue->status === 'resolved'    ? 'bg-green-100 text-green-700' : '' }}
                        {{ $issue->status === 'dismissed'   ? 'bg-gray-100 text-gray-500'   : '' }}">
                        {{ ucfirst(str_replace('_',' ',$issue->status)) }}
                    </span>
                </div>
            </div>
            @empty
            <p class="text-sm text-gray-400">No issues reported for this project.</p>
            @endforelse

            {{-- Report Issue Form (staff + admin) --}}
            @if(auth()->user()->isAdmin() || auth()->user()->isContractor())
            <div class="mt-4 pt-4 border-t border-gray-100">
                <button onclick="toggleIssueForm()" class="text-sm text-red-600 hover:text-red-700 font-medium flex items-center gap-2">
                    <i class="fas fa-plus-circle"></i> Report New Issue
                </button>
                <div id="issueForm" class="hidden mt-4">
                    <form method="POST" action="{{ route('project-issues.store', $project) }}" class="space-y-3">
                        @csrf
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Type</label>
                                <select name="type" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-red-400">
                                    @foreach(['delay' => 'Delay', 'material_shortage' => 'Material Shortage', 'safety' => 'Safety', 'quality' => 'Quality', 'weather' => 'Weather', 'other' => 'Other'] as $val => $label)
                                        <option value="{{ $val }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Severity</label>
                                <select name="severity" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-red-400">
                                    @foreach(['low' => 'Low', 'medium' => 'Medium', 'high' => 'High', 'critical' => 'Critical'] as $val => $label)
                                        <option value="{{ $val }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Title</label>
                            <input type="text" name="title" placeholder="Brief issue title" required
                                class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-red-400">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Description</label>
                            <textarea name="description" rows="2" placeholder="Describe the issue in detail..." required
                                class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-red-400 resize-none"></textarea>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Estimated Delay (days) <span class="text-gray-400">(0 if none)</span></label>
                            <input type="number" name="impact_days" value="0" min="0"
                                class="w-32 px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-red-400">
                        </div>
                        <div class="flex gap-2">
                            <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-red-700 transition font-medium">
                                <i class="fas fa-flag mr-1"></i> Submit Issue
                            </button>
                            <button type="button" onclick="toggleIssueForm()" class="bg-gray-100 text-gray-600 px-4 py-2 rounded-lg text-sm hover:bg-gray-200 transition">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
            @endif
        </div>

    </div>
</div>

<script>
function toggleIssueForm() {
    document.getElementById('issueForm').classList.toggle('hidden');
}
</script>
@endsection
