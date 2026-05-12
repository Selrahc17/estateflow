@extends('layouts.app')

@section('title', 'Upload Progress - EstateFlow')
@section('page-title', 'Upload Progress')
@section('page-subtitle', 'Record daily construction progress with photos')

@section('content')
<div class="max-w-2xl">
    <div class="bg-white rounded-xl shadow-sm p-6">
        <form method="POST" action="{{ route('progress-logs.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Project <span class="text-red-500">*</span></label>
                    <select name="project_id" required class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">Select Project</option>
                        @foreach($projects as $project)
                            <option value="{{ $project->id }}" {{ old('project_id', $selectedProject) == $project->id ? 'selected' : '' }}>
                                {{ $project->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Log Date <span class="text-red-500">*</span></label>
                    <input type="date" name="log_date" value="{{ old('log_date', now()->format('Y-m-d')) }}" required
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Completion % <span class="text-red-500">*</span></label>
                    <input type="number" name="completion_percentage" value="{{ old('completion_percentage', 0) }}" min="0" max="100" required
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <p class="text-xs text-gray-400 mt-1">Overall project completion percentage</p>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description <span class="text-red-500">*</span></label>
                    <textarea name="description" rows="4" required placeholder="What was accomplished today? What phase is currently being worked on?"
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('description') }}</textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Workers Present</label>
                    <input type="number" name="workers_count" value="{{ old('workers_count') }}" min="0"
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Hours Worked</label>
                    <input type="number" name="hours_worked" value="{{ old('hours_worked') }}" min="0"
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Weather Conditions</label>
                    <input type="text" name="weather_conditions" value="{{ old('weather_conditions') }}" placeholder="e.g. Sunny, no delays"
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Issues / Problems Encountered</label>
                    <textarea name="issues" rows="2" placeholder="Any issues, delays, or problems?"
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('issues') }}</textarea>
                </div>

                {{-- Photo Upload - Prominent --}}
                <div class="md:col-span-2 bg-indigo-50 border-2 border-dashed border-indigo-200 rounded-xl p-5">
                    <div class="text-center mb-3">
                        <i class="fas fa-camera text-indigo-400 text-2xl mb-1 block"></i>
                        <p class="text-sm font-semibold text-indigo-700">Progress Photos</p>
                        <p class="text-xs text-indigo-400">Upload photos as proof of today's work</p>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Cover Photo (Main)</label>
                            <input type="file" name="image_path" accept="image/*"
                                class="w-full px-3 py-2 border border-gray-200 bg-white rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:text-xs file:bg-indigo-50 file:text-indigo-600">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Additional Photos</label>
                            <input type="file" name="images[]" accept="image/*" multiple
                                class="w-full px-3 py-2 border border-gray-200 bg-white rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:text-xs file:bg-indigo-50 file:text-indigo-600">
                        </div>
                    </div>
                    <p class="text-xs text-gray-400 mt-2 text-center">Max 5MB per photo. JPG, PNG accepted.</p>
                </div>
            </div>

            <div class="mt-6 flex gap-3">
                <button type="submit" class="bg-green-600 text-white px-6 py-2.5 rounded-lg text-sm hover:bg-green-700 transition font-medium">
                    <i class="fas fa-upload mr-1"></i> Submit Progress Update
                </button>
                <a href="{{ route('contractor.projects') }}" class="bg-gray-100 text-gray-600 px-6 py-2.5 rounded-lg text-sm hover:bg-gray-200 transition">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
