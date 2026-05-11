<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $property->title }} — EstateFlow</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50 font-sans">

{{-- Navbar --}}
<nav class="bg-white shadow-sm sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">
        <a href="{{ route('home') }}" class="flex items-center gap-2">
            <div class="w-8 h-8 rounded-lg flex items-center justify-center overflow-hidden">
                <img src="/estateflow/public/logo.png" alt="EstateFlow" class="w-8 h-8 object-contain">
            </div>
            <span class="font-bold text-gray-900 text-lg">EstateFlow</span>
        </a>
        <div class="flex items-center gap-3">
            <a href="{{ route('home') }}" class="text-sm font-medium text-gray-600 hover:text-indigo-600 transition px-3 py-1.5">Home</a>
            <a href="{{ route('home.browse') }}" class="text-sm font-medium text-gray-600 hover:text-indigo-600 transition px-3 py-1.5">Browse</a>
            @auth
                @if(auth()->user()->isClient())
                <div class="relative" id="clientDropdown">
                    <button onclick="toggleClientNav()" class="flex items-center gap-2 text-sm font-medium px-3 py-1.5 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                        @if(auth()->user()->avatar)
                            <img src="{{ asset('storage/' . auth()->user()->avatar) }}" class="w-5 h-5 rounded-full object-cover">
                        @else
                            <div class="w-5 h-5 bg-indigo-400 rounded-full flex items-center justify-center text-xs font-bold">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
                        @endif
                        {{ auth()->user()->name }}
                        <i class="fas fa-chevron-down text-xs" id="clientNavArrow"></i>
                    </button>
                    <div id="clientNavMenu" class="hidden absolute right-0 mt-2 w-52 bg-white rounded-xl shadow-lg border border-gray-100 z-50 py-1">
                        <a href="{{ route('client.reservations') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition"><i class="fas fa-calendar-check w-4 text-gray-400"></i> My Reservations</a>
                        <a href="{{ route('client.payments') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition"><i class="fas fa-receipt w-4 text-gray-400"></i> My Payments</a>
                        <a href="{{ route('client.documents') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition"><i class="fas fa-file-alt w-4 text-gray-400"></i> My Documents</a>
                        <a href="{{ route('messages.index') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition"><i class="fas fa-comments w-4 text-gray-400"></i> Messages</a>
                        <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition"><i class="fas fa-user-cog w-4 text-gray-400"></i> My Profile</a>
                        <hr class="border-gray-100 my-1">
                        <form method="POST" action="{{ route('logout') }}">@csrf
                            <button type="submit" class="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-red-500 hover:bg-red-50 transition"><i class="fas fa-sign-out-alt w-4"></i> Logout</button>
                        </form>
                    </div>
                </div>
                @endif
            @else
                <a href="{{ route('login') }}" class="text-sm text-gray-600 hover:text-indigo-600 transition font-medium px-3 py-1.5">Login</a>
                <a href="{{ route('register') }}" class="text-sm bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition font-medium">Register</a>
            @endauth
        </div>
    </div>
</nav>

<div class="max-w-7xl mx-auto px-6 py-10">

    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-sm text-gray-400 mb-6">
        <a href="{{ route('home') }}" class="hover:text-indigo-600 transition">Home</a>
        <span>/</span>
        <a href="{{ route('home.browse') }}" class="hover:text-indigo-600 transition">Browse</a>
        <span>/</span>
        <span class="text-gray-700">{{ $property->title }}</span>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

        {{-- Left: Images & Details --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Main Image --}}
            @if($property->image_main)
                <img src="{{ asset($property->image_main) }}" alt="{{ $property->title }}"
                    class="w-full h-96 object-cover rounded-2xl shadow-sm">
            @else
                <div class="w-full h-96 bg-gradient-to-br from-indigo-100 to-blue-50 rounded-2xl flex items-center justify-center">
                    <i class="fas fa-building text-indigo-300 text-6xl"></i>
                </div>
            @endif

            {{-- Gallery --}}
            @if($property->images && count($property->images))
            <div class="grid grid-cols-4 gap-2">
                @foreach($property->images as $img)
                    <img src="{{ asset($img) }}" class="w-full h-20 object-cover rounded-lg">
                @endforeach
            </div>
            @endif

            {{-- Description --}}
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="font-semibold text-gray-800 mb-3">About this property</h2>
                <p class="text-sm text-gray-600 leading-relaxed">{{ $property->description ?? 'No description provided.' }}</p>
            </div>

            {{-- Amenities --}}
            @if($property->amenities && count($property->amenities))
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="font-semibold text-gray-800 mb-3">Amenities</h2>
                <div class="flex flex-wrap gap-2">
                    @foreach($property->amenities as $amenity)
                        <span class="px-3 py-1 bg-indigo-50 text-indigo-700 text-sm rounded-full">{{ ucfirst($amenity) }}</span>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        {{-- Right: Price & CTA --}}
        <div class="space-y-6">

            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs px-2.5 py-1 rounded-full font-medium
                        {{ $property->status === 'available'          ? 'bg-green-100 text-green-700'   : '' }}
                        {{ $property->status === 'reserved'           ? 'bg-yellow-100 text-yellow-700' : '' }}
                        {{ $property->status === 'sold'               ? 'bg-red-100 text-red-700'       : '' }}
                        {{ $property->status === 'under_construction' ? 'bg-blue-100 text-blue-700'     : '' }}">
                        {{ ucfirst(str_replace('_', ' ', $property->status)) }}
                    </span>
                    @if($property->is_featured)
                        <span class="text-xs bg-yellow-100 text-yellow-700 px-2 py-1 rounded-full">
                            <i class="fas fa-star mr-1"></i>Featured
                        </span>
                    @endif
                </div>

                <p class="text-3xl font-bold text-indigo-600 mb-1">₱{{ number_format($property->price, 0) }}</p>
                <p class="text-xs text-gray-400 mb-4">{{ $property->currency }}</p>

                @if($property->status === 'available')
                    @auth
                        <a href="{{ route('reservations.create', ['property_id' => $property->id]) }}"
                            class="block text-center bg-indigo-600 text-white py-3 rounded-xl font-semibold hover:bg-indigo-700 transition mb-3">
                            <i class="fas fa-calendar-check mr-2"></i>Reserve This Property
                        </a>
                    @else
                        <a href="{{ route('login') }}?redirect={{ urlencode(route('reservations.create', ['property_id' => $property->id])) }}"
                            class="block text-center bg-indigo-600 text-white py-3 rounded-xl font-semibold hover:bg-indigo-700 transition mb-3">
                            <i class="fas fa-calendar-check mr-2"></i>Reserve This Property
                        </a>
                        <p class="text-xs text-center text-gray-400">You need to <a href="{{ route('register') }}" class="text-indigo-600 hover:underline">create an account</a> to reserve.</p>
                    @endauth
                @else
                    <div class="text-center bg-gray-100 text-gray-500 py-3 rounded-xl text-sm">
                        Not available for reservation
                    </div>
                @endif
            </div>

            {{-- Property Details --}}
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="font-semibold text-gray-800 mb-4">Property Details</h3>
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
                    @if($property->area_sqm)
                    <div class="flex justify-between">
                        <span class="text-gray-500">Area</span>
                        <span class="font-medium text-gray-800">{{ $property->area_sqm }} sqm</span>
                    </div>
                    @endif
                    @if($property->garage_spaces)
                    <div class="flex justify-between">
                        <span class="text-gray-500">Garage</span>
                        <span class="font-medium text-gray-800">{{ $property->garage_spaces }} space(s)</span>
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

            @guest
            {{-- Already have account --}}
            <div class="bg-indigo-50 rounded-xl p-4 text-center">
                <p class="text-sm text-indigo-700 font-medium mb-2">Already have an account?</p>
                <a href="{{ route('login') }}" class="text-sm bg-indigo-600 text-white px-6 py-2 rounded-lg hover:bg-indigo-700 transition">
                    Login to Reserve
                </a>
            </div>
            @endguest
        </div>
    </div>

    {{-- Similar Properties --}}
    @if($similar->count())
    <div class="mt-16">
        <h2 class="text-xl font-bold text-gray-900 mb-6">Similar Properties</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @foreach($similar as $prop)
                @include('partials.property-card', ['property' => $prop])
            @endforeach
        </div>
    </div>
    @endif
</div>

{{-- Footer --}}
<footer class="bg-gray-900 text-gray-400 py-8 px-6 text-center text-sm mt-16">
    <p>© {{ date('Y') }} EstateFlow. All rights reserved.</p>
    <div class="flex items-center justify-center gap-6 mt-2">
        <a href="{{ route('home') }}" class="hover:text-white transition">Home</a>
        <a href="{{ route('home.browse') }}" class="hover:text-white transition">Browse</a>
        @guest
        <a href="{{ route('login') }}" class="hover:text-white transition">Login</a>
        <a href="{{ route('register') }}" class="hover:text-white transition">Register</a>
        @endguest
    </div>
</footer>

</body>
</html>

<script>
function toggleClientNav() {
    const menu  = document.getElementById('clientNavMenu');
    const arrow = document.getElementById('clientNavArrow');
    menu.classList.toggle('hidden');
    arrow.classList.toggle('rotate-180');
}
document.addEventListener('click', function(e) {
    const dd   = document.getElementById('clientDropdown');
    const menu = document.getElementById('clientNavMenu');
    if (dd && menu && !dd.contains(e.target) && !menu.classList.contains('hidden')) {
        menu.classList.add('hidden');
        document.getElementById('clientNavArrow').classList.remove('rotate-180');
    }
});
</script>
