<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Properties — EstateFlow</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Custom range slider styling */
        .rotate-180 {
            transform: rotate(180deg);
        }
        input[type="range"] {
            -webkit-appearance: none;
            appearance: none;
            width: 100%;
            height: 6px;
            background: #e5e7eb;
            border-radius: 3px;
            outline: none;
        }
        input[type="range"]::-webkit-slider-thumb {
            -webkit-appearance: none;
            appearance: none;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            background: #4f46e5;
            cursor: pointer;
            border: 2px solid white;
            box-shadow: 0 1px 3px rgba(0,0,0,0.2);
        }
        input[type="range"]::-moz-range-thumb {
            width: 18px;
            height: 18px;
            border-radius: 50%;
            background: #4f46e5;
            cursor: pointer;
            border: 2px solid white;
            box-shadow: 0 1px 3px rgba(0,0,0,0.2);
        }
        .range-value {
            font-size: 0.8rem;
            color: #4f46e5;
            font-weight: 600;
        }
        /* Checkbox styling */
        .filter-checkbox:checked {
            background-color: #4f46e5;
            border-color: #4f46e5;
        }
    </style>
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

        @guest
        <div class="flex items-center gap-3">
            <a href="{{ route('home.browse') }}" class="text-sm text-indigo-600 font-semibold">Browse</a>
            <a href="{{ route('login') }}" class="text-sm text-gray-600 hover:text-indigo-600 transition font-medium px-3 py-1.5">Login</a>
            <a href="{{ route('register') }}" class="text-sm bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition font-medium">Register</a>
        </div>
        @else
        {{-- Logged-in Client Navigation --}}
        <div class="flex items-center gap-3">
            <a href="{{ route('home') }}" class="text-sm font-medium text-gray-600 hover:text-indigo-600 transition px-3 py-1.5">Home</a>
            <a href="{{ route('home.browse') }}" class="text-sm font-medium text-indigo-600 px-3 py-1.5">Browse</a>
            <div class="relative" id="clientDropdown">
                <button onclick="toggleClientNav()" class="flex items-center gap-2 text-sm font-medium px-3 py-1.5 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                    @if(auth()->user()->avatar)
                        <img src="{{ asset('storage/' . auth()->user()->avatar) }}" class="w-5 h-5 rounded-full object-cover">
                    @else
                        <div class="w-5 h-5 bg-indigo-400 rounded-full flex items-center justify-center text-xs font-bold">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>
                    @endif
                    {{ auth()->user()->name }}
                    <i class="fas fa-chevron-down text-xs transition-transform duration-200" id="clientNavArrow"></i>
                </button>
                <div id="clientNavMenu" class="hidden absolute right-0 mt-2 w-52 bg-white rounded-xl shadow-lg border border-gray-100 z-50 py-1">
                    <a href="{{ route('client.reservations') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition">
                        <i class="fas fa-calendar-check w-4 text-gray-400"></i> My Reservations
                    </a>
                    <a href="{{ route('client.payments') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition">
                        <i class="fas fa-receipt w-4 text-gray-400"></i> My Payments
                    </a>
                    <a href="{{ route('client.documents') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition">
                        <i class="fas fa-file-alt w-4 text-gray-400"></i> My Documents
                    </a>
                    <a href="{{ route('messages.index') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition">
                        <i class="fas fa-comments w-4 text-gray-400"></i> Messages
                    </a>
                    <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition">
                        <i class="fas fa-user-cog w-4 text-gray-400"></i> My Profile
                    </a>
                    <hr class="border-gray-100 my-1">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-red-500 hover:bg-red-50 transition">
                            <i class="fas fa-sign-out-alt w-4"></i> Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @endguest
    </div>
</nav>

<div class="max-w-7xl mx-auto px-6 py-10">

    <h1 class="text-2xl font-bold text-gray-900 mb-6">Browse Properties</h1>

    {{-- Filters --}}
    <form method="GET" action="{{ route('home.browse') }}" class="bg-white rounded-xl shadow-sm p-5 mb-8" id="filterForm">
        {{-- Row 1: Search + Location + Status + Buttons --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
            <div class="md:col-span-1">
                <label class="block text-xs font-medium text-gray-500 mb-1">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name or location..."
                    class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Location</label>
                <select name="location" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">All Locations</option>
                    <option value="Gatid Santa Cruz, Laguna" {{ request('location') == 'Gatid Santa Cruz, Laguna' ? 'selected' : '' }}>Gatid Santa Cruz, Laguna</option>
                    <option value="Oogong Santa Cruz, Laguna" {{ request('location') == 'Oogong Santa Cruz, Laguna' ? 'selected' : '' }}>Oogong Santa Cruz, Laguna</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Status</label>
                <select name="status" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">All Status</option>
                    <option value="available"          {{ request('status') === 'available'          ? 'selected' : '' }}>Available</option>
                    <option value="reserved"           {{ request('status') === 'reserved'           ? 'selected' : '' }}>Reserved</option>
                    <option value="under_construction" {{ request('status') === 'under_construction' ? 'selected' : '' }}>Under Construction</option>
                </select>
            </div>
            <div class="flex gap-2 items-end">
                <button type="submit" class="flex-1 bg-indigo-600 text-white py-2.5 rounded-lg text-sm hover:bg-indigo-700 transition font-medium">
                    <i class="fas fa-search mr-1"></i> Search
                </button>
                <a href="{{ route('home.browse') }}" class="px-3 py-2.5 bg-gray-100 text-gray-600 rounded-lg text-sm hover:bg-gray-200 transition">Reset</a>
            </div>
        </div>

        {{-- Row 2: Property Type Checkboxes --}}
        <div class="mb-4">
            <label class="block text-xs font-medium text-gray-500 mb-2">Property Type</label>
            <div class="flex flex-wrap gap-3">
                @foreach($propertyTypes as $type)
                    <label class="flex items-center gap-2 px-3 py-1.5 border border-gray-200 rounded-lg cursor-pointer hover:border-indigo-300 transition text-sm
                        {{ in_array((string)$type->id, (array)request('types', [])) ? 'bg-indigo-50 border-indigo-400' : 'bg-white' }}">
                        <input type="checkbox" name="types[]" value="{{ $type->id }}"
                            class="filter-checkbox w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500"
                            {{ in_array((string)$type->id, (array)request('types', [])) ? 'checked' : '' }}>
                        <span>{{ $type->name }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        {{-- Row 3: Price Range Sliders + Bedrooms + Bathrooms --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            {{-- Min Price Slider --}}
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Min Price: <span id="minPriceDisplay" class="range-value">₱{{ request('min_price') ? number_format((int)request('min_price')) : '0' }}</span></label>
                <input type="range" name="min_price" id="minPriceSlider" min="0" max="10000000" step="100000"
                    value="{{ request('min_price') ?? 0 }}"
                    oninput="document.getElementById('minPriceDisplay').textContent = '₱' + Number(this.value).toLocaleString();">
                <div class="flex justify-between text-xs text-gray-400 mt-1">
                    <span>₱0</span>
                    <span>₱10M</span>
                </div>
            </div>

            {{-- Max Price Slider --}}
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Max Price: <span id="maxPriceDisplay" class="range-value">₱{{ request('max_price') ? number_format((int)request('max_price')) : '10M' }}</span></label>
                <input type="range" name="max_price" id="maxPriceSlider" min="0" max="10000000" step="100000"
                    value="{{ request('max_price') ?? 10000000 }}"
                    oninput="document.getElementById('maxPriceDisplay').textContent = '₱' + Number(this.value).toLocaleString();">
                <div class="flex justify-between text-xs text-gray-400 mt-1">
                    <span>₱0</span>
                    <span>₱10M</span>
                </div>
            </div>

            {{-- Bedrooms --}}
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Bedrooms</label>
                <select name="bedrooms" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">Any</option>
                    @foreach([1,2,3,4,5] as $b)
                        <option value="{{ $b }}" {{ request('bedrooms') == $b ? 'selected' : '' }}>{{ $b }}+ Bedrooms</option>
                    @endforeach
                </select>
            </div>

            {{-- Bathrooms (NEW) --}}
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Bathrooms</label>
                <select name="bathrooms" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">Any</option>
                    @foreach([1,2,3,4,5] as $b)
                        <option value="{{ $b }}" {{ request('bathrooms') == $b ? 'selected' : '' }}>{{ $b }}+ Bathrooms</option>
                    @endforeach
                </select>
            </div>
        </div>
    </form>

    {{-- Results --}}
    <div class="flex items-center justify-between mb-4">
        <p class="text-sm text-gray-500">{{ $properties->total() }} properties found</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($properties as $property)
            @include('partials.property-card', ['property' => $property])
        @empty
            <div class="col-span-full text-center py-16 text-gray-400">
                <i class="fas fa-building text-5xl mb-4 block text-gray-200"></i>
                <p>No properties found matching your search.</p>
                <a href="{{ route('home.browse') }}" class="mt-4 inline-block text-indigo-600 hover:underline text-sm">Clear filters</a>
            </div>
        @endforelse
    </div>

    @if($properties->hasPages())
        <div class="mt-8">{{ $properties->links() }}</div>
    @endif
</div>

{{-- Footer --}}
<footer class="bg-gray-900 text-gray-400 py-8 px-6 text-center text-sm mt-16">
    <p>© {{ date('Y') }} EstateFlow. All rights reserved.</p>
    <div class="flex items-center justify-center gap-6 mt-2">
        <a href="{{ route('home') }}" class="hover:text-white transition">Home</a>
        <a href="{{ route('login') }}" class="hover:text-white transition">Login</a>
        <a href="{{ route('register') }}" class="hover:text-white transition">Register</a>
    </div>
</footer>

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

</body>
</html>