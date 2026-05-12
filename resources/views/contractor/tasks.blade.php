@extends('layouts.app')

@section('title', 'My Tasks - EstateFlow')
@section('page-title', 'My Tasks')
@section('page-subtitle', 'Tasks from your assigned projects')

@section('content')

{{-- Stats --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    @foreach(['pending' => ['bg-gray-100','text-gray-600','fa-clock',$pendingTasks,'Pending'], 'in_progress' => ['bg-blue-100','text-blue-600','fa-spinner',$inProgressTasks,'In Progress']] as $status => $config)
    <div class="bg-white rounded-xl shadow-sm p-5 flex items-center gap-4">
        <div class="w-11 h-11 {{ $config[0] }} rounded-xl flex items-center justify-center">
            <i class="fas {{ $config[2] }} {{ $config[1] }}"></i>
        </div>
        <div>
            <p class="text-sm text-gray-500">{{ $config[4] }}</p>
            <p class="text-2xl font-bold text-gray-800">{{ $config[3] }}</p>
        </div>
    </div>
    @endforeach
</div>

{{-- Tasks --}}
<div class="space-y-3">
    @forelse($tasks as $task)
    @php
        $isOverdue = $task->due_date && $task->due_date->isPast() && $task->status !== 'completed';
    @endphp
    <div class="bg-white rounded-xl shadow-sm p-5 {{ $isOverdue ? 'border-l-4 border-red-400' : '' }}">
        <div class="flex items-start justify-between gap-4">

            {{-- Task Info --}}
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 flex-wrap mb-1">
                    <p class="font-semibold text-gray-800">{{ $task->title }}</p>
                    {{-- Priority Badge --}}
                    <span class="text-xs px-2 py-0.5 rounded-full font-medium
                        {{ $task->priority === 'low'    ? 'bg-gray-100 text-gray-500'     : '' }}
                        {{ $task->priority === 'medium' ? 'bg-blue-100 text-blue-600'     : '' }}
                        {{ $task->priority === 'high'   ? 'bg-orange-100 text-orange-600' : '' }}
                        {{ $task->priority === 'urgent' ? 'bg-red-100 text-red-600'       : '' }}">
                        {{ ucfirst($task->priority) }}
                    </span>
                    @if($isOverdue)
                        <span class="text-xs bg-red-100 text-red-600 px-2 py-0.5 rounded-full font-medium">
                            <i class="fas fa-exclamation-circle mr-1"></i>Overdue
                        </span>
                    @endif
                </div>
                @if($task->description)
                    <p class="text-xs text-gray-400 mb-2">{{ Str::limit($task->description, 80) }}</p>
                @endif
                <div class="flex items-center gap-4 text-xs text-gray-400 flex-wrap">
                    <span><i class="fas fa-project-diagram mr-1"></i>{{ $task->project->name ?? '—' }}</span>
                    @if($task->due_date)
                        <span class="{{ $isOverdue ? 'text-red-500 font-medium' : '' }}">
                            <i class="fas fa-calendar mr-1"></i>Due {{ $task->due_date->format('M d, Y') }}
                        </span>
                    @endif
                    @if($task->assignedTo)
                        <span><i class="fas fa-user mr-1"></i>{{ $task->assignedTo->name }}</span>
                    @endif
                </div>
            </div>

            {{-- Status + Actions --}}
            <div class="flex flex-col items-end gap-2 flex-shrink-0">
                {{-- Current Status --}}
                <span class="text-xs px-2.5 py-1 rounded-full font-medium
                    {{ $task->status === 'pending'     ? 'bg-gray-100 text-gray-600'   : '' }}
                    {{ $task->status === 'in_progress' ? 'bg-blue-100 text-blue-700'   : '' }}
                    {{ $task->status === 'completed'   ? 'bg-green-100 text-green-700' : '' }}
                    {{ $task->status === 'cancelled'   ? 'bg-red-100 text-red-700'     : '' }}">
                    {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                </span>

                {{-- Inline Status Update --}}
                <div class="flex items-center gap-1.5">
                    @if($task->status === 'pending')
                        <form method="POST" action="{{ route('tasks.update-status', $task) }}">
                            @csrf @method('PATCH')
                            <input type="hidden" name="status" value="in_progress">
                            <button type="submit" class="text-xs bg-blue-50 text-blue-600 px-2.5 py-1.5 rounded-lg hover:bg-blue-100 transition font-medium">
                                <i class="fas fa-play mr-1"></i>Start
                            </button>
                        </form>
                    @endif
                    @if($task->status === 'in_progress')
                        <form method="POST" action="{{ route('tasks.update-status', $task) }}">
                            @csrf @method('PATCH')
                            <input type="hidden" name="status" value="completed">
                            <button type="submit" class="text-xs bg-green-50 text-green-600 px-2.5 py-1.5 rounded-lg hover:bg-green-100 transition font-medium">
                                <i class="fas fa-check mr-1"></i>Done
                            </button>
                        </form>
                    @endif
                    @if(in_array($task->status, ['pending','in_progress']))
                        <form method="POST" action="{{ route('tasks.update-status', $task) }}">
                            @csrf @method('PATCH')
                            <input type="hidden" name="status" value="cancelled">
                            <button type="submit" class="text-xs bg-gray-50 text-gray-500 px-2.5 py-1.5 rounded-lg hover:bg-red-50 hover:text-red-500 transition font-medium">
                                <i class="fas fa-times mr-1"></i>Cancel
                            </button>
                        </form>
                    @endif
                    <a href="{{ route('tasks.show', $task) }}"
                        class="text-xs bg-indigo-50 text-indigo-600 px-2.5 py-1.5 rounded-lg hover:bg-indigo-100 transition font-medium">
                        <i class="fas fa-eye"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="bg-white rounded-xl shadow-sm p-16 text-center text-gray-400">
        <i class="fas fa-tasks text-4xl mb-3 block text-gray-200"></i>
        No tasks found for your projects.
    </div>
    @endforelse
</div>

@if(method_exists($tasks, 'hasPages') && $tasks->hasPages())
    <div class="mt-6">{{ $tasks->links() }}</div>
@endif

@endsection
