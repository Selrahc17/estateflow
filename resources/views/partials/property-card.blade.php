<div class="bg-white rounded-xl shadow-sm hover:shadow-md transition overflow-hidden group">
    @if($property->image_main)
        <img src="{{ asset($property->image_main) }}" alt="{{ $property->title }}" class="w-full h-48 object-cover group-hover:scale-105 transition duration-300">
    @else
        <div class="w-full h-48 bg-gradient-to-br from-indigo-100 to-blue-50 flex items-center justify-center">
            <i class="fas fa-building text-indigo-300 text-4xl"></i>
        </div>
    @endif
    <div class="p-5">
        <div class="flex items-start justify-between mb-2">
            <h3 class="font-semibold text-gray-800 text-sm leading-tight">{{ $property->title }}</h3>
            <span class="ml-2 flex-shrink-0 text-xs px-2 py-0.5 rounded-full font-medium
                {{ $property->status === 'available'          ? 'bg-green-100 text-green-700'  : '' }}
                {{ $property->status === 'reserved'           ? 'bg-yellow-100 text-yellow-700' : '' }}
                {{ $property->status === 'sold'               ? 'bg-red-100 text-red-700'       : '' }}
                {{ $property->status === 'under_construction' ? 'bg-blue-100 text-blue-700'     : '' }}">
                {{ ucfirst(str_replace('_', ' ', $property->status)) }}
            </span>
        </div>
        <p class="text-xs text-gray-400 mb-3"><i class="fas fa-map-marker-alt mr-1"></i>{{ $property->location ?? 'Location not set' }}</p>
        <div class="flex items-center gap-3 text-xs text-gray-500 mb-3">
            @if($property->bedrooms)
                <span><i class="fas fa-bed mr-1"></i>{{ $property->bedrooms }}</span>
            @endif
            @if($property->bathrooms)
                <span><i class="fas fa-bath mr-1"></i>{{ $property->bathrooms }}</span>
            @endif
            @if($property->area_sqm)
                <span><i class="fas fa-ruler-combined mr-1"></i>{{ $property->area_sqm }}sqm</span>
            @endif
        </div>
        <div class="flex items-center justify-between">
            <p class="text-lg font-bold text-indigo-600">₱{{ number_format($property->price, 0) }}</p>
            <a href="{{ route('home.property', $property) }}"
                class="text-xs bg-indigo-600 text-white px-3 py-1.5 rounded-lg hover:bg-indigo-700 transition">
                View Details
            </a>
        </div>
    </div>
</div>
