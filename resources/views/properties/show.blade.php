@extends('layouts.app')

@section('title', $property->title . ' - EstateFlow')
@section('page-title', $property->title)
@section('page-subtitle', ($property->propertyType->name ?? '') . ' · ' . ($property->location ?? ''))

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Left: Images --}}
    <div class="lg:col-span-2 space-y-4">

        {{-- Main Image --}}
        @if($property->image_main)
            <img src="{{ asset($property->image_main) }}" alt="{{ $property->title }}"
                class="w-full h-80 object-cover rounded-xl shadow-sm">
        @else
            <div class="w-full h-80 bg-gradient-to-br from-indigo-100 to-blue-50 rounded-xl flex items-center justify-center">
                <i class="fas fa-building text-indigo-300 text-6xl"></i>
            </div>
        @endif

        {{-- Gallery --}}
        @if($property->images && count($property->images))
            <div class="grid grid-cols-4 gap-2">
                @foreach($property->images as $image)
                    <img src="{{ asset($image) }}" alt="Property image"
                        class="w-full h-20 object-cover rounded-lg">
                @endforeach
            </div>
        @endif

        {{-- Description --}}
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="font-semibold text-gray-800 mb-3">Description</h3>
            <p class="text-sm text-gray-600 leading-relaxed">{{ $property->description ?? 'No description provided.' }}</p>
        </div>

        {{-- Amenities --}}
        @if($property->amenities && count($property->amenities))
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="font-semibold text-gray-800 mb-3">Amenities</h3>
            <div class="flex flex-wrap gap-2">
                @foreach($property->amenities as $amenity)
                    <span class="px-3 py-1 bg-indigo-50 text-indigo-700 text-sm rounded-full">
                        {{ ucfirst($amenity) }}
                    </span>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Reservations (admin/agent only) --}}
        @if(auth()->user()->isAdmin() || auth()->user()->isAgent())
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="font-semibold text-gray-800 mb-4">Reservations ({{ $property->reservations->count() }})</h3>
            @forelse($property->reservations as $reservation)
            <div class="flex items-center justify-between py-3 border-b border-gray-50 last:border-0">
                <div>
                    <p class="text-sm font-medium text-gray-800">{{ $reservation->client->full_name ?? '—' }}</p>
                    <p class="text-xs text-gray-400">{{ $reservation->reservation_date->format('M d, Y') }}</p>
                </div>
                <span class="text-xs px-2.5 py-1 rounded-full font-medium
                    {{ $reservation->status === 'confirmed' ? 'bg-green-100 text-green-700'   : '' }}
                    {{ $reservation->status === 'pending'   ? 'bg-yellow-100 text-yellow-700' : '' }}
                    {{ $reservation->status === 'cancelled' ? 'bg-red-100 text-red-700'       : '' }}
                    {{ $reservation->status === 'completed' ? 'bg-blue-100 text-blue-700'     : '' }}">
                    {{ ucfirst($reservation->status) }}
                </span>
            </div>
            @empty
            <p class="text-sm text-gray-400">No reservations yet.</p>
            @endforelse
        </div>
        @endif

    </div>

    {{-- Right: Details & Actions --}}
    <div class="lg:col-span-1 space-y-6">

        {{-- Price & Status --}}
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <span class="text-xs px-2.5 py-1 rounded-full font-medium
                    {{ $property->status === 'available'         ? 'bg-green-100 text-green-700' : '' }}
                    {{ $property->status === 'reserved'          ? 'bg-yellow-100 text-yellow-700' : '' }}
                    {{ $property->status === 'sold'              ? 'bg-red-100 text-red-700' : '' }}
                    {{ $property->status === 'under_construction'? 'bg-blue-100 text-blue-700' : '' }}">
                    {{ ucfirst(str_replace('_', ' ', $property->status)) }}
                </span>
                @if($property->is_featured)
                    <span class="text-xs px-2 py-1 bg-yellow-100 text-yellow-700 rounded-full">
                        <i class="fas fa-star mr-1"></i>Featured
                    </span>
                @endif
            </div>
            <p class="text-3xl font-bold text-indigo-600 mb-1">₱{{ number_format($property->price, 2) }}</p>
            <p class="text-xs text-gray-400">{{ $property->currency }}</p>

            {{-- Reserve Button for clients --}}
            @if(auth()->user()->isClient() && $property->status === 'available')
                <a href="{{ route('reservations.create', ['property_id' => $property->id]) }}"
                    class="mt-4 block text-center bg-indigo-600 text-white py-2.5 rounded-lg text-sm hover:bg-indigo-700 transition font-medium">
                    <i class="fas fa-calendar-check mr-1"></i> Reserve This Property
                </a>
            @elseif($property->status !== 'available')
                <div class="mt-4 text-center bg-gray-100 text-gray-500 py-2.5 rounded-lg text-sm">
                    Not Available
                </div>
            @endif

            @if(auth()->user()->isAdmin() || auth()->user()->isAgent())
                <div class="mt-4 flex gap-2">
                    <a href="{{ route('properties.edit', $property) }}"
                        class="flex-1 text-center bg-indigo-600 text-white py-2 rounded-lg text-sm hover:bg-indigo-700 transition">
                        <i class="fas fa-edit mr-1"></i> Edit
                    </a>
                    <a href="{{ route('reservations.create', ['property_id' => $property->id]) }}"
                        class="flex-1 text-center bg-green-600 text-white py-2 rounded-lg text-sm hover:bg-green-700 transition">
                        Reserve
                    </a>
                </div>
            @endif
        </div>

        {{-- Property Details --}}
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="font-semibold text-gray-800 mb-4">Details</h3>
            <div class="space-y-3 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-500">Type</span>
                    <span class="font-medium text-gray-800">{{ $property->propertyType->name ?? '—' }}</span>
                </div>
                @if($property->bedrooms)
                <div class="flex justify-between">
                    <span class="text-gray-500">Bedrooms</span>
                    <span class="font-medium text-gray-800">{{ $property->bedrooms }}</span>
                </div>
                @endif
                @if($property->bathrooms)
                <div class="flex justify-between">
                    <span class="text-gray-500">Bathrooms</span>
                    <span class="font-medium text-gray-800">{{ $property->bathrooms }}</span>
                </div>
                @endif
                @if($property->garage_spaces)
                <div class="flex justify-between">
                    <span class="text-gray-500">Garage</span>
                    <span class="font-medium text-gray-800">{{ $property->garage_spaces }} space(s)</span>
                </div>
                @endif
                @if($property->area_sqm)
                <div class="flex justify-between">
                    <span class="text-gray-500">Area</span>
                    <span class="font-medium text-gray-800">{{ $property->area_sqm }} sqm</span>
                </div>
                @endif
                @if($property->location)
                <div class="flex justify-between">
                    <span class="text-gray-500">Location</span>
                    <span class="font-medium text-gray-800 text-right max-w-xs">{{ $property->location }}</span>
                </div>
                @endif
            </div>
        </div>

        {{-- Back Button --}}
        <a href="{{ route('properties.index') }}"
            class="block text-center bg-gray-100 text-gray-600 py-2.5 rounded-lg text-sm hover:bg-gray-200 transition">
            <i class="fas fa-arrow-left mr-1"></i> Back to Properties
        </a>

    </div>
</div>
@endsection
