@extends('layouts.app')

@section('title', 'Milestones - EstateFlow')
@section('page-title', 'Milestones')
@section('page-subtitle', 'Track project milestones')

@section('content')

{{-- Stats --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
    <div class="bg-white rounded-xl shadow-sm p-6 flex items-center gap-4">
        <div class="w-12 h-12 bg-indigo-100 rounded-xl flex items-center justify-center">
            <i class="fas fa-flag text-indigo-600 text-xl"></i>
        </div>
        <div>
            <p class="text-sm text-gray-500">Total</p>
            <p class="text-2xl font-bold text-gray-800">{{ $totalMilestones }}</p>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-6 flex items-center gap-4">
        <div class="w-12 h-12 bg-yellow-100 rounded-xl flex items-center justify-center">
            <i class="fas fa-clock text-yellow-600 text-xl"></i>
        </div>
        <div>
            <p class="text-sm text-gray-500">Pending</p>
            <p class="text-2xl font-bold text-gray-800">{{ $pendingCount }}</p>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-6 flex items-center gap-4">
        <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
            <i class="fas fa-check-circle text-green-600 text-xl"></i>
        </div>
        <div>
            <p class="text-sm text-gray-500">Completed</p>
            <p class="text-2xl font-bold text-gray-800">{{ $completedCount }}</p>
        </div>
    </div>
</div>

{{-- Filters --}}
<div class="bg-white rounded-xl shadow-sm p-5 mb-6">
    <form method="GET" action="{{ route('milestones.index') }}" class="flex flex-wrap gap-3 items-end">
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Search</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Milestone name..."
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
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Status</label>
            <select name="status" class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">All</option>
                <option value="pending"   {{ request('status') === 'pending'   ? 'selected' : '' }}>Pending</option>
                <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
            </select>
        </div>
        <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-indigo-700 transition">
            <i class="fas fa-search mr-1"></i> Filter
        </button>
        <a href="{{ route('milestones.index') }}" class="bg-gray-100 text-gray-600 px-4 py-2 rounded-lg text-sm hover:bg-gray-200 transition">Reset</a>
    </form>
</div>

{{-- Header Row --}}
<div class="flex items-center justify-between mb-4">
    <p class="text-sm text-gray-500">{{ $milestones->total() }} milestones found</p>
    @if(auth()->user()->isAdmin() || auth()->user()->isContractor())
        <a href="{{ route('milestones.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-indigo-700 transition">
            <i class="fas fa-plus mr-1"></i> Add Milestone
        </a>
    @endif
</div>

{{-- Table --}}
<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-100">
            <tr>
                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Milestone</th>
                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Project</th>
                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Target Date</th>
                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Progress</th>
                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                <th class="text-right px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @forelse($milestones as $milestone)
            <tr class="hover:bg-gray-50 transition">
                <td class="px-6 py-4">
                    <p class="font-medium text-gray-800">{{ $milestone->name }}</p>
                    @if($milestone->description)
                        <p class="text-xs text-gray-400">{{ Str::limit($milestone->description, 60) }}</p>
                    @endif
                </td>
                <td class="px-6 py-4">
                    <a href="{{ route('projects.show', $milestone->project) }}" class="text-indigo-600 hover:underline text-xs">
                        {{ $milestone->project->name ?? '—' }}
                    </a>
                </td>
                <td class="px-6 py-4">
                    <p class="{{ $milestone->target_date->isPast() && !$milestone->is_completed ? 'text-red-600 font-medium' : 'text-gray-700' }}">
                        {{ $milestone->target_date->format('M d, Y') }}
                    </p>
                    @if($milestone->actual_date)
                        <p class="text-xs text-green-600">Done: {{ $milestone->actual_date->format('M d, Y') }}</p>
                    @endif
                </td>
                <td class="px-6 py-4">
                    <div class="flex items-center gap-2">
                        <div class="w-20 bg-gray-100 rounded-full h-2">
                            <div class="h-2 rounded-full {{ $milestone->is_completed ? 'bg-green-500' : 'bg-indigo-500' }}"
                                style="width: {{ $milestone->completion_percentage }}%"></div>
                        </div>
                        <span class="text-xs text-gray-500">{{ $milestone->completion_percentage }}%</span>
                    </div>
                </td>
                <td class="px-6 py-4">
                    <span class="text-xs px-2.5 py-1 rounded-full font-medium {{ $milestone->is_completed ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                        {{ $milestone->is_completed ? 'Completed' : 'Pending' }}
                    </span>
                </td>
                <td class="px-6 py-4">
                    <div class="flex items-center justify-end gap-2">
                        <a href="{{ route('milestones.show', $milestone) }}" class="text-xs px-3 py-1.5 bg-indigo-50 text-indigo-600 rounded-lg hover:bg-indigo-100 transition">View</a>
                        @if(auth()->user()->isAdmin() || auth()->user()->isContractor())
                            <a href="{{ route('milestones.edit', $milestone) }}" class="text-xs px-3 py-1.5 bg-gray-50 text-gray-600 rounded-lg hover:bg-gray-100 transition">Edit</a>
                            <form method="POST" action="{{ route('milestones.destroy', $milestone) }}"
                                onsubmit="return confirm('Delete this milestone?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-xs px-3 py-1.5 bg-gray-50 text-gray-500 rounded-lg hover:bg-red-50 hover:text-red-600 transition">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="px-6 py-16 text-center text-gray-400">
                    <i class="fas fa-flag text-4xl mb-3 block text-gray-200"></i>
                    No milestones found.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    @if($milestones->hasPages())
        <div class="px-6 py-4 border-t border-gray-100">{{ $milestones->links() }}</div>
    @endif
</div>

@endsection
