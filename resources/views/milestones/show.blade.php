@extends('layouts.app')

@section('title', '{{ $milestone->name }} - EstateFlow')
@section('page-title', '{{ $milestone->name }}')
@section('page-subtitle', 'Milestone Details')

@section('content')
<div class="max-w-2xl">
    <div class="bg-white rounded-xl shadow-sm p-6">

        {{-- Header --}}
        <div class="flex items-start justify-between mb-6 pb-6 border-b border-gray-100">
            <div>
                <h2 class="text-lg font-bold text-gray-800">{{ $milestone->name }}</h2>
                <a href="{{ route('projects.show', $milestone->project) }}" class="text-sm text-indigo-600 hover:underline mt-1 block">
                    <i class="fas fa-project-diagram mr-1"></i>{{ $milestone->project->name ?? '—' }}
                </a>
            </div>
            <span class="text-xs px-2.5 py-1 rounded-full font-medium {{ $milestone->is_completed ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                {{ $milestone->is_completed ? 'Completed' : 'Pending' }}
            </span>
        </div>

        {{-- Progress --}}
        <div class="mb-6">
            <div class="flex justify-between text-xs text-gray-500 mb-1">
                <span>Progress</span>
                <span>{{ $milestone->completion_percentage }}%</span>
            </div>
            <div class="w-full bg-gray-100 rounded-full h-3">
                <div class="h-3 rounded-full {{ $milestone->is_completed ? 'bg-green-500' : 'bg-indigo-500' }}"
                    style="width: {{ $milestone->completion_percentage }}%"></div>
            </div>
        </div>

        @if($milestone->description)
        <p class="text-sm text-gray-600 mb-6">{{ $milestone->description }}</p>
        @endif

        <div class="space-y-3 text-sm">
            <div class="flex justify-between py-2 border-b border-gray-50">
                <span class="text-gray-500">Target Date</span>
                <span class="font-medium {{ $milestone->target_date->isPast() && !$milestone->is_completed ? 'text-red-600' : 'text-gray-800' }}">
                    {{ $milestone->target_date->format('M d, Y') }}
                    @if($milestone->target_date->isPast() && !$milestone->is_completed)
                        <span class="text-xs">(Overdue)</span>
                    @endif
                </span>
            </div>
            @if($milestone->actual_date)
            <div class="flex justify-between py-2 border-b border-gray-50">
                <span class="text-gray-500">Actual Date</span>
                <span class="font-medium text-green-700">{{ $milestone->actual_date->format('M d, Y') }}</span>
            </div>
            @endif
            @if($milestone->notes)
            <div class="py-2">
                <p class="text-gray-500 mb-1">Notes</p>
                <p class="text-gray-800">{{ $milestone->notes }}</p>
            </div>
            @endif
        </div>

        @if(auth()->user()->isAdmin() || auth()->user()->isContractor())
        <div class="mt-6 flex gap-3">
            <a href="{{ route('milestones.edit', $milestone) }}" class="bg-indigo-600 text-white px-6 py-2 rounded-lg text-sm hover:bg-indigo-700 transition font-medium">
                <i class="fas fa-edit mr-1"></i> Edit
            </a>
            <form method="POST" action="{{ route('milestones.destroy', $milestone) }}"
                onsubmit="return confirm('Delete this milestone?')">
                @csrf @method('DELETE')
                <button type="submit" class="bg-red-50 text-red-600 px-6 py-2 rounded-lg text-sm hover:bg-red-100 transition">
                    <i class="fas fa-trash mr-1"></i> Delete
                </button>
            </form>
            <a href="{{ route('milestones.index') }}" class="bg-gray-100 text-gray-600 px-6 py-2 rounded-lg text-sm hover:bg-gray-200 transition">Back</a>
        </div>
        @endif
    </div>
</div>
@endsection
