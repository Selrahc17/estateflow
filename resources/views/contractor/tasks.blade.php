@extends('layouts.app')

@section('title', 'My Tasks - EstateFlow')
@section('page-title', 'My Tasks')
@section('page-subtitle', 'Tasks from your assigned projects')

@section('content')

{{-- Stats --}}
<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
    <div class="bg-white rounded-xl shadow-sm p-6 flex items-center gap-4">
        <div class="w-12 h-12 bg-yellow-100 rounded-xl flex items-center justify-center">
            <i class="fas fa-clock text-yellow-600 text-xl"></i>
        </div>
        <div>
            <p class="text-sm text-gray-500">Pending</p>
            <p class="text-2xl font-bold text-gray-800">{{ $pendingTasks }}</p>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-6 flex items-center gap-4">
        <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
            <i class="fas fa-spinner text-blue-600 text-xl"></i>
        </div>
        <div>
            <p class="text-sm text-gray-500">In Progress</p>
            <p class="text-2xl font-bold text-gray-800">{{ $inProgressTasks }}</p>
        </div>
    </div>
</div>

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
                    @if($task->description)
                        <p class="text-xs text-gray-400">{{ Str::limit($task->description, 50) }}</p>
                    @endif
                </td>
                <td class="px-6 py-4">
                    <a href="{{ route('projects.show', $task->project) }}" class="text-xs text-indigo-600 hover:underline">
                        {{ $task->project->name ?? '—' }}
                    </a>
                </td>
                <td class="px-6 py-4 text-gray-700">{{ $task->assignedTo->name ?? 'Unassigned' }}</td>
                <td class="px-6 py-4">
                    @if($task->due_date)
                        <span class="{{ $task->due_date->isPast() && $task->status !== 'completed' ? 'text-red-600 font-medium' : 'text-gray-600' }}">
                            {{ $task->due_date->format('M d, Y') }}
                        </span>
                        @if($task->due_date->isPast() && $task->status !== 'completed')
                            <p class="text-xs text-red-500">Overdue</p>
                        @endif
                    @else
                        <span class="text-gray-400">—</span>
                    @endif
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
                        <a href="{{ route('tasks.edit', $task) }}" class="text-xs px-3 py-1.5 bg-gray-50 text-gray-600 rounded-lg hover:bg-gray-100 transition">Edit</a>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="px-6 py-16 text-center text-gray-400">
                    <i class="fas fa-tasks text-4xl mb-3 block text-gray-200"></i>
                    No tasks found for your projects.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    @if(method_exists($tasks, 'hasPages') && $tasks->hasPages())
        <div class="px-6 py-4 border-t border-gray-100">{{ $tasks->links() }}</div>
    @endif
</div>

@endsection
