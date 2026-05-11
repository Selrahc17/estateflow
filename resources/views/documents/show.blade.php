@extends('layouts.app')

@section('title', '{{ $document->title }} - EstateFlow')
@section('page-title', '{{ $document->title }}')
@section('page-subtitle', 'Document Details')

@section('content')
<div class="max-w-2xl">
    <div class="bg-white rounded-xl shadow-sm p-6">

        {{-- Header --}}
        <div class="flex items-start justify-between mb-6 pb-6 border-b border-gray-100">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 bg-indigo-50 rounded-xl flex items-center justify-center">
                    @if(str_contains($document->file_type, 'pdf'))
                        <i class="fas fa-file-pdf text-red-500 text-2xl"></i>
                    @elseif(str_contains($document->file_type, 'image'))
                        <i class="fas fa-file-image text-blue-500 text-2xl"></i>
                    @else
                        <i class="fas fa-file-alt text-gray-400 text-2xl"></i>
                    @endif
                </div>
                <div>
                    <h2 class="font-bold text-gray-800 text-lg">{{ $document->title }}</h2>
                    <p class="text-sm text-gray-400">{{ $document->file_name }}</p>
                </div>
            </div>
            <span class="text-xs px-2.5 py-1 rounded-full font-medium {{ $document->is_verified ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                {{ $document->is_verified ? 'Verified' : 'Pending' }}
            </span>
        </div>

        @if($document->description)
        <p class="text-sm text-gray-600 mb-6">{{ $document->description }}</p>
        @endif

        <div class="space-y-3 text-sm">
            <div class="flex justify-between py-2 border-b border-gray-50">
                <span class="text-gray-500">Document Type</span>
                <span class="font-medium text-gray-800">{{ ucfirst(str_replace('_', ' ', $document->document_type)) }}</span>
            </div>
            <div class="flex justify-between py-2 border-b border-gray-50">
                <span class="text-gray-500">File Size</span>
                <span class="font-medium text-gray-800">{{ $document->file_size_formatted }}</span>
            </div>
            <div class="flex justify-between py-2 border-b border-gray-50">
                <span class="text-gray-500">File Type</span>
                <span class="font-medium text-gray-800">{{ $document->file_type }}</span>
            </div>
            <div class="flex justify-between py-2 border-b border-gray-50">
                <span class="text-gray-500">Attached To</span>
                <span class="font-medium text-gray-800">{{ class_basename($document->documentable_type) }} #{{ $document->documentable_id }}</span>
            </div>
            @if($document->is_verified && $document->verifiedBy)
            <div class="flex justify-between py-2 border-b border-gray-50">
                <span class="text-gray-500">Verified By</span>
                <span class="font-medium text-gray-800">{{ $document->verifiedBy->name }}</span>
            </div>
            @endif
            <div class="flex justify-between py-2 border-b border-gray-50">
                <span class="text-gray-500">Uploaded</span>
                <span class="font-medium text-gray-800">{{ $document->created_at->format('M d, Y') }}</span>
            </div>
        </div>

        <div class="mt-6 flex flex-wrap gap-3">
            <a href="{{ route('documents.download', $document) }}" class="bg-green-600 text-white px-6 py-2 rounded-lg text-sm hover:bg-green-700 transition font-medium">
                <i class="fas fa-download mr-1"></i> Download
            </a>
            @if(auth()->user()->isAdmin())
                <a href="{{ route('documents.edit', $document) }}" class="bg-indigo-600 text-white px-6 py-2 rounded-lg text-sm hover:bg-indigo-700 transition font-medium">
                    <i class="fas fa-edit mr-1"></i> Edit
                </a>
                @if(!$document->is_verified)
                    <form method="POST" action="{{ route('documents.verify', $document) }}">
                        @csrf @method('PATCH')
                        <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg text-sm hover:bg-blue-700 transition font-medium">
                            <i class="fas fa-check mr-1"></i> Verify
                        </button>
                    </form>
                @endif
                <form method="POST" action="{{ route('documents.destroy', $document) }}"
                    onsubmit="return confirm('Delete this document?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="bg-red-50 text-red-600 px-6 py-2 rounded-lg text-sm hover:bg-red-100 transition">
                        <i class="fas fa-trash mr-1"></i> Delete
                    </button>
                </form>
            @endif
            <a href="{{ route('documents.index') }}" class="bg-gray-100 text-gray-600 px-6 py-2 rounded-lg text-sm hover:bg-gray-200 transition">Back</a>
        </div>
    </div>
</div>
@endsection
