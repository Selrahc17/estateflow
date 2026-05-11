@extends('layouts.app')

@section('title', '{{ $task->title }} - EstateFlow')
@section('page-title', '{{ $task->title }}')
@section('page-subtitle', 'Task Details')

@section('content')
<div class="max-w-2xl">
    <div class="bg-white rounded-xl shadow-sm p-6">

        {{-- Header --}}
        <div class="flex items-start justify-between mb-6 pb-6 border-b border-gray-100">
            <div>
                <h2 class="text-lg font-bold text-gray-800">{{ $task->title }}</h2>
                <a href="{{ route('projects.show', $task->project) }}" class="text-sm text-indigo-600 hover:underline mt-1 block">
                    <i class="fas fa-project-diagram mr-1"></i>{{ $task->project->name ?? '—' }}
                </a>
            </div>
            <div class="flex gap-2">
                <span class="text-xs px-2.5 py-1 rounded-full font-medium
                    {{ $task->priority === 'low'    ? 'bg-gray-100 text-gray-600'     : '' }}
                    {{ $task->priority === 'medium' ? 'bg-blue-100 text-blue-700'     : '' }}
                    {{ $task->priority === 'high'   ? 'bg-orange-100 text-orange-700' : '' }}
                    {{ $task->priority === 'urgent' ? 'bg-red-100 text-red-700'       : '' }}">
                    {{ ucfirst($task->priority) }}
                </span>
                <span class="text-xs px-2.5 py-1 rounded-full font-medium
                    {{ $task->status === 'pending'     ? 'bg-gray-100 text-gray-600'   : '' }}
                    {{ $task->status === 'in_progress' ? 'bg-blue-100 text-blue-700'   : '' }}
                    {{ $task->status === 'completed'   ? 'bg-green-100 text-green-700' : '' }}
                    {{ $task->status === 'cancelled'   ? 'bg-red-100 text-red-700'     : '' }}">
                    {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                </span>
            </div>
        </div>

        @if($task->description)
        <p class="text-sm text-gray-600 mb-6">{{ $task->description }}</p>
        @endif

        {{-- Details --}}
        <div class="space-y-3 text-sm">
            <div class="flex justify-between py-2 border-b border-gray-50">
                <span class="text-gray-500">Assigned To</span>
                <span class="font-medium text-gray-800">{{ $task->assignedTo->name ?? 'Unassigned' }}</span>
            </div>
            <div class="flex justify-between py-2 border-b border-gray-50">
                <span class="text-gray-500">Assigned By</span>
                <span class="font-medium text-gray-800">{{ $task->assignedBy->name ?? '—' }}</span>
            </div>
            @if($task->start_date)
            <div class="flex justify-between py-2 border-b border-gray-50">
                <span class="text-gray-500">Start Date</span>
                <span class="font-medium text-gray-800">{{ $task->start_date->format('M d, Y') }}</span>
            </div>
            @endif
            @if($task->due_date)
            <div class="flex justify-between py-2 border-b border-gray-50">
                <span class="text-gray-500">Due Date</span>
                <span class="font-medium {{ $task->due_date->isPast() && $task->status !== 'completed' ? 'text-red-600' : 'text-gray-800' }}">
                    {{ $task->due_date->format('M d, Y') }}
                    @if($task->due_date->isPast() && $task->status !== 'completed')
                        <span class="text-xs">(Overdue)</span>
                    @endif
                </span>
            </div>
            @endif
            @if($task->completed_date)
            <div class="flex justify-between py-2 border-b border-gray-50">
                <span class="text-gray-500">Completed Date</span>
                <span class="font-medium text-green-700">{{ $task->completed_date->format('M d, Y') }}</span>
            </div>
            @endif
            @if($task->estimated_hours)
            <div class="flex justify-between py-2 border-b border-gray-50">
                <span class="text-gray-500">Estimated Hours</span>
                <span class="font-medium text-gray-800">{{ $task->estimated_hours }}h</span>
            </div>
            @endif
            @if($task->actual_hours)
            <div class="flex justify-between py-2 border-b border-gray-50">
                <span class="text-gray-500">Actual Hours</span>
                <span class="font-medium text-gray-800">{{ $task->actual_hours }}h</span>
            </div>
            @endif
            @if($task->notes)
            <div class="py-2">
                <p class="text-gray-500 mb-1">Notes</p>
                <p class="text-gray-800">{{ $task->notes }}</p>
            </div>
            @endif
        </div>

        @if(auth()->user()->isAdmin() || auth()->user()->isContractor())
        <div class="mt-6 flex gap-3">
            <a href="{{ route('tasks.edit', $task) }}" class="bg-indigo-600 text-white px-6 py-2 rounded-lg text-sm hover:bg-indigo-700 transition font-medium">
                <i class="fas fa-edit mr-1"></i> Edit
            </a>
            <form method="POST" action="{{ route('tasks.destroy', $task) }}"
                onsubmit="return confirm('Delete this task?')">
                @csrf @method('DELETE')
                <button type="submit" class="bg-red-50 text-red-600 px-6 py-2 rounded-lg text-sm hover:bg-red-100 transition">
                    <i class="fas fa-trash mr-1"></i> Delete
                </button>
            </form>
            <a href="{{ route('tasks.index') }}" class="bg-gray-100 text-gray-600 px-6 py-2 rounded-lg text-sm hover:bg-gray-200 transition">Back</a>
        </div>
        @endif
    </div>
</div>
@endsection
