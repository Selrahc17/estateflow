@extends('layouts.app')

@section('title', 'Edit Progress Log - EstateFlow')
@section('page-title', 'Edit Progress Log')
@section('page-subtitle', $progressLog->log_date->format('M d, Y'))

@section('content')
<div class="max-w-2xl">
    <div class="bg-white rounded-xl shadow-sm p-6">
        <form method="POST" action="{{ route('progress-logs.update', $progressLog) }}" enctype="multipart/form-data">
            @csrf @method('PUT')
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Project <span class="text-red-500">*</span></label>
                    <select name="project_id" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        @foreach($projects as $project)
                            <option value="{{ $project->id }}" {{ old('project_id', $progressLog->project_id) == $project->id ? 'selected' : '' }}>
                                {{ $project->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Log Date <span class="text-red-500">*</span></label>
                    <input type="date" name="log_date" value="{{ old('log_date', $progressLog->log_date->format('Y-m-d')) }}"
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Completion % <span class="text-red-500">*</span></label>
                    <input type="number" name="completion_percentage" value="{{ old('completion_percentage', $progressLog->completion_percentage) }}" min="0" max="100"
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description <span class="text-red-500">*</span></label>
                    <textarea name="description" rows="4"
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('description', $progressLog->description) }}</textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Workers Count</label>
                    <input type="number" name="workers_count" value="{{ old('workers_count', $progressLog->workers_count) }}" min="0"
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Hours Worked</label>
                    <input type="number" name="hours_worked" value="{{ old('hours_worked', $progressLog->hours_worked) }}" min="0"
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Weather Conditions</label>
                    <input type="text" name="weather_conditions" value="{{ old('weather_conditions', $progressLog->weather_conditions) }}"
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Issues / Problems</label>
                    <textarea name="issues" rows="2"
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('issues', $progressLog->issues) }}</textarea>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Cover Photo</label>
                    @if($progressLog->image_path)
                        <img src="{{ asset('storage/' . $progressLog->image_path) }}" class="w-32 h-20 object-cover rounded-lg mb-2">
                    @endif
                    <input type="file" name="image_path" accept="image/*"
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <p class="text-xs text-gray-400 mt-1">Leave blank to keep existing. Max 5MB.</p>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Additional Photos</label>
                    @if($progressLog->images)
                        <div class="flex gap-2 flex-wrap mb-2">
                            @foreach($progressLog->images as $img)
                                <img src="{{ asset('storage/' . $img) }}" class="w-20 h-16 object-cover rounded-lg">
                            @endforeach
                        </div>
                    @endif
                    <input type="file" name="images[]" accept="image/*" multiple
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <p class="text-xs text-gray-400 mt-1">Uploading new images replaces existing ones. Max 5MB each.</p>
                </div>
            </div>

            <div class="mt-6 flex gap-3">
                <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded-lg text-sm hover:bg-indigo-700 transition font-medium">Update Log</button>
                <a href="{{ route('progress-logs.show', $progressLog) }}" class="bg-gray-100 text-gray-600 px-6 py-2 rounded-lg text-sm hover:bg-gray-200 transition">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
