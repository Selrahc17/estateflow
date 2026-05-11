@extends('layouts.app')

@section('title', 'Tasks - EstateFlow')
@section('page-title', 'Tasks')
@section('page-subtitle', 'Manage all project tasks')

@section('content')

{{-- Stats --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-6 mb-6">
    <div class="bg-white rounded-xl shadow-sm p-6 flex items-center gap-4">
        <div class="w-12 h-12 bg-indigo-100 rounded-xl flex items-center justify-center">
            <i class="fas fa-tasks text-indigo-600 text-xl"></i>
        </div>
        <div>
            <p class="text-sm text-gray-500">Total</p>
            <p class="text-2xl font-bold text-gray-800">{{ $totalTasks }}</p>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-6 flex items-center gap-4">
        <div class="w-12 h-12 bg-gray-100 rounded-xl flex items-center justify-center">
            <i class="fas fa-clock text-gray-600 text-xl"></i>
        </div>
        <div>
            <p class="text-sm text-gray-500">Pending</p>
            <p class="text-2xl font-bold text-gray-800">{{ $pendingCount }}</p>
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
    <form method="GET" action="{{ route('tasks.index') }}" class="flex flex-wrap gap-3 items-end">
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Search</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Task title..."
                class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 w-48">
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
                <option value="">All Status</option>
                @foreach(['pending','in_progress','completed','cancelled'] as $s)
                    <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>
                        {{ ucfirst(str_replace('_', ' ', $s)) }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Priority</label>
            <select name="priority" class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">All Priorities</option>
                @foreach(['low','medium','high','urgent'] as $p)
                    <option value="{{ $p }}" {{ request('priority') === $p ? 'selected' : '' }}>
                        {{ ucfirst($p) }}
                    </option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-indigo-700 transition">
            <i class="fas fa-search mr-1"></i> Filter
        </button>
        <a href="{{ route('tasks.index') }}" class="bg-gray-100 text-gray-600 px-4 py-2 rounded-lg text-sm hover:bg-gray-200 transition">Reset</a>
    </form>
</div>

{{-- Header Row --}}
<div class="flex items-center justify-between mb-4">
    <p class="text-sm text-gray-500">{{ $tasks->total() }} tasks found</p>
    @if(auth()->user()->isAdmin() || auth()->user()->isContractor())
        <a href="{{ route('tasks.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-indigo-700 transition">
            <i class="fas fa-plus mr-1"></i> Add Task
        </a>
    @endif
</div>

{{-- Table --}}
<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-100">
            <tr>
                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Task</th>
                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Project</th>
                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Assigned To</th>
                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Due Date</th>
                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Priority</th>
                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                <th class="text-right px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @forelse($tasks as $task)
            <tr class="hover:bg-gray-50 transition">
                <td class="px-6 py-4">
                    <p class="font-medium text-gray-800">{{ $task->title }}</p>
                    @if($task->estimated_hours)
                        <p class="text-xs text-gray-400">Est: {{ $task->estimated_hours }}h
                            @if($task->actual_hours) · Actual: {{ $task->actual_hours }}h @endif
                        </p>
                    @endif
                </td>
                <td class="px-6 py-4">
                    <a href="{{ route('projects.show', $task->project) }}" class="text-indigo-600 hover:underline text-xs">
                        {{ $task->project->name ?? '—' }}
                    </a>
                </td>
                <td class="px-6 py-4 text-gray-700">{{ $task->assignedTo->name ?? '—' }}</td>
                <td class="px-6 py-4 text-gray-600">
                    {{ $task->due_date ? $task->due_date->format('M d, Y') : '—' }}
                </td>
                <td class="px-6 py-4">
                    <span class="text-xs px-2.5 py-1 rounded-full font-medium
                        {{ $task->priority === 'low'    ? 'bg-gray-100 text-gray-600'     : '' }}
                        {{ $task->priority === 'medium' ? 'bg-blue-100 text-blue-700'     : '' }}
                        {{ $task->priority === 'high'   ? 'bg-orange-100 text-orange-700' : '' }}
                        {{ $task->priority === 'urgent' ? 'bg-red-100 text-red-700'       : '' }}">
                        {{ ucfirst($task->priority) }}
                    </span>
                </td>
                <td class="px-6 py-4">
                    <span class="text-xs px-2.5 py-1 rounded-full font-medium
                        {{ $task->status === 'pending'     ? 'bg-gray-100 text-gray-600'   : '' }}
                        {{ $task->status === 'in_progress' ? 'bg-blue-100 text-blue-700'   : '' }}
                        {{ $task->status === 'completed'   ? 'bg-green-100 text-green-700' : '' }}
                        {{ $task->status === 'cancelled'   ? 'bg-red-100 text-red-700'     : '' }}">
                        {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                    </span>
                </td>
                <td class="px-6 py-4">
                    <div class="flex items-center justify-end gap-2">
                        <a href="{{ route('tasks.show', $task) }}" class="text-xs px-3 py-1.5 bg-indigo-50 text-indigo-600 rounded-lg hover:bg-indigo-100 transition">View</a>
                        @if(auth()->user()->isAdmin() || auth()->user()->isContractor())
                            <a href="{{ route('tasks.edit', $task) }}" class="text-xs px-3 py-1.5 bg-gray-50 text-gray-600 rounded-lg hover:bg-gray-100 transition">Edit</a>
                            <form method="POST" action="{{ route('tasks.destroy', $task) }}"
                                onsubmit="return confirm('Delete this task?')">
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
                    <i class="fas fa-tasks text-4xl mb-3 block text-gray-200"></i>
                    No tasks found.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    @if($tasks->hasPages())
        <div class="px-6 py-4 border-t border-gray-100">{{ $tasks->links() }}</div>
    @endif
</div>

@endsection
