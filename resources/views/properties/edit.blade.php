<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Property - EstateFlow</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex flex-col">
        <!-- Header -->
        <header class="bg-white shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">
                    <div class="flex items-center">
                        <h1 class="text-xl font-bold text-gray-900">EstateFlow</h1>
                    </div>
                    <div class="flex items-center space-x-4">
                        <span class="text-sm text-gray-600">{{ Auth::user()->name }}</span>
                        <span class="text-sm text-gray-500">{{ ucfirst(Auth::user()->role) }}</span>
                        <a href="{{ route('profile.edit') }}" class="text-sm text-indigo-600 hover:text-indigo-900">Profile</a>
                        <form action="{{ route('logout') }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="text-sm text-red-600 hover:text-red-900">Logout</button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-1">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">Edit Property</h2>

                <div class="bg-white rounded-lg shadow p-6">
                    @if($errors->any())
                        <div class="mb-4 bg-red-50 border border-red-200 rounded-lg p-4">
                            <ul class="text-sm text-red-600 list-disc list-inside space-y-1">
                                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                            </ul>
                        </div>
                    @endif
                    <form action="{{ route('properties.update', $property->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Basic Information -->
                            <div class="md:col-span-2">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Basic Information</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Property Type</label>
                                        <select name="property_type_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                            @foreach($propertyTypes as $type)
                                                <option value="{{ $type->id }}" {{ $property->property_type_id == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Title</label>
                                        <input type="text" name="title" value="{{ $property->title }}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="Enter property title">
                                    </div>
                                </div>
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                                    <textarea name="description" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="Enter property description">{{ $property->description }}</textarea>
                                </div>
                            </div>

                            <!-- Location & Coordinates -->
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Location</h3>
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Location</label>
                                    <select name="location" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                        <option value="">Select Location</option>
                                        <option value="Gatid Santa Cruz, Laguna" {{ $property->location == 'Gatid Santa Cruz, Laguna' ? 'selected' : '' }}>Gatid Santa Cruz, Laguna</option>
                                        <option value="Oogong Santa Cruz, Laguna" {{ $property->location == 'Oogong Santa Cruz, Laguna' ? 'selected' : '' }}>Oogong Santa Cruz, Laguna</option>
                                    </select>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Latitude</label>
                                        <input type="number" name="latitude" step="0.00000001" value="{{ $property->latitude }}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="Latitude">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Longitude</label>
                                        <input type="number" name="longitude" step="0.00000001" value="{{ $property->longitude }}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="Longitude">
                                    </div>
                                </div>
                            </div>

                            <!-- Property Details -->
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Property Details</h3>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Area (sqm)</label>
                                        <input type="number" name="area_sqm" step="0.01" value="{{ $property->area_sqm }}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="Area in square meters">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Price</label>
                                        <input type="number" name="price" step="0.01" value="{{ $property->price }}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="Property price">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Currency</label>
                                        <select name="currency" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                            <option value="PHP" {{ $property->currency == 'PHP' ? 'selected' : '' }}>PHP</option>
                                            <option value="USD" {{ $property->currency == 'USD' ? 'selected' : '' }}>USD</option>
                                            <option value="EUR" {{ $property->currency == 'EUR' ? 'selected' : '' }}>EUR</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                                        <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                            <option value="available" {{ $property->status == 'available' ? 'selected' : '' }}>Available</option>
                                            <option value="reserved" {{ $property->status == 'reserved' ? 'selected' : '' }}>Reserved</option>
                                            <option value="sold" {{ $property->status == 'sold' ? 'selected' : '' }}>Sold</option>
                                            <option value="under_construction" {{ $property->status == 'under_construction' ? 'selected' : '' }}>Under Construction</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Features -->
                            <div class="md:col-span-2">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Features</h3>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Bedrooms</label>
                                        <input type="number" name="bedrooms" value="{{ $property->bedrooms }}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="Number of bedrooms">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Bathrooms</label>
                                        <input type="number" name="bathrooms" value="{{ $property->bathrooms }}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="Number of bathrooms">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Garage Spaces</label>
                                        <input type="number" name="garage_spaces" value="{{ $property->garage_spaces }}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="Garage spaces">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Amenities</label>
                                        <input type="text" name="amenities" value="{{ implode(', ', is_array($property->amenities) ? $property->amenities : json_decode($property->amenities ?? '[]', true) ?? []) }}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="Comma-separated amenities (e.g., Pool, Gym, Garden)">
                                    </div>
                                </div>
                            </div>

                            <!-- Images -->
                            <div class="md:col-span-2">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Images</h3>
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Main Image</label>
                                    <input type="file" name="image_main" accept="image/*" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    @if($property->image_main)
                                        <div class="mt-2">
                                            <img src="{{ asset($property->image_main) }}" alt="Current main image" class="h-20 object-cover rounded">
                                        </div>
                                    @endif
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Additional Images (optional)</label>
                                    <input type="file" name="images[]" accept="image/*" multiple class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    @if($property->images)
                                        <div class="mt-2 grid grid-cols-4 gap-2">
                                            @foreach(json_decode($property->images) as $image)
                                                <img src="{{ asset($image) }}" alt="Additional image" class="w-full h-20 object-cover rounded">
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Options -->
                            <div class="md:col-span-2">
                                <div class="flex items-center space-x-4">
                                    <div>
                                        <label class="flex items-center">
                                            <input type="checkbox" name="is_featured" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded" {{ $property->is_featured ? 'checked' : '' }}>
                                            <span class="ml-2 text-sm text-gray-700">Featured Property</span>
                                        </label>
                                    </div>
                                    <div>
                                        <label class="flex items-center">
                                            <input type="checkbox" name="is_active" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded" {{ $property->is_active ? 'checked' : '' }}>
                                            <span class="ml-2 text-sm text-gray-700">Active</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-8 flex justify-end space-x-4">
                            <a href="{{ route('properties.index') }}" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">Cancel</a>
                            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">Update Property</button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</body>
</html>