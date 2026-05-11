@extends('layouts.app')

@section('title', 'Progress Logs - EstateFlow')
@section('page-title', 'Progress Logs')
@section('page-subtitle', 'Daily construction progress records')

@section('content')

{{-- Stats --}}
<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
    <div class="bg-white rounded-xl shadow-sm p-6 flex items-center gap-4">
        <div class="w-12 h-12 bg-indigo-100 rounded-xl flex items-center justify-center">
            <i class="fas fa-clipboard-list text-indigo-600 text-xl"></i>
        </div>
        <div>
            <p class="text-sm text-gray-500">Total Logs</p>
            <p class="text-2xl font-bold text-gray-800">{{ $totalLogs }}</p>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-6 flex items-center gap-4">
        <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
            <i class="fas fa-project-diagram text-green-600 text-xl"></i>
        </div>
        <div>
            <p class="text-sm text-gray-500">Projects Tracked</p>
            <p class="text-2xl font-bold text-gray-800">{{ $projects->count() }}</p>
        </div>
    </div>
</div>

{{-- Filters --}}
<div class="bg-white rounded-xl shadow-sm p-5 mb-6">
    <form method="GET" action="{{ route('progress-logs.index') }}" class="flex flex-wrap gap-3 items-end">
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Search</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Description..."
                class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 w-56">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Project</label>
            <select name="project_id" class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">All Projects</option>
                @foreach($projects as $project)
                    <option value="{{ $project->id }}" {{ request('project_id') == $project->id ? 'selected' : '' }}>
                        {{ $project->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-indigo-700 transition">
            <i class="fas fa-search mr-1"></i> Filter
        </button>
        <a href="{{ route('progress-logs.index') }}" class="bg-gray-100 text-gray-600 px-4 py-2 rounded-lg text-sm hover:bg-gray-200 transition">Reset</a>
    </form>
</div>

{{-- Header Row --}}
<div class="flex items-center justify-between mb-4">
    <p class="text-sm text-gray-500">{{ $logs->total() }} logs found</p>
    @if(auth()->user()->isAdmin() || auth()->user()->isContractor())
        <a href="{{ route('progress-logs.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-indigo-700 transition">
            <i class="fas fa-plus mr-1"></i> Add Log
        </a>
    @endif
</div>

{{-- Logs --}}
<div class="space-y-4">
    @forelse($logs as $log)
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="flex items-start justify-between mb-3">
            <div>
                <div class="flex items-center gap-3">
                    <span class="text-sm font-bold text-gray-800">{{ $log->log_date->format('M d, Y') }}</span>
                    <a href="{{ route('projects.show', $log->project) }}" class="text-xs text-indigo-600 hover:underline">
                        <i class="fas fa-project-diagram mr-1"></i>{{ $log->project->name ?? '—' }}
                    </a>
                </div>
                <p class="text-xs text-gray-400 mt-0.5">Logged by {{ $log->user->name ?? 'Unknown' }}</p>
            </div>
            <div class="flex items-center gap-3">
                <div class="text-right">
                    <p class="text-xs text-gray-500">Completion</p>
                    <p class="text-sm font-bold text-indigo-600">{{ $log->completion_percentage }}%</p>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('progress-logs.show', $log) }}" class="text-xs px-3 py-1.5 bg-indigo-50 text-indigo-600 rounded-lg hover:bg-indigo-100 transition">View</a>
                    @if(auth()->user()->isAdmin() || auth()->user()->isContractor())
                        <a href="{{ route('progress-logs.edit', $log) }}" class="text-xs px-3 py-1.5 bg-gray-50 text-gray-600 rounded-lg hover:bg-gray-100 transition">Edit</a>
                        <form method="POST" action="{{ route('progress-logs.destroy', $log) }}"
                            onsubmit="return confirm('Delete this log?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-xs px-3 py-1.5 bg-gray-50 text-gray-500 rounded-lg hover:bg-red-50 hover:text-red-600 transition">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        <p class="text-sm text-gray-700 mb-3">{{ $log->description }}</p>

        <div class="flex flex-wrap gap-4 text-xs text-gray-500">
            @if($log->workers_count)
                <span><i class="fas fa-users mr-1"></i>{{ $log->workers_count }} workers</span>
            @endif
            @if($log->hours_worked)
                <span><i class="fas fa-clock mr-1"></i>{{ $log->hours_worked }} hrs</span>
            @endif
            @if($log->weather_conditions)
                <span><i class="fas fa-cloud-sun mr-1"></i>{{ $log->weather_conditions }}</span>
            @endif
            @if($log->issues)
                <span class="text-red-500"><i class="fas fa-exclamation-triangle mr-1"></i>Issues reported</span>
            @endif
        </div>
    </div>
    @empty
    <div class="bg-white rounded-xl shadow-sm p-16 text-center text-gray-400">
        <i class="fas fa-clipboard-list text-4xl mb-3 block text-gray-200"></i>
        No progress logs found.
    </div>
    @endforelse
</div>

@if($logs->hasPages())
    <div class="mt-6">{{ $logs->links() }}</div>
@endif

@endsection
