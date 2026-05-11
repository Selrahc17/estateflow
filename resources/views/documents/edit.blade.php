@extends('layouts.app')

@section('title', 'Edit Document - EstateFlow')
@section('page-title', 'Edit Document')
@section('page-subtitle', '{{ $document->title }}')

@section('content')
<div class="max-w-2xl">
    <div class="bg-white rounded-xl shadow-sm p-6">
        <form method="POST" action="{{ route('documents.update', $document) }}">
            @csrf @method('PUT')
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Title <span class="text-red-500">*</span></label>
                    <input type="text" name="title" value="{{ old('title', $document->title) }}"
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Document Type <span class="text-red-500">*</span></label>
                    <select name="document_type" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        @foreach(['contract','permit','title','id','payment_receipt','deed_of_sale','floor_plan','other'] as $type)
                            <option value="{{ $type }}" {{ old('document_type', $document->document_type) === $type ? 'selected' : '' }}>
                                {{ ucfirst(str_replace('_', ' ', $type)) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea name="description" rows="3"
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('description', $document->description) }}</textarea>
                </div>

                <div class="md:col-span-2 bg-gray-50 rounded-lg p-4">
                    <p class="text-xs text-gray-500 mb-1">Current File</p>
                    <p class="text-sm font-medium text-gray-800">{{ $document->file_name }}</p>
                    <p class="text-xs text-gray-400">{{ $document->file_size_formatted }} · {{ $document->file_type }}</p>
                    <p class="text-xs text-gray-400 mt-1">To replace the file, delete this document and upload a new one.</p>
                </div>
            </div>

            <div class="mt-6 flex gap-3">
                <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded-lg text-sm hover:bg-indigo-700 transition font-medium">Update Document</button>
                <a href="{{ route('documents.show', $document) }}" class="bg-gray-100 text-gray-600 px-6 py-2 rounded-lg text-sm hover:bg-gray-200 transition">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
