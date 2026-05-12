@extends('layouts.app')

@section('title', 'Progress Log - EstateFlow')
@section('page-title', 'Progress Log')
@section('page-subtitle', $progressLog->log_date->format('F d, Y'))

@section('content')
<div class="max-w-2xl">
    <div class="bg-white rounded-xl shadow-sm p-6">

        {{-- Header --}}
        <div class="flex items-start justify-between mb-6 pb-6 border-b border-gray-100">
            <div>
                <h2 class="text-lg font-bold text-gray-800">{{ $progressLog->log_date->format('F d, Y') }}</h2>
                <a href="{{ route('projects.show', $progressLog->project) }}" class="text-sm text-indigo-600 hover:underline mt-1 block">
                    <i class="fas fa-project-diagram mr-1"></i>{{ $progressLog->project->name ?? '—' }}
                </a>
                <p class="text-xs text-gray-400 mt-1">Logged by {{ $progressLog->user->name ?? 'Unknown' }}</p>
            </div>
            <div class="text-center">
                <p class="text-3xl font-bold text-indigo-600">{{ $progressLog->completion_percentage }}%</p>
                <p class="text-xs text-gray-400">Completion</p>
            </div>
        </div>

        {{-- Description --}}
        <div class="mb-6">
            <h3 class="text-sm font-semibold text-gray-700 mb-2">Work Done</h3>
            <p class="text-sm text-gray-700 leading-relaxed">{{ $progressLog->description }}</p>
        </div>

        {{-- Stats --}}
        <div class="grid grid-cols-3 gap-4 mb-6">
            <div class="bg-gray-50 rounded-xl p-4 text-center">
                <i class="fas fa-users text-gray-400 mb-1 block"></i>
                <p class="text-lg font-bold text-gray-800">{{ $progressLog->workers_count ?? '—' }}</p>
                <p class="text-xs text-gray-500">Workers</p>
            </div>
            <div class="bg-gray-50 rounded-xl p-4 text-center">
                <i class="fas fa-clock text-gray-400 mb-1 block"></i>
                <p class="text-lg font-bold text-gray-800">{{ $progressLog->hours_worked ?? '—' }}</p>
                <p class="text-xs text-gray-500">Hours</p>
            </div>
            <div class="bg-gray-50 rounded-xl p-4 text-center">
                <i class="fas fa-cloud-sun text-gray-400 mb-1 block"></i>
                <p class="text-sm font-medium text-gray-800">{{ $progressLog->weather_conditions ?? '—' }}</p>
                <p class="text-xs text-gray-500">Weather</p>
            </div>
        </div>

        {{-- Issues --}}
        @if($progressLog->issues)
        <div class="bg-red-50 border border-red-100 rounded-xl p-4 mb-6">
            <h3 class="text-sm font-semibold text-red-700 mb-2"><i class="fas fa-exclamation-triangle mr-1"></i>Issues Reported</h3>
            <p class="text-sm text-red-600">{{ $progressLog->issues }}</p>
        </div>
        @endif

        {{-- Photos --}}
        @if($progressLog->image_path || ($progressLog->images && count($progressLog->images)))
        <div class="mb-6">
            <h3 class="text-sm font-semibold text-gray-700 mb-3"><i class="fas fa-images mr-1 text-indigo-400"></i>Progress Photos</h3>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                @if($progressLog->image_path)
                <a href="{{ asset('storage/' . $progressLog->image_path) }}" target="_blank">
                    <img src="{{ asset('storage/' . $progressLog->image_path) }}"
                        class="w-full h-32 object-cover rounded-xl border border-gray-100 hover:opacity-90 transition">
                    <p class="text-xs text-center text-gray-400 mt-1">Cover Photo</p>
                </a>
                @endif
                @if($progressLog->images)
                    @foreach($progressLog->images as $img)
                    <a href="{{ asset('storage/' . $img) }}" target="_blank">
                        <img src="{{ asset('storage/' . $img) }}"
                            class="w-full h-32 object-cover rounded-xl border border-gray-100 hover:opacity-90 transition">
                    </a>
                    @endforeach
                @endif
            </div>
        </div>
        @endif

        @if(auth()->user()->isAdmin() || auth()->user()->isContractor())
        <div class="flex gap-3">
            <a href="{{ route('progress-logs.edit', $progressLog) }}" class="bg-indigo-600 text-white px-6 py-2 rounded-lg text-sm hover:bg-indigo-700 transition font-medium">
                <i class="fas fa-edit mr-1"></i> Edit
            </a>
            <form method="POST" action="{{ route('progress-logs.destroy', $progressLog) }}"
                onsubmit="return confirm('Delete this log?')">
                @csrf @method('DELETE')
                <button type="submit" class="bg-red-50 text-red-600 px-6 py-2 rounded-lg text-sm hover:bg-red-100 transition">
                    <i class="fas fa-trash mr-1"></i> Delete
                </button>
            </form>
            <a href="{{ route('progress-logs.index') }}" class="bg-gray-100 text-gray-600 px-6 py-2 rounded-lg text-sm hover:bg-gray-200 transition">Back</a>
        </div>
        @endif
    </div>
</div>
@endsection
