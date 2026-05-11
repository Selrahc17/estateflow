@extends('layouts.app')

@section('title', 'Projects - EstateFlow')
@section('page-title', 'Projects')
@section('page-subtitle', 'Manage all construction projects')

@section('content')

{{-- Stats --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-6 mb-6">
    <div class="bg-white rounded-xl shadow-sm p-6 flex items-center gap-4">
        <div class="w-12 h-12 bg-indigo-100 rounded-xl flex items-center justify-center">
            <i class="fas fa-project-diagram text-indigo-600 text-xl"></i>
        </div>
        <div>
            <p class="text-sm text-gray-500">Total</p>
            <p class="text-2xl font-bold text-gray-800">{{ $totalProjects }}</p>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-6 flex items-center gap-4">
        <div class="w-12 h-12 bg-yellow-100 rounded-xl flex items-center justify-center">
            <i class="fas fa-drafting-compass text-yellow-600 text-xl"></i>
        </div>
        <div>
            <p class="text-sm text-gray-500">Planning</p>
            <p class="text-2xl font-bold text-gray-800">{{ $planningCount }}</p>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-6 flex items-center gap-4">
        <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
            <i class="fas fa-spinner text-blue-600 text-xl"></i>
        </div>
        <div>
            <p class="text-sm text-gray-500">In Progress</p>
            <p class="text-2xl font-bold text-gray-800">{{ $inProgressCount }}</p>
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
    <form method="GET" action="{{ route('projects.index') }}" class="flex flex-wrap gap-3 items-end">
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Search</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Project name..."
                class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 w-64">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Status</label>
            <select name="status" class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">All Status</option>
                @foreach(['planning','in_progress','on_hold','completed','cancelled'] as $s)
                    <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>
                        {{ ucfirst(str_replace('_', ' ', $s)) }}
                    </option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-indigo-700 transition">
            <i class="fas fa-search mr-1"></i> Filter
        </button>
        <a href="{{ route('projects.index') }}" class="bg-gray-100 text-gray-600 px-4 py-2 rounded-lg text-sm hover:bg-gray-200 transition">Reset</a>
    </form>
</div>

{{-- Header Row --}}
<div class="flex items-center justify-between mb-4">
    <p class="text-sm text-gray-500">{{ $projects->total() }} projects found</p>
    @if(auth()->user()->isAdmin())
        <a href="{{ route('projects.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-indigo-700 transition">
            <i class="fas fa-plus mr-1"></i> New Project
        </a>
    @endif
</div>

{{-- Table --}}
<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-100">
            <tr>
                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Project</th>
                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Client</th>
                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Staff</th>
                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Progress</th>
                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Budget</th>
                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                <th class="text-right px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @forelse($projects as $project)
            <tr class="hover:bg-gray-50 transition">
                <td class="px-6 py-4">
                    <p class="font-medium text-gray-800">{{ $project->name }}</p>
                    <p class="text-xs text-gray-400">
                        {{ $project->start_date ? $project->start_date->format('M d, Y') : 'No start date' }}
                        @if($project->estimated_completion_date)
                            → {{ $project->estimated_completion_date->format('M d, Y') }}
                        @endif
                    </p>
                </td>
                <td class="px-6 py-4 text-gray-700">{{ $project->client->full_name ?? '—' }}</td>
                <td class="px-6 py-4 text-gray-700">{{ $project->contractor->company_name ?? '—' }}</td>
                <td class="px-6 py-4">
                    <div class="flex items-center gap-2">
                        <div class="w-24 bg-gray-100 rounded-full h-2">
                            <div class="h-2 rounded-full {{ $project->completion_percentage >= 100 ? 'bg-green-500' : 'bg-indigo-500' }}"
                                style="width: {{ $project->completion_percentage }}%"></div>
                        </div>
                        <span class="text-xs text-gray-500">{{ $project->completion_percentage }}%</span>
                    </div>
                </td>
                <td class="px-6 py-4">
                    <p class="text-gray-800 font-medium">₱{{ number_format($project->budget, 0) }}</p>
                    <p class="text-xs text-gray-400">Spent: ₱{{ number_format($project->actual_cost, 0) }}</p>
                </td>
                <td class="px-6 py-4">
                    <span class="text-xs px-2.5 py-1 rounded-full font-medium
                        {{ $project->status === 'planning'    ? 'bg-gray-100 text-gray-600'     : '' }}
                        {{ $project->status === 'in_progress' ? 'bg-blue-100 text-blue-700'     : '' }}
                        {{ $project->status === 'on_hold'     ? 'bg-yellow-100 text-yellow-700' : '' }}
                        {{ $project->status === 'completed'   ? 'bg-green-100 text-green-700'   : '' }}
                        {{ $project->status === 'cancelled'   ? 'bg-red-100 text-red-700'       : '' }}">
                        {{ ucfirst(str_replace('_', ' ', $project->status)) }}
                    </span>
                </td>
                <td class="px-6 py-4">
                    <div class="flex items-center justify-end gap-2">
                        <a href="{{ route('projects.show', $project) }}" class="text-xs px-3 py-1.5 bg-indigo-50 text-indigo-600 rounded-lg hover:bg-indigo-100 transition">View</a>
                        @if(auth()->user()->isAdmin())
                            <a href="{{ route('projects.edit', $project) }}" class="text-xs px-3 py-1.5 bg-gray-50 text-gray-600 rounded-lg hover:bg-gray-100 transition">Edit</a>
                            <form method="POST" action="{{ route('projects.destroy', $project) }}"
                                onsubmit="return confirm('Delete {{ $project->name }}?')">
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
                <td colspan="7" class="px-6 py-16 text-center text-gray-400">
                    <i class="fas fa-project-diagram text-4xl mb-3 block text-gray-200"></i>
                    No projects found.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    @if($projects->hasPages())
        <div class="px-6 py-4 border-t border-gray-100">{{ $projects->links() }}</div>
    @endif
</div>

@endsection
