<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $property->title }} - EstateFlow</title>
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
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Property Images -->
                    <div>
                        @if($property->image_main)
                            <img src="{{ asset($property->image_main) }}" alt="{{ $property->title }}" class="w-full h-96 object-cover rounded-lg">
                        @else
                            <div class="w-full h-96 bg-gray-200 rounded-lg flex items-center justify-center">
                                <span class="text-gray-400">No Image</span>
                            </div>
                        @endif
                        @if($property->images)
                            <div class="grid grid-cols-4 gap-2 mt-4">
                                @foreach(json_decode($property->images) as $image)
                                    <img src="{{ asset($image) }}" alt="Property image" class="w-full h-20 object-cover rounded">
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <!-- Property Details -->
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 mb-4">{{ $property->title }}</h1>
                        <div class="flex items-center mb-4">
                            <span class="px-3 py-1 bg-blue-100 text-blue-800 text-sm rounded-full">
                                {{ $property->propertyType->name ?? 'Unknown' }}
                            </span>
                            @if($property->is_featured)
                                <span class="px-3 py-1 bg-yellow-100 text-yellow-800 text-sm rounded-full ml-2">Featured</span>
                            @endif
                        </div>
                        <div class="text-4xl font-bold text-gray-900 mb-6">{{ number_format($property->price, 2) }} {{ $property->currency }}</div>
                        <div class="grid grid-cols-2 gap-4 mb-6">
                            <div>
                                <div class="text-sm text-gray-500">Bedrooms</div>
                                <div class="text-lg font-semibold">{{ $property->bedrooms ?? 'N/A' }}</div>
                            </div>
                            <div>
                                <div class="text-sm text-gray-500">Bathrooms</div>
                                <div class="text-lg font-semibold">{{ $property->bathrooms ?? 'N/A' }}</div>
                            </div>
                            <div>
                                <div class="text-sm text-gray-500">Area</div>
                                <div class="text-lg font-semibold">{{ $property->area_sqm ?? 'N/A' }} sqm</div>
                            </div>
                            <div>
                                <div class="text-sm text-gray-500">Garage</div>
                                <div class="text-lg font-semibold">{{ $property->garage_spaces ?? 'N/A' }}</div>
                            </div>
                        </div>
                        <div class="mb-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">Description</h3>
                            <p class="text-gray-700 leading-relaxed">{{ $property->description }}</p>
                        </div>
                        <div class="mb-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">Location</h3>
                            <p class="text-gray-700">{{ $property->location }}</p>
                        </div>
                        @if($property->amenities)
                            <div class="mb-6">
                                <h3 class="text-lg font-semibold text-gray-900 mb-2">Amenities</h3>
                                <div class="flex flex-wrap gap-2">
                                    @foreach(json_decode($property->amenities) as $amenity)
                                        <span class="px-3 py-1 bg-gray-100 text-gray-800 text-sm rounded-full">{{ $amenity }}</span>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                        <div class="flex space-x-4">
                            @if($property->status === 'available')
                                <button class="flex-1 bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
                                    Reserve Now
                                </button>
                            @elseif($property->status === 'reserved')
                                <span class="flex-1 bg-yellow-400 text-white px-4 py-2 rounded-md">Reserved</span>
                            @elseif($property->status === 'sold')
                                <span class="flex-1 bg-gray-400 text-white px-4 py-2 rounded-md">Sold</span>
                            @elseif($property->status === 'under_construction')
                                <span class="flex-1 bg-orange-400 text-white px-4 py-2 rounded-md">Under Construction</span>
                            @endif
                            <a href="{{ route('properties.index') }}" class="flex-1 bg-gray-200 text-gray-800 px-4 py-2 rounded-md hover:bg-gray-300">Back to List</a>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>