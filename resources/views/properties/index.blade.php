@extends('layouts.app')

@section('title', 'Properties - EstateFlow')
@section('page-title', 'Properties')
@section('page-subtitle', 'Browse and manage all properties')

@section('content')

{{-- Search & Filter --}}
<div class="bg-white rounded-xl shadow-sm p-6 mb-6">
    <form action="{{ route('properties.search') }}" method="GET">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Location</label>
                <input type="text" name="location" value="{{ request('location') }}" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="Search location...">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Type</label>
                <select name="property_type_id" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">All Types</option>
                    @foreach(\App\Models\PropertyType::where('is_active', true)->get() as $type)
                        <option value="{{ $type->id }}" {{ request('property_type_id') == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Status</label>
                <select name="status" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">All Status</option>
                    <option value="available" {{ request('status') === 'available' ? 'selected' : '' }}>Available</option>
                    <option value="reserved" {{ request('status') === 'reserved' ? 'selected' : '' }}>Reserved</option>
                    <option value="sold" {{ request('status') === 'sold' ? 'selected' : '' }}>Sold</option>
                    <option value="under_construction" {{ request('status') === 'under_construction' ? 'selected' : '' }}>Under Construction</option>
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-indigo-700 transition">
                    <i class="fas fa-search mr-1"></i> Search
                </button>
                <a href="{{ route('properties.index') }}" class="px-4 py-2 bg-gray-100 text-gray-600 rounded-lg text-sm hover:bg-gray-200 transition">Reset</a>
            </div>
        </div>
    </form>
</div>

{{-- Header Row --}}
<div class="flex items-center justify-between mb-4">
    <div class="flex items-center gap-3">
        <p class="text-sm text-gray-500">{{ $properties->total() }} properties found</p>
        @if(auth()->user()->isAdmin())
            <a href="{{ route('properties.archived') }}" class="text-xs text-gray-400 hover:text-red-600 transition flex items-center gap-1">
                <i class="fas fa-archive"></i> View Archived ({{ \App\Models\Property::onlyTrashed()->count() }})
            </a>
        @endif
    </div>
    @if(auth()->user()->isAdmin() || auth()->user()->isAgent())
        <a href="{{ route('properties.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-indigo-700 transition">
            <i class="fas fa-plus mr-1"></i> Add Property
        </a>
    @endif
</div>

{{-- Properties Grid --}}
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    @forelse($properties as $property)
    <div class="bg-white rounded-xl shadow-sm hover:shadow-md transition overflow-hidden">
        @if($property->image_main)
            <img src="{{ asset($property->image_main) }}" alt="{{ $property->title }}" class="w-full h-48 object-cover">
        @else
            <div class="w-full h-48 bg-gradient-to-br from-indigo-100 to-blue-50 flex items-center justify-center">
                <i class="fas fa-building text-indigo-300 text-4xl"></i>
            </div>
        @endif
        <div class="p-5">
            <div class="flex items-start justify-between mb-2">
                <div class="flex items-center gap-2 min-w-0">
                    <h3 class="font-semibold text-gray-800 text-sm leading-tight truncate">{{ $property->title }}</h3>
                    @if($property->is_featured)
                        <span class="flex-shrink-0 text-xs bg-yellow-100 text-yellow-700 px-2 py-0.5 rounded-full font-medium">
                            <i class="fas fa-star text-yellow-500 mr-0.5"></i> Featured
                        </span>
                    @endif
                </div>
                <span class="ml-2 flex-shrink-0 text-xs px-2 py-0.5 rounded-full font-medium
                    {{ $property->status === 'available' ? 'bg-green-100 text-green-700' : '' }}
                    {{ $property->status === 'reserved' ? 'bg-yellow-100 text-yellow-700' : '' }}
                    {{ $property->status === 'sold' ? 'bg-red-100 text-red-700' : '' }}
                    {{ $property->status === 'under_construction' ? 'bg-blue-100 text-blue-700' : '' }}">
                    {{ ucfirst(str_replace('_', ' ', $property->status)) }}
                </span>
            </div>
            <p class="text-xs text-gray-400 mb-3"><i class="fas fa-map-marker-alt mr-1"></i>{{ $property->location ?? 'Location not set' }}</p>
            <div class="flex items-center gap-4 text-xs text-gray-500 mb-3">
                @if($property->bedrooms)
                    <span><i class="fas fa-bed mr-1"></i>{{ $property->bedrooms }} beds</span>
                @endif
                @if($property->bathrooms)
                    <span><i class="fas fa-bath mr-1"></i>{{ $property->bathrooms }} baths</span>
                @endif
                @if($property->area_sqm)
                    <span><i class="fas fa-ruler-combined mr-1"></i>{{ $property->area_sqm }} sqm</span>
                @endif
            </div>
            <div class="flex items-center justify-between">
                <p class="text-lg font-bold text-indigo-600">₱{{ number_format($property->price, 2) }}</p>
                <span class="text-xs text-gray-400">{{ $property->propertyType->name ?? '' }}</span>
            </div>
            <div class="mt-4 flex gap-2">
                <a href="{{ route('properties.show', $property->id) }}" class="flex-1 text-center bg-indigo-600 text-white py-2 rounded-lg text-xs hover:bg-indigo-700 transition">View</a>
                @if(auth()->user()->isAdmin() || auth()->user()->isAgent())
                    <a href="{{ route('properties.edit', $property->id) }}" class="flex-1 text-center bg-gray-100 text-gray-700 py-2 rounded-lg text-xs hover:bg-gray-200 transition">Edit</a>
                @endif
                @if(auth()->user()->isAdmin())
                    <form method="POST" action="{{ route('properties.toggle-featured', $property->id) }}">
                        @csrf @method('PATCH')
                        <button type="submit" title="{{ $property->is_featured ? 'Remove from featured' : 'Mark as featured' }}"
                            class="px-3 py-2 rounded-lg text-xs transition
                            {{ $property->is_featured ? 'bg-yellow-100 text-yellow-600 hover:bg-yellow-200' : 'bg-gray-100 text-gray-400 hover:bg-yellow-50 hover:text-yellow-500' }}">
                            <i class="fas fa-star"></i>
                        </button>
                    </form>
                    <form method="POST" action="{{ route('properties.destroy', $property->id) }}"
                        onsubmit="return confirm('Archive this property? It can be restored later.')">
                        @csrf @method('DELETE')
                        <button type="submit" title="Archive property"
                            class="px-3 py-2 rounded-lg text-xs bg-gray-100 text-gray-400 hover:bg-orange-50 hover:text-orange-500 transition">
                            <i class="fas fa-archive"></i>
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
    @empty
    <div class="col-span-full text-center py-16">
        <i class="fas fa-building text-gray-200 text-5xl mb-4"></i>
        <p class="text-gray-400">No properties found.</p>
        @if(auth()->user()->isAdmin() || auth()->user()->isAgent())
            <a href="{{ route('properties.create') }}" class="mt-4 inline-block bg-indigo-600 text-white px-6 py-2 rounded-lg text-sm hover:bg-indigo-700 transition">Add First Property</a>
        @endif
    </div>
    @endforelse
</div>

@if($properties->hasPages())
    <div class="mt-6">{{ $properties->links() }}</div>
@endif

@endsection
