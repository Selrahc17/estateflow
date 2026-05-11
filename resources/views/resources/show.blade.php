@extends('layouts.app')

@section('title', '{{ $resource->name }} - EstateFlow')
@section('page-title', '{{ $resource->name }}')
@section('page-subtitle', 'Resource details')

@section('content')
<div class="max-w-2xl">

    <div class="bg-white rounded-xl shadow-sm p-6 mb-4">
        <div class="flex items-start justify-between mb-6">
            <div>
                <h2 class="text-xl font-bold text-gray-800">{{ $resource->name }}</h2>
                <p class="text-sm text-gray-500 mt-1">
                    <a href="{{ route('projects.show', $resource->project) }}" class="text-indigo-600 hover:underline">
                        {{ $resource->project->name ?? '—' }}
                    </a>
                </p>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-xs px-3 py-1.5 rounded-full font-medium
                    {{ $resource->type === 'material'  ? 'bg-blue-100 text-blue-700'     : '' }}
                    {{ $resource->type === 'equipment' ? 'bg-purple-100 text-purple-700' : '' }}
                    {{ $resource->type === 'labor'     ? 'bg-orange-100 text-orange-700' : '' }}">
                    {{ ucfirst($resource->type) }}
                </span>
                <span class="text-xs px-3 py-1.5 rounded-full font-medium
                    {{ $resource->status === 'pending'   ? 'bg-yellow-100 text-yellow-700' : '' }}
                    {{ $resource->status === 'ordered'   ? 'bg-blue-100 text-blue-700'     : '' }}
                    {{ $resource->status === 'delivered' ? 'bg-green-100 text-green-700'   : '' }}
                    {{ $resource->status === 'used'      ? 'bg-gray-100 text-gray-600'     : '' }}
                    {{ $resource->status === 'returned'  ? 'bg-red-100 text-red-700'       : '' }}">
                    {{ ucfirst($resource->status) }}
                </span>
            </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-3 gap-6">
            <div>
                <p class="text-xs text-gray-400">Quantity</p>
                <p class="text-lg font-bold text-gray-800 mt-0.5">{{ $resource->quantity }} {{ $resource->unit }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400">Unit Price</p>
                <p class="text-lg font-bold text-gray-800 mt-0.5">₱{{ number_format($resource->unit_price, 2) }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400">Total Cost</p>
                <p class="text-lg font-bold text-indigo-600 mt-0.5">₱{{ number_format($resource->total_cost, 2) }}</p>
            </div>
            @if($resource->delivery_date)
            <div>
                <p class="text-xs text-gray-400">Delivery Date</p>
                <p class="text-sm font-medium text-gray-800 mt-0.5">{{ $resource->delivery_date->format('M d, Y') }}</p>
            </div>
            @endif
            <div>
                <p class="text-xs text-gray-400">Currency</p>
                <p class="text-sm font-medium text-gray-800 mt-0.5">{{ $resource->currency }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400">Added</p>
                <p class="text-sm font-medium text-gray-800 mt-0.5">{{ $resource->created_at->format('M d, Y') }}</p>
            </div>
        </div>

        @if($resource->description)
        <div class="mt-6 pt-4 border-t border-gray-100">
            <p class="text-xs text-gray-400 mb-1">Description</p>
            <p class="text-sm text-gray-700">{{ $resource->description }}</p>
        </div>
        @endif

        @if($resource->notes)
        <div class="mt-4 pt-4 border-t border-gray-100">
            <p class="text-xs text-gray-400 mb-1">Notes</p>
            <p class="text-sm text-gray-700">{{ $resource->notes }}</p>
        </div>
        @endif
    </div>

    <div class="flex gap-3">
        @if(auth()->user()->isAdmin() || auth()->user()->isContractor())
            <a href="{{ route('resources.edit', $resource) }}" class="bg-indigo-600 text-white px-5 py-2 rounded-lg text-sm hover:bg-indigo-700 transition">
                <i class="fas fa-edit mr-1"></i> Edit
            </a>
            <form method="POST" action="{{ route('resources.destroy', $resource) }}"
                onsubmit="return confirm('Delete this resource?')">
                @csrf @method('DELETE')
                <button type="submit" class="bg-red-50 text-red-600 px-5 py-2 rounded-lg text-sm hover:bg-red-100 transition">
                    <i class="fas fa-trash mr-1"></i> Delete
                </button>
            </form>
        @endif
        <a href="{{ route('resources.index') }}" class="bg-gray-100 text-gray-600 px-5 py-2 rounded-lg text-sm hover:bg-gray-200 transition">
            Back to Resources
        </a>
    </div>
</div>
@endsection
