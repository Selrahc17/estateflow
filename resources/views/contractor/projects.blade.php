@extends('layouts.app')

@section('title', 'My Projects - EstateFlow')
@section('page-title', 'My Projects')
@section('page-subtitle', 'Projects assigned to you')

@section('content')

@if(!$contractorRecord && !auth()->user()->isAdmin())
<div class="mb-6 bg-yellow-50 border border-yellow-200 rounded-xl p-4">
    <p class="text-sm text-yellow-800">Your account is not linked to a staff profile. Contact an administrator.</p>
</div>
@endif

@if(method_exists($projects, 'count') && $projects->count())
<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
    @foreach($projects as $project)
    @php
        $completedTasks = $project->tasks->where('status','completed')->count();
        $totalTasks     = $project->tasks->count();
        $barColor = $project->completion_percentage >= 100 ? 'bg-green-500' : ($project->completion_percentage >= 60 ? 'bg-blue-500' : ($project->completion_percentage >= 30 ? 'bg-yellow-400' : 'bg-red-400'));
    @endphp
    <div class="bg-white rounded-2xl shadow-sm overflow-hidden hover:shadow-md transition">

        {{-- Status Bar --}}
        <div class="h-1.5 w-full bg-gray-100">
            <div class="{{ $barColor }} h-1.5 transition-all" style="width: {{ $project->completion_percentage }}%"></div>
        </div>

        <div class="p-5">
            {{-- Header --}}
            <div class="flex items-start justify-between mb-3">
                <div>
                    <h3 class="font-semibold text-gray-800">{{ $project->name }}</h3>
                    <p class="text-xs text-gray-400 mt-0.5">
                        <i class="fas fa-building mr-1"></i>{{ $project->property->title ?? 'No property' }}
                    </p>
                </div>
                <span class="text-xs px-2.5 py-1 rounded-full font-medium flex-shrink-0
                    {{ $project->status === 'in_progress' ? 'bg-blue-100 text-blue-700'    : '' }}
                    {{ $project->status === 'planning'    ? 'bg-gray-100 text-gray-600'    : '' }}
                    {{ $project->status === 'completed'   ? 'bg-green-100 text-green-700'  : '' }}
                    {{ $project->status === 'on_hold'     ? 'bg-yellow-100 text-yellow-700': '' }}
                    {{ $project->status === 'cancelled'   ? 'bg-red-100 text-red-700'      : '' }}">
                    {{ ucfirst(str_replace('_', ' ', $project->status)) }}
                </span>
            </div>

            {{-- Progress --}}
            <div class="mb-4">
                <div class="flex items-center justify-between text-xs text-gray-500 mb-1">
                    <span>Overall Progress</span>
                    <span class="font-semibold text-gray-700">{{ $project->completion_percentage }}%</span>
                </div>
                <div class="w-full bg-gray-100 rounded-full h-2">
                    <div class="{{ $barColor }} h-2 rounded-full" style="width: {{ $project->completion_percentage }}%"></div>
                </div>
            </div>

            {{-- Stats Row --}}
            <div class="grid grid-cols-3 gap-2 mb-4 text-center">
                <div class="bg-gray-50 rounded-lg py-2">
                    <p class="text-sm font-bold text-gray-800">{{ $totalTasks }}</p>
                    <p class="text-xs text-gray-400">Tasks</p>
                </div>
                <div class="bg-gray-50 rounded-lg py-2">
                    <p class="text-sm font-bold text-green-600">{{ $completedTasks }}</p>
                    <p class="text-xs text-gray-400">Done</p>
                </div>
                <div class="bg-gray-50 rounded-lg py-2">
                    <p class="text-sm font-bold text-indigo-600">{{ $project->progressLogs->count() }}</p>
                    <p class="text-xs text-gray-400">Logs</p>
                </div>
            </div>

            {{-- Timeline --}}
            @if($project->start_date || $project->estimated_completion_date)
            <div class="flex items-center gap-2 text-xs text-gray-400 mb-4">
                <i class="fas fa-calendar text-gray-300"></i>
                {{ $project->start_date?->format('M d, Y') ?? '—' }}
                <i class="fas fa-arrow-right text-gray-300"></i>
                {{ $project->estimated_completion_date?->format('M d, Y') ?? '—' }}
                @if($project->estimated_completion_date && $project->estimated_completion_date->isPast() && $project->status !== 'completed')
                    <span class="text-red-500 font-medium"><i class="fas fa-exclamation-circle mr-1"></i>Overdue</span>
                @endif
            </div>
            @endif

            {{-- Actions --}}
            <div class="flex gap-2">
                <a href="{{ route('contractor.project.detail', $project) }}"
                    class="flex-1 text-center text-xs bg-indigo-600 text-white py-2 rounded-lg hover:bg-indigo-700 transition font-medium">
                    <i class="fas fa-eye mr-1"></i> View Details
                </a>
                <a href="{{ route('progress-logs.create') }}?project_id={{ $project->id }}"
                    class="flex-1 text-center text-xs bg-green-50 text-green-600 py-2 rounded-lg hover:bg-green-100 transition font-medium">
                    <i class="fas fa-plus mr-1"></i> Log Progress
                </a>
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
