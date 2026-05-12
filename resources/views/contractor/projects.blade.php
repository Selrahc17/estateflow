@extends('layouts.app')

@section('title', 'My Projects - EstateFlow')
@section('page-title', 'My Projects')
@section('page-subtitle', 'Construction projects assigned to you')

@section('content')

@if(!$contractorRecord && !auth()->user()->isAdmin())
<div class="mb-6 bg-yellow-50 border border-yellow-200 rounded-xl p-4">
    <p class="text-sm text-yellow-800">Your account is not linked to a staff profile. Contact an administrator.</p>
</div>
@endif

@if(method_exists($projects, 'count') && $projects->count())
<div class="space-y-4">
    @foreach($projects as $project)
    @php
        $start       = $project->start_date;
        $target      = $project->estimated_completion_date;
        $today       = now();
        $totalDays   = $start && $target ? $start->diffInDays($target) : 0;
        $elapsed     = $start ? min($start->diffInDays($today), max($totalDays, 1)) : 0;
        $timePercent = $totalDays > 0 ? round(($elapsed / $totalDays) * 100) : 0;
        $isOnTrack   = $project->completion_percentage >= ($timePercent - 10);
        $isOverdue   = $target && $target->isPast() && $project->status !== 'completed';
        $daysLeft    = $target ? (int) $today->diffInDays($target, false) : null;
        $completedMilestones = $project->milestones->where('is_completed', true)->count();
        $totalMilestones     = $project->milestones->count();
    @endphp
    <div class="bg-white rounded-2xl shadow-sm overflow-hidden hover:shadow-md transition">

        {{-- Color bar --}}
        <div class="h-1.5 w-full bg-gray-100">
            <div class="h-1.5 {{ $project->completion_percentage >= 100 ? 'bg-green-500' : ($isOnTrack ? 'bg-indigo-500' : 'bg-red-400') }}"
                style="width: {{ $project->completion_percentage }}%"></div>
        </div>

        <div class="p-6">
            <div class="flex items-start justify-between gap-4 mb-4">
                <div>
                    <h3 class="font-semibold text-gray-800 text-lg">{{ $project->name }}</h3>
                    @if($project->property)
                        <p class="text-xs text-gray-400 mt-0.5"><i class="fas fa-building mr-1"></i>{{ $project->property->title }}</p>
                    @endif
                </div>
                <div class="flex items-center gap-2 flex-shrink-0">
                    @if($project->status === 'completed')
                        <span class="text-xs bg-green-100 text-green-700 px-2.5 py-1 rounded-full font-medium"><i class="fas fa-check-circle mr-1"></i>Completed</span>
                    @elseif($isOverdue)
                        <span class="text-xs bg-red-100 text-red-700 px-2.5 py-1 rounded-full font-medium"><i class="fas fa-exclamation-circle mr-1"></i>Overdue</span>
                    @elseif($isOnTrack)
                        <span class="text-xs bg-green-100 text-green-700 px-2.5 py-1 rounded-full font-medium"><i class="fas fa-check mr-1"></i>On Track</span>
                    @else
                        <span class="text-xs bg-yellow-100 text-yellow-700 px-2.5 py-1 rounded-full font-medium"><i class="fas fa-exclamation mr-1"></i>Behind</span>
                    @endif
                    <span class="text-lg font-bold text-indigo-600">{{ $project->completion_percentage }}%</span>
                </div>
            </div>

            {{-- Timeline --}}
            <div class="flex items-center gap-3 mb-4">
                <div class="text-center flex-shrink-0">
                    <p class="text-xs text-gray-400">Start</p>
                    <p class="text-xs font-semibold text-gray-700">{{ $start?->format('M d, Y') ?? '—' }}</p>
                </div>
                <div class="flex-1">
                    <div class="relative h-2 bg-gray-100 rounded-full">
                        <div class="h-2 bg-gray-200 rounded-full absolute top-0 left-0" style="width: {{ $timePercent }}%"></div>
                        <div class="h-2 rounded-full absolute top-0 left-0 {{ $isOnTrack ? 'bg-indigo-500' : 'bg-red-400' }}"
                            style="width: {{ $project->completion_percentage }}%"></div>
                    </div>
                    @if($daysLeft !== null)
                    <p class="text-xs text-center mt-1 {{ $daysLeft < 0 ? 'text-red-500 font-medium' : 'text-gray-400' }}">
                        {{ $daysLeft < 0 ? abs($daysLeft) . ' days overdue' : $daysLeft . ' days remaining' }}
                    </p>
                    @endif
                </div>
                <div class="text-center flex-shrink-0">
                    <p class="text-xs text-gray-400">Target</p>
                    <p class="text-xs font-semibold {{ $isOverdue ? 'text-red-600' : 'text-gray-700' }}">{{ $target?->format('M d, Y') ?? '—' }}</p>
                </div>
            </div>

            {{-- Stats + Actions --}}
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4 text-xs text-gray-500">
                    <span><i class="fas fa-flag mr-1 text-indigo-400"></i>{{ $completedMilestones }}/{{ $totalMilestones }} milestones</span>
                    <span><i class="fas fa-camera mr-1 text-green-400"></i>{{ $project->progressLogs->count() }} updates</span>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('progress-logs.create') }}?project_id={{ $project->id }}"
                        class="text-xs bg-green-50 text-green-600 px-3 py-1.5 rounded-lg hover:bg-green-100 transition font-medium">
                        <i class="fas fa-camera mr-1"></i>Upload
                    </a>
                    <a href="{{ route('contractor.project.detail', $project) }}"
                        class="text-xs bg-indigo-600 text-white px-3 py-1.5 rounded-lg hover:bg-indigo-700 transition font-medium">
                        <i class="fas fa-eye mr-1"></i>View
                    </a>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

@if(method_exists($projects, 'hasPages') && $projects->hasPages())
    <div class="mt-6">{{ $projects->links() }}</div>
@endif

@else
<div class="bg-white rounded-xl shadow-sm p-16 text-center text-gray-400">
    <i class="fas fa-project-diagram text-4xl mb-3 block text-gray-200"></i>
    <p>No projects assigned to you yet.</p>
</div>
@endif

@endsection
