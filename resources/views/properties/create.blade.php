@extends('layouts.app')

@section('title', 'Create Property - EstateFlow')
@section('page-title', 'Create Property')
@section('page-subtitle', 'Add a new property listing')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm p-8">
        <form action="{{ route('properties.store') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
            @csrf

            {{-- Basic Information --}}
            <div>
                <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wider mb-4">Basic Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Property Type <span class="text-red-500">*</span></label>
                        <select name="property_type_id" required
                            class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('property_type_id') border-red-400 @enderror">
                            <option value="">Select Property Type</option>
                            @foreach($propertyTypes as $type)
                                <option value="{{ $type->id }}" {{ old('property_type_id') == $type->id ? 'selected' : '' }}>
                                    {{ $type->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('property_type_id')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Title <span class="text-red-500">*</span></label>
                        <input type="text" name="title" value="{{ old('title') }}" required
                            placeholder="e.g. Villa Rosalina Unit 12"
                            class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('title') border-red-400 @enderror">
                        @error('title')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs font-medium text-gray-600 mb-1">Description</label>
                        <textarea name="description" rows="3"
                            placeholder="Describe the property..."
                            class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('description') }}</textarea>
                    </div>
                </div>
            </div>

            {{-- Location --}}
            <div>
                <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wider mb-4">Location</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-xs font-medium text-gray-600 mb-1">Location</label>
                        <select name="location"
                            class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">Select Location</option>
                            <option value="Gatid Santa Cruz, Laguna"  {{ old('location') === 'Gatid Santa Cruz, Laguna'  ? 'selected' : '' }}>Gatid Santa Cruz, Laguna</option>
                            <option value="Oogong Santa Cruz, Laguna" {{ old('location') === 'Oogong Santa Cruz, Laguna' ? 'selected' : '' }}>Oogong Santa Cruz, Laguna</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Latitude</label>
                        <input type="number" name="latitude" value="{{ old('latitude') }}" step="0.00000001"
                            placeholder="e.g. 14.2791"
                            class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Longitude</label>
                        <input type="number" name="longitude" value="{{ old('longitude') }}" step="0.00000001"
                            placeholder="e.g. 121.4113"
                            class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>
            </div>

            {{-- Pricing & Status --}}
            <div>
                <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wider mb-4">Pricing & Status</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Price <span class="text-red-500">*</span></label>
                        <input type="number" name="price" value="{{ old('price') }}" step="0.01" required
                            placeholder="e.g. 2500000"
                            class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('price') border-red-400 @enderror">
                        @error('price')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Currency <span class="text-red-500">*</span></label>
                        <select name="currency" required
                            class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="PHP" {{ old('currency', 'PHP') === 'PHP' ? 'selected' : '' }}>PHP</option>
                            <option value="USD" {{ old('currency') === 'USD' ? 'selected' : '' }}>USD</option>
                            <option value="EUR" {{ old('currency') === 'EUR' ? 'selected' : '' }}>EUR</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Status <span class="text-red-500">*</span></label>
                        <select name="status" required
                            class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="available"         {{ old('status', 'available') === 'available'         ? 'selected' : '' }}>Available</option>
                            <option value="reserved"          {{ old('status') === 'reserved'                       ? 'selected' : '' }}>Reserved</option>
                            <option value="sold"              {{ old('status') === 'sold'                           ? 'selected' : '' }}>Sold</option>
                            <option value="under_construction"{{ old('status') === 'under_construction'             ? 'selected' : '' }}>Under Construction</option>
                        </select>
                    </div>
                </div>
            </div>

            {{-- Property Details --}}
            <div>
                <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wider mb-4">Property Details</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Area (sqm)</label>
                        <input type="number" name="area_sqm" value="{{ old('area_sqm') }}" step="0.01"
                            placeholder="e.g. 120"
                            class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Bedrooms</label>
                        <input type="number" name="bedrooms" value="{{ old('bedrooms') }}" min="0"
                            placeholder="e.g. 3"
                            class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Bathrooms</label>
                        <input type="number" name="bathrooms" value="{{ old('bathrooms') }}" min="0"
                            placeholder="e.g. 2"
                            class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Garage Spaces</label>
                        <input type="number" name="garage_spaces" value="{{ old('garage_spaces') }}" min="0"
                            placeholder="e.g. 1"
                            class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div class="col-span-2 md:col-span-4">
                        <label class="block text-xs font-medium text-gray-600 mb-1">Amenities <span class="text-gray-400">(comma-separated)</span></label>
                        <input type="text" name="amenities" value="{{ old('amenities') }}"
                            placeholder="e.g. Swimming Pool, Gym, Garden, CCTV"
                            class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>
            </div>

            {{-- Images --}}
            <div>
                <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wider mb-4">Images</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Main Image</label>
                        <input type="file" name="image_main" accept="image/*"
                            class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:bg-indigo-50 file:text-indigo-600">
                        @error('image_main')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Additional Images <span class="text-gray-400">(optional)</span></label>
                        <input type="file" name="images[]" accept="image/*" multiple
                            class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:bg-indigo-50 file:text-indigo-600">
                    </div>
                </div>
            </div>

            {{-- Options --}}
            <div class="flex items-center gap-6">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_featured" value="1" {{ old('is_featured') ? 'checked' : '' }}
                        class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                    <span class="text-sm text-gray-700">Featured Property</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}
                        class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                    <span class="text-sm text-gray-700">Active (visible to public)</span>
                </label>
            </div>

            {{-- Actions --}}
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                <a href="{{ route('properties.index') }}"
                    class="px-5 py-2 border border-gray-200 text-gray-600 rounded-lg text-sm hover:bg-gray-50 transition">
                    Cancel
                </a>
                <button type="submit"
                    class="px-6 py-2 bg-indigo-600 text-white rounded-lg text-sm hover:bg-indigo-700 transition font-medium">
                    <i class="fas fa-plus mr-1"></i> Create Property
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
