@extends('layouts.app')

@section('title', $project->name . ' - EstateFlow')
@section('page-title', $project->name)
@section('page-subtitle', $project->property->title ?? 'Construction Project')

@section('content')

{{-- Back --}}
<div class="flex items-center justify-between mb-6">
    <a href="{{ route('contractor.projects') }}"
        class="flex items-center gap-2 text-sm text-gray-500 hover:text-indigo-600 transition">
        <i class="fas fa-arrow-left"></i> Back to Projects
    </a>
    <a href="{{ route('progress-logs.create') }}?project_id={{ $project->id }}"
        class="flex items-center gap-2 bg-green-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-green-700 transition font-medium">
        <i class="fas fa-plus"></i> Log Progress
    </a>
</div>

{{-- Summary Cards --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-xl shadow-sm p-5">
        <p class="text-xs text-gray-400 mb-1">Overall Progress</p>
        <p class="text-2xl font-bold text-indigo-600">{{ $project->completion_percentage }}%</p>
        <div class="w-full bg-gray-100 rounded-full h-1.5 mt-2">
            <div class="h-1.5 rounded-full {{ $project->completion_percentage >= 100 ? 'bg-green-500' : 'bg-indigo-500' }}"
                style="width: {{ $project->completion_percentage }}%"></div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-5">
        <p class="text-xs text-gray-400 mb-1">Tasks</p>
        <p class="text-2xl font-bold text-gray-800">{{ $completedTasks }}/{{ $totalTasks }}</p>
        <p class="text-xs text-green-600 mt-1">{{ $totalTasks > 0 ? round(($completedTasks/$totalTasks)*100) : 0 }}% completed</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-5">
        <p class="text-xs text-gray-400 mb-1">Milestones</p>
        <p class="text-2xl font-bold text-gray-800">{{ $completedMilestones }}/{{ $project->milestones->count() }}</p>
        <p class="text-xs text-indigo-600 mt-1">completed</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-5">
        <p class="text-xs text-gray-400 mb-1">Status</p>
        <span class="text-sm px-2.5 py-1 rounded-full font-medium
            {{ $project->status === 'in_progress' ? 'bg-blue-100 text-blue-700'    : '' }}
            {{ $project->status === 'planning'    ? 'bg-gray-100 text-gray-600'    : '' }}
            {{ $project->status === 'completed'   ? 'bg-green-100 text-green-700'  : '' }}
            {{ $project->status === 'on_hold'     ? 'bg-yellow-100 text-yellow-700': '' }}
            {{ $project->status === 'cancelled'   ? 'bg-red-100 text-red-700'      : '' }}">
            {{ ucfirst(str_replace('_', ' ', $project->status)) }}
        </span>
        @if($project->estimated_completion_date)
            <p class="text-xs text-gray-400 mt-2">
                Due {{ $project->estimated_completion_date->format('M d, Y') }}
                @if($project->estimated_completion_date->isPast() && $project->status !== 'completed')
                    <span class="text-red-500 font-medium">· Overdue</span>
                @endif
            </p>
        @endif
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Left: Tasks + Milestones --}}
    <div class="lg:col-span-2 space-y-5">

        {{-- Tasks --}}
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="font-semibold text-gray-800">Tasks <span class="text-gray-400 font-normal text-sm">({{ $totalTasks }})</span></h3>
            </div>
            @forelse($project->tasks as $task)
            @php $isOverdue = $task->due_date && $task->due_date->isPast() && $task->status !== 'completed'; @endphp
            <div class="flex items-center justify-between px-6 py-3.5 border-b border-gray-50 last:border-0 hover:bg-gray-50 transition">
                <div class="flex items-center gap-3 flex-1 min-w-0">
                    {{-- Status dot --}}
                    <div class="w-2.5 h-2.5 rounded-full flex-shrink-0
                        {{ $task->status === 'completed'   ? 'bg-green-500' : '' }}
                        {{ $task->status === 'in_progress' ? 'bg-blue-500'  : '' }}
                        {{ $task->status === 'pending'     ? 'bg-gray-300'  : '' }}
                        {{ $task->status === 'cancelled'   ? 'bg-red-400'   : '' }}">
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-gray-800 {{ $task->status === 'completed' ? 'line-through text-gray-400' : '' }}">
                            {{ $task->title }}
                        </p>
                        <div class="flex items-center gap-3 text-xs text-gray-400 mt-0.5">
                            @if($task->due_date)
                                <span class="{{ $isOverdue ? 'text-red-500 font-medium' : '' }}">
                                    Due {{ $task->due_date->format('M d, Y') }}
                                    @if($isOverdue) · Overdue @endif
                                </span>
                            @endif
                            <span class="text-xs px-1.5 py-0.5 rounded font-medium
                                {{ $task->priority === 'urgent' ? 'bg-red-100 text-red-600'       : '' }}
                                {{ $task->priority === 'high'   ? 'bg-orange-100 text-orange-600' : '' }}
                                {{ $task->priority === 'medium' ? 'bg-blue-100 text-blue-600'     : '' }}
                                {{ $task->priority === 'low'    ? 'bg-gray-100 text-gray-500'     : '' }}">
                                {{ ucfirst($task->priority) }}
                            </span>
                        </div>
                    </div>
                </div>
                {{-- Inline status buttons --}}
                <div class="flex items-center gap-1.5 flex-shrink-0 ml-3">
                    @if($task->status === 'pending')
                        <form method="POST" action="{{ route('tasks.update-status', $task) }}">
                            @csrf @method('PATCH')
                            <input type="hidden" name="status" value="in_progress">
                            <button type="submit" class="text-xs bg-blue-50 text-blue-600 px-2.5 py-1.5 rounded-lg hover:bg-blue-100 transition">
                                <i class="fas fa-play mr-1"></i>Start
                            </button>
                        </form>
                    @elseif($task->status === 'in_progress')
                        <form method="POST" action="{{ route('tasks.update-status', $task) }}">
                            @csrf @method('PATCH')
                            <input type="hidden" name="status" value="completed">
                            <button type="submit" class="text-xs bg-green-50 text-green-600 px-2.5 py-1.5 rounded-lg hover:bg-green-100 transition">
                                <i class="fas fa-check mr-1"></i>Done
                            </button>
                        </form>
                    @elseif($task->status === 'completed')
                        <span class="text-xs text-green-500"><i class="fas fa-check-circle"></i></span>
                    @endif
                </div>
            </div>
            @empty
            <div class="px-6 py-8 text-center text-gray-400 text-sm">
                <i class="fas fa-tasks text-2xl mb-2 block text-gray-200"></i>
                No tasks for this project.
            </div>
            @endforelse
        </div>

        {{-- Milestones --}}
        @if($project->milestones->count())
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-800">Milestones</h3>
            </div>
            @foreach($project->milestones as $milestone)
            <div class="flex items-center justify-between px-6 py-3.5 border-b border-gray-50 last:border-0">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center
                        {{ $milestone->status === 'completed' ? 'bg-green-100' : 'bg-gray-100' }}">
                        <i class="fas {{ $milestone->status === 'completed' ? 'fa-check text-green-600' : 'fa-flag text-gray-400' }} text-xs"></i>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-800">{{ $milestone->title }}</p>
                        @if($milestone->due_date)
                            <p class="text-xs text-gray-400">Due {{ $milestone->due_date->format('M d, Y') }}</p>
                        @endif
                    </div>
                </div>
                <span class="text-xs px-2.5 py-1 rounded-full font-medium
                    {{ $milestone->status === 'completed'   ? 'bg-green-100 text-green-700'  : '' }}
                    {{ $milestone->status === 'pending'     ? 'bg-gray-100 text-gray-600'    : '' }}
                    {{ $milestone->status === 'in_progress' ? 'bg-blue-100 text-blue-700'    : '' }}">
                    {{ ucfirst(str_replace('_', ' ', $milestone->status)) }}
                </span>
            </div>
            @endforeach
        </div>
        @endif

    </div>

    {{-- Right: Progress Logs --}}
    <div class="space-y-4">

        {{-- Project Info --}}
        <div class="bg-white rounded-xl shadow-sm p-5">
            <h3 class="font-semibold text-gray-800 mb-3 text-sm">Project Info</h3>
            <div class="space-y-2 text-sm">
                @if($project->property)
                <div class="flex items-start gap-2">
                    <i class="fas fa-building text-gray-300 mt-0.5 w-4"></i>
                    <span class="text-gray-600">{{ $project->property->title }}</span>
                </div>
                @endif
                @if($project->client)
                <div class="flex items-start gap-2">
                    <i class="fas fa-user text-gray-300 mt-0.5 w-4"></i>
                    <span class="text-gray-600">{{ $project->client->full_name }}</span>
                </div>
                @endif
                @if($project->start_date)
                <div class="flex items-start gap-2">
                    <i class="fas fa-calendar-alt text-gray-300 mt-0.5 w-4"></i>
                    <span class="text-gray-600">{{ $project->start_date->format('M d, Y') }} → {{ $project->estimated_completion_date?->format('M d, Y') ?? 'TBD' }}</span>
                </div>
                @endif
                @if($project->notes)
                <div class="flex items-start gap-2 mt-2 pt-2 border-t border-gray-100">
                    <i class="fas fa-sticky-note text-gray-300 mt-0.5 w-4"></i>
                    <span class="text-gray-500 text-xs">{{ $project->notes }}</span>
                </div>
                @endif
            </div>
        </div>

        {{-- Progress Logs --}}
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="font-semibold text-gray-800 text-sm">Progress Logs</h3>
                <a href="{{ route('progress-logs.create') }}?project_id={{ $project->id }}"
                    class="text-xs bg-green-50 text-green-600 px-2.5 py-1.5 rounded-lg hover:bg-green-100 transition">
                    <i class="fas fa-plus mr-1"></i>Add
                </a>
            </div>
            @forelse($project->progressLogs->sortByDesc('log_date')->take(5) as $log)
            <div class="px-5 py-3.5 border-b border-gray-50 last:border-0">
                <div class="flex items-center justify-between mb-1">
                    <p class="text-xs font-medium text-gray-700">{{ $log->log_date->format('M d, Y') }}</p>
                    <span class="text-xs font-bold text-indigo-600">{{ $log->completion_percentage }}%</span>
                </div>
                <p class="text-xs text-gray-500 line-clamp-2">{{ Str::limit($log->description, 80) }}</p>
                {{-- Photos --}}
                @if($log->image_path || ($log->images && count($log->images)))
                <div class="flex gap-1.5 mt-2 flex-wrap">
                    @if($log->image_path)
                        <a href="{{ asset('storage/' . $log->image_path) }}" target="_blank">
                            <img src="{{ asset('storage/' . $log->image_path) }}"
                                class="w-14 h-14 object-cover rounded-lg border border-gray-100 hover:opacity-80 transition">
                        </a>
                    @endif
                    @if($log->images)
                        @foreach(array_slice($log->images, 0, 3) as $img)
                        <a href="{{ asset('storage/' . $img) }}" target="_blank">
                            <img src="{{ asset('storage/' . $img) }}"
                                class="w-14 h-14 object-cover rounded-lg border border-gray-100 hover:opacity-80 transition">
                        </a>
                        @endforeach
                        @if(count($log->images) > 3)
                            <div class="w-14 h-14 bg-gray-100 rounded-lg flex items-center justify-center text-xs text-gray-500 font-medium">
                                +{{ count($log->images) - 3 }}
                            </div>
                        @endif
                    @endif
                </div>
                @endif
                @if($log->issues)
                    <p class="text-xs text-red-500 mt-1"><i class="fas fa-exclamation-circle mr-1"></i>{{ Str::limit($log->issues, 50) }}</p>
                @endif
                <div class="flex items-center justify-between mt-1.5">
                    <p class="text-xs text-gray-400">by {{ $log->user->name ?? '—' }}</p>
                    <a href="{{ route('progress-logs.show', $log) }}" class="text-xs text-indigo-500 hover:underline">View →</a>
                </div>
            </div>
            @empty
            <div class="px-5 py-8 text-center text-gray-400 text-sm">
                <i class="fas fa-clipboard-list text-2xl mb-2 block text-gray-200"></i>
                No progress logs yet.
            </div>
            @endforelse
            @if($project->progressLogs->count() > 5)
                <div class="px-5 py-3 border-t border-gray-100">
                    <a href="{{ route('progress-logs.index', ['project_id' => $project->id]) }}"
                        class="text-xs text-indigo-600 hover:underline">
                        View all {{ $project->progressLogs->count() }} logs →
                    </a>
                </div>
            @endif
        </div>

    </div>
</div>

@endsection
