@extends('layouts.app')

@section('title', 'Archived Properties — EstateFlow')
@section('page-title', 'Archived Properties')
@section('page-subtitle', 'Soft-deleted properties — restore or permanently delete')

@section('content')

@if(session('success'))
    <div class="mb-4 bg-green-50 border border-green-200 text-green-700 text-sm px-4 py-3 rounded-xl flex items-center gap-2">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
    </div>
@endif

<div class="flex items-center justify-between mb-6">
    <p class="text-sm text-gray-500">{{ $properties->total() }} archived properties</p>
    <a href="{{ route('properties.index') }}" class="text-sm text-indigo-600 hover:underline">
        <i class="fas fa-arrow-left mr-1"></i> Back to Active Properties
    </a>
</div>

<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-100">
            <tr>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Property</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Type</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Price</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Archived At</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @forelse($properties as $property)
            <tr class="hover:bg-gray-50 transition">
                <td class="px-5 py-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg overflow-hidden flex-shrink-0 bg-gray-100">
                            @if($property->image_main)
                                <img src="{{ asset($property->image_main) }}" class="w-10 h-10 object-cover">
                            @else
                                <div class="w-10 h-10 flex items-center justify-center">
                                    <i class="fas fa-building text-gray-300"></i>
                                </div>
                            @endif
                        </div>
                        <div>
                            <p class="font-medium text-gray-800">{{ $property->title }}</p>
                            <p class="text-xs text-gray-400">{{ $property->location ?? '—' }}</p>
                        </div>
                    </div>
                </td>
                <td class="px-5 py-4 text-xs text-gray-500">{{ $property->propertyType->name ?? '—' }}</td>
                <td class="px-5 py-4 font-medium text-indigo-600">₱{{ number_format($property->price, 0) }}</td>
                <td class="px-5 py-4">
                    <span class="text-xs px-2.5 py-1 rounded-full font-medium bg-gray-100 text-gray-500">
                        {{ ucfirst(str_replace('_', ' ', $property->status)) }}
                    </span>
                </td>
                <td class="px-5 py-4 text-xs text-gray-500">{{ $property->deleted_at->format('M d, Y h:i A') }}</td>
                <td class="px-5 py-4">
                    <div class="flex items-center gap-2">
                        <form method="POST" action="{{ route('properties.restore', $property->id) }}">
                            @csrf @method('PATCH')
                            <button class="text-xs bg-green-100 text-green-700 px-3 py-1.5 rounded-lg hover:bg-green-200 transition">
                                <i class="fas fa-undo mr-1"></i>Restore
                            </button>
                        </form>
                        <form method="POST" action="{{ route('properties.force-delete', $property->id) }}"
                            onsubmit="return confirm('Permanently delete this property? This cannot be undone.')">
                            @csrf @method('DELETE')
                            <button class="text-xs bg-red-100 text-red-600 px-3 py-1.5 rounded-lg hover:bg-red-200 transition">
                                <i class="fas fa-trash mr-1"></i>Delete Forever
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="px-5 py-16 text-center text-gray-400">
                    <i class="fas fa-archive text-4xl mb-3 block text-gray-200"></i>
                    No archived properties.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    @if($properties->hasPages())
        <div class="px-5 py-4 border-t border-gray-100">{{ $properties->links() }}</div>
    @endif
</div>

@endsection
