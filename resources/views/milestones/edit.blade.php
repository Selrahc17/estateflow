@extends('layouts.app')

@section('title', 'Edit Milestone - EstateFlow')
@section('page-title', 'Edit Milestone')
@section('page-subtitle', $milestone->name)

@section('content')
<div class="max-w-2xl">
    <div class="bg-white rounded-xl shadow-sm p-6">
        <form method="POST" action="{{ route('milestones.update', $milestone) }}">
            @csrf @method('PUT')
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Project <span class="text-red-500">*</span></label>
                    <select name="project_id" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        @foreach($projects as $project)
                            <option value="{{ $project->id }}" {{ old('project_id', $milestone->project_id) == $project->id ? 'selected' : '' }}>
                                {{ $project->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Milestone Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $milestone->name) }}"
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea name="description" rows="3"
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('description', $milestone->description) }}</textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Target Date <span class="text-red-500">*</span></label>
                    <input type="date" name="target_date" value="{{ old('target_date', $milestone->target_date->format('Y-m-d')) }}"
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Actual Date</label>
                    <input type="date" name="actual_date" value="{{ old('actual_date', $milestone->actual_date?->format('Y-m-d')) }}"
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Completion %</label>
                    <input type="number" name="completion_percentage" value="{{ old('completion_percentage', $milestone->completion_percentage) }}" min="0" max="100"
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <div class="flex items-center gap-3 pt-6">
                    <input type="checkbox" name="is_completed" id="is_completed" value="1"
                        {{ old('is_completed', $milestone->is_completed) ? 'checked' : '' }}
                        class="w-4 h-4 text-indigo-600 rounded">
                    <label for="is_completed" class="text-sm font-medium text-gray-700">Mark as Completed</label>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                    <textarea name="notes" rows="2"
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('notes', $milestone->notes) }}</textarea>
                </div>
            </div>

            <div class="mt-6 flex gap-3">
                <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded-lg text-sm hover:bg-indigo-700 transition font-medium">Update Milestone</button>
                <a href="{{ route('milestones.show', $milestone) }}" class="bg-gray-100 text-gray-600 px-6 py-2 rounded-lg text-sm hover:bg-gray-200 transition">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
