@extends('layouts.app')

@section('title', $project->name . ' - EstateFlow')
@section('page-title', $project->name)
@section('page-subtitle', $project->property->title ?? 'Construction Project')

@section('content')

@php
    $start       = $project->start_date;
    $target      = $project->estimated_completion_date;
    $actual      = $project->actual_completion_date;
    $today       = now();
    $totalDays   = $start && $target ? $start->diffInDays($target) : 0;
    $elapsed     = $start ? min($start->diffInDays($today), max($totalDays, 1)) : 0;
    $timePercent = $totalDays > 0 ? round(($elapsed / $totalDays) * 100) : 0;
    $isOnTrack   = $project->completion_percentage >= ($timePercent - 10);
    $isOverdue   = $target && $target->isPast() && $project->status !== 'completed';
    $daysLeft    = $target ? (int) $today->diffInDays($target, false) : null;
@endphp

{{-- Back + Log Progress --}}
<div class="flex items-center justify-between mb-6">
    <a href="{{ route('contractor.projects') }}"
        class="flex items-center gap-2 text-sm text-gray-500 hover:text-indigo-600 transition">
        <i class="fas fa-arrow-left"></i> Back to Projects
    </a>
    <a href="{{ route('progress-logs.create') }}?project_id={{ $project->id }}"
        class="flex items-center gap-2 bg-green-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-green-700 transition font-medium">
        <i class="fas fa-camera mr-1"></i> Upload Progress
    </a>
</div>

{{-- Construction Timeline Card --}}
<div class="bg-white rounded-2xl shadow-sm p-6 mb-6">
    <div class="flex items-start justify-between mb-4">
        <div>
            <h2 class="text-lg font-bold text-gray-800">{{ $project->name }}</h2>
            @if($project->property)
                <p class="text-sm text-gray-400 mt-0.5"><i class="fas fa-building mr-1"></i>{{ $project->property->title }}</p>
            @endif
        </div>
        <div class="text-right">
            @if($project->status === 'completed')
                <span class="text-xs bg-green-100 text-green-700 px-3 py-1.5 rounded-full font-semibold">
                    <i class="fas fa-check-circle mr-1"></i>Completed
                </span>
            @elseif($isOverdue)
                <span class="text-xs bg-red-100 text-red-700 px-3 py-1.5 rounded-full font-semibold">
                    <i class="fas fa-exclamation-circle mr-1"></i>Overdue
                </span>
            @elseif($isOnTrack)
                <span class="text-xs bg-green-100 text-green-700 px-3 py-1.5 rounded-full font-semibold">
                    <i class="fas fa-check mr-1"></i>On Track
                </span>
            @else
                <span class="text-xs bg-yellow-100 text-yellow-700 px-3 py-1.5 rounded-full font-semibold">
                    <i class="fas fa-exclamation mr-1"></i>Behind Schedule
                </span>
            @endif
        </div>
    </div>

    {{-- Overall Progress --}}
    <div class="mb-2 flex items-center justify-between text-sm">
        <span class="text-gray-500">Overall Completion</span>
        <span class="font-bold text-indigo-600 text-lg">{{ $project->completion_percentage }}%</span>
    </div>
    <div class="w-full bg-gray-100 rounded-full h-3 mb-4 relative overflow-hidden">
        {{-- Time elapsed (background) --}}
        <div class="h-3 bg-gray-200 rounded-full absolute top-0 left-0" style="width: {{ $timePercent }}%"></div>
        {{-- Actual completion (foreground) --}}
        <div class="h-3 rounded-full absolute top-0 left-0 transition-all
            {{ $project->completion_percentage >= 100 ? 'bg-green-500' : ($isOnTrack ? 'bg-indigo-600' : 'bg-red-400') }}"
            style="width: {{ $project->completion_percentage }}%"></div>
    </div>

    {{-- Date Row --}}
    <div class="grid grid-cols-3 gap-4 text-center text-sm">
        <div class="bg-gray-50 rounded-xl p-3">
            <p class="text-xs text-gray-400 mb-1">Start Date</p>
            <p class="font-semibold text-gray-800">{{ $start?->format('M d, Y') ?? '—' }}</p>
        </div>
        <div class="bg-{{ $isOverdue ? 'red' : 'indigo' }}-50 rounded-xl p-3">
            <p class="text-xs text-{{ $isOverdue ? 'red' : 'indigo' }}-400 mb-1">Target Completion</p>
            <p class="font-semibold text-{{ $isOverdue ? 'red' : 'indigo' }}-700">{{ $target?->format('M d, Y') ?? '—' }}</p>
            @if($daysLeft !== null)
                <p class="text-xs {{ $daysLeft < 0 ? 'text-red-500 font-medium' : 'text-gray-400' }} mt-0.5">
                    {{ $daysLeft < 0 ? abs($daysLeft) . ' days overdue' : $daysLeft . ' days left' }}
                </p>
            @endif
        </div>
        <div class="bg-{{ $actual ? 'green' : 'gray' }}-50 rounded-xl p-3">
            <p class="text-xs text-{{ $actual ? 'green' : 'gray' }}-400 mb-1">Actual Completion</p>
            <p class="font-semibold text-{{ $actual ? 'green' : 'gray' }}-700">
                {{ $actual?->format('M d, Y') ?? 'Not yet completed' }}
            </p>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Left: Milestones Timeline --}}
    <div class="lg:col-span-2 space-y-5">

        {{-- Milestones --}}
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-800">Construction Phases / Milestones</h3>
                <p class="text-xs text-gray-400 mt-0.5">Phases set by management based on the contract</p>
            </div>

            @forelse($project->milestones->sortBy('target_date') as $index => $milestone)
            @php
                $mOverdue = !$milestone->is_completed && $milestone->target_date->isPast();
            @endphp
            <div class="flex items-start gap-4 px-6 py-4 border-b border-gray-50 last:border-0">
                {{-- Step Number / Check --}}
                <div class="flex flex-col items-center flex-shrink-0">
                    <div class="w-9 h-9 rounded-full flex items-center justify-center font-bold text-sm
                        {{ $milestone->is_completed ? 'bg-green-500 text-white' : ($mOverdue ? 'bg-red-100 text-red-600' : 'bg-indigo-100 text-indigo-600') }}">
                        @if($milestone->is_completed)
                            <i class="fas fa-check text-xs"></i>
                        @else
                            {{ $index + 1 }}
                        @endif
                    </div>
                    @if(!$loop->last)
                        <div class="w-0.5 h-8 {{ $milestone->is_completed ? 'bg-green-300' : 'bg-gray-200' }} mt-1"></div>
                    @endif
                </div>

                {{-- Milestone Info --}}
                <div class="flex-1 min-w-0 pb-2">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <p class="font-semibold text-gray-800 {{ $milestone->is_completed ? 'line-through text-gray-400' : '' }}">
                                {{ $milestone->name }}
                            </p>
                            @if($milestone->description)
                                <p class="text-xs text-gray-400 mt-0.5">{{ $milestone->description }}</p>
                            @endif
                        </div>
                        <span class="text-xs px-2.5 py-1 rounded-full font-medium flex-shrink-0
                            {{ $milestone->is_completed ? 'bg-green-100 text-green-700' : ($mOverdue ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-600') }}">
                            {{ $milestone->is_completed ? 'Completed' : ($mOverdue ? 'Overdue' : 'Pending') }}
                        </span>
                    </div>

                    <div class="flex items-center gap-4 mt-2 text-xs text-gray-400">
                        <span><i class="fas fa-calendar-alt mr-1"></i>Target: <span class="{{ $mOverdue ? 'text-red-500 font-medium' : '' }}">{{ $milestone->target_date->format('M d, Y') }}</span></span>
                        @if($milestone->actual_date)
                            <span class="text-green-600"><i class="fas fa-check-circle mr-1"></i>Done: {{ $milestone->actual_date->format('M d, Y') }}</span>
                        @endif
                        @if($milestone->completion_percentage > 0)
                            <span class="text-indigo-600 font-medium">{{ $milestone->completion_percentage }}%</span>
                        @endif
                    </div>

                    @if($milestone->notes)
                        <p class="text-xs text-gray-500 mt-1 bg-gray-50 rounded-lg px-2 py-1">{{ $milestone->notes }}</p>
                    @endif
                </div>
            </div>
            @empty
            <div class="px-6 py-10 text-center text-gray-400 text-sm">
                <i class="fas fa-flag text-2xl mb-2 block text-gray-200"></i>
                No milestones set yet. Admin will add construction phases.
            </div>
            @endforelse
        </div>

        {{-- Progress Logs --}}
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <div>
                    <h3 class="font-semibold text-gray-800">Progress Updates</h3>
                    <p class="text-xs text-gray-400 mt-0.5">{{ $project->progressLogs->count() }} update(s) submitted</p>
                </div>
                <a href="{{ route('progress-logs.create') }}?project_id={{ $project->id }}"
                    class="text-xs bg-green-600 text-white px-3 py-1.5 rounded-lg hover:bg-green-700 transition font-medium">
                    <i class="fas fa-camera mr-1"></i> Upload
                </a>
            </div>

            @forelse($project->progressLogs->sortByDesc('log_date') as $log)
            <div class="px-6 py-4 border-b border-gray-50 last:border-0">
                <div class="flex items-start justify-between mb-2">
                    <div>
                        <p class="text-sm font-semibold text-gray-800">{{ $log->log_date->format('F d, Y') }}</p>
                        <p class="text-xs text-gray-400">by {{ $log->user->name ?? '—' }}</p>
                    </div>
                    <span class="text-sm font-bold text-indigo-600">{{ $log->completion_percentage }}%</span>
                </div>

                <p class="text-sm text-gray-600 mb-3">{{ $log->description }}</p>

                {{-- Stats --}}
                @if($log->workers_count || $log->hours_worked || $log->weather_conditions)
                <div class="flex items-center gap-4 text-xs text-gray-400 mb-3">
                    @if($log->workers_count)
                        <span><i class="fas fa-users mr-1"></i>{{ $log->workers_count }} workers</span>
                    @endif
                    @if($log->hours_worked)
                        <span><i class="fas fa-clock mr-1"></i>{{ $log->hours_worked }} hrs</span>
                    @endif
                    @if($log->weather_conditions)
                        <span><i class="fas fa-cloud-sun mr-1"></i>{{ $log->weather_conditions }}</span>
                    @endif
                </div>
                @endif

                {{-- Issues --}}
                @if($log->issues)
                <div class="bg-red-50 rounded-lg px-3 py-2 mb-3">
                    <p class="text-xs text-red-600"><i class="fas fa-exclamation-triangle mr-1"></i>{{ $log->issues }}</p>
                </div>
                @endif

                {{-- Photos --}}
                @if($log->image_path || ($log->images && count($log->images)))
                <div class="flex gap-2 flex-wrap">
                    @if($log->image_path)
                        <a href="{{ asset('storage/' . $log->image_path) }}" target="_blank">
                            <img src="{{ asset('storage/' . $log->image_path) }}"
                                class="w-20 h-20 object-cover rounded-xl border border-gray-100 hover:opacity-80 transition">
                        </a>
                    @endif
                    @if($log->images)
                        @foreach($log->images as $img)
                        <a href="{{ asset('storage/' . $img) }}" target="_blank">
                            <img src="{{ asset('storage/' . $img) }}"
                                class="w-20 h-20 object-cover rounded-xl border border-gray-100 hover:opacity-80 transition">
                        </a>
                        @endforeach
                    @endif
                </div>
                @endif
            </div>
            @empty
            <div class="px-6 py-10 text-center text-gray-400 text-sm">
                <i class="fas fa-camera text-2xl mb-2 block text-gray-200"></i>
                No progress updates yet.
                <a href="{{ route('progress-logs.create') }}?project_id={{ $project->id }}"
                    class="block mt-2 text-green-600 hover:underline">Upload first progress photo →</a>
            </div>
            @endforelse
        </div>
    </div>

    {{-- Right: Project Info + Construction Company --}}
    <div class="space-y-4">

        {{-- Project Info --}}
        <div class="bg-white rounded-xl shadow-sm p-5">
            <h3 class="font-semibold text-gray-800 mb-3 text-sm">Project Info</h3>
            <div class="space-y-2.5 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-400">Status</span>
                    <span class="font-medium text-gray-800 capitalize">{{ str_replace('_', ' ', $project->status) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-400">Milestones</span>
                    <span class="font-medium text-gray-800">{{ $completedMilestones }}/{{ $project->milestones->count() }} done</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-400">Progress Logs</span>
                    <span class="font-medium text-gray-800">{{ $project->progressLogs->count() }}</span>
                </div>
                @if($project->notes)
                <div class="pt-2 border-t border-gray-100">
                    <p class="text-xs text-gray-400 mb-1">Notes</p>
                    <p class="text-xs text-gray-600">{{ $project->notes }}</p>
                </div>
                @endif
            </div>
        </div>

        {{-- Construction Company --}}
        @if($project->staff)
        <div class="bg-white rounded-xl shadow-sm p-5">
            <h3 class="font-semibold text-gray-800 mb-3 text-sm flex items-center gap-2">
                <i class="fas fa-hard-hat text-yellow-500"></i> Construction Company
            </h3>
            <div class="space-y-2 text-sm">
                <p class="font-semibold text-gray-800">{{ $project->staff->company_name }}</p>
                @if($project->staff->contact_person)
                    <p class="text-xs text-gray-500"><i class="fas fa-user mr-1 text-gray-300"></i>{{ $project->staff->contact_person }}</p>
                @endif
                @if($project->staff->phone)
                    <p class="text-xs text-gray-500"><i class="fas fa-phone mr-1 text-gray-300"></i>{{ $project->staff->phone }}</p>
                @endif
                @if($project->staff->specialization)
                    <p class="text-xs text-gray-500"><i class="fas fa-tools mr-1 text-gray-300"></i>{{ $project->staff->specialization }}</p>
                @endif
            </div>
        </div>
        @endif

    </div>
</div>

@endsection
