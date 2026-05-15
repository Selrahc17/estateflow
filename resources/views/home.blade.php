<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Villa Rosalina — We create the best Home for you.</title>
    <link rel="manifest" href="/estateflow/public/manifest.json">
    <meta name="theme-color" content="#4f46e5">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="EstateFlow">
    <link rel="apple-touch-icon" href="/estateflow/public/icons/icon-192x192.png">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50 font-sans">

{{-- Navbar --}}
<nav class="bg-white shadow-sm sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">
        <a href="{{ route('home') }}" class="flex items-center gap-2">
            <div class="w-8 h-8 rounded-lg flex items-center justify-center overflow-hidden">
                <img src="{{ asset('logo.png') }}" alt="EstateFlow" class="w-8 h-8 object-contain">
            </div>
            <span class="font-bold text-gray-900 text-lg">EstateFlow</span>
        </a>

        @guest
        {{-- Guest Navbar --}}
        <div class="flex items-center gap-3">
            <a href="{{ route('home.browse') }}" class="text-sm text-gray-600 hover:text-indigo-600 transition font-medium">Browse Properties</a>
            <a href="{{ route('login') }}" class="text-sm text-gray-600 hover:text-indigo-600 transition font-medium px-3 py-1.5">Login</a>
            <a href="{{ route('register') }}" class="text-sm bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition font-medium">Register</a>
        </div>
        @else
        {{-- Logged-in Client Navigation --}}
        <div class="flex items-center gap-3">
            <a href="{{ route('home') }}" class="text-sm font-medium text-gray-600 hover:text-indigo-600 transition px-3 py-1.5">Home</a>
            <a href="{{ route('home.browse') }}" class="text-sm font-medium text-gray-600 hover:text-indigo-600 transition px-3 py-1.5">Browse</a>
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
                        @if($unreadMessages > 0)
                            <span class="ml-auto bg-red-500 text-white text-xs rounded-full px-1.5 py-0.5">{{ $unreadMessages }}</span>
                        @endif
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

{{-- Hero --}}
<section class="bg-gradient-to-br from-indigo-900 via-indigo-800 to-blue-900 text-white py-24 px-6">
    <div class="max-w-4xl mx-auto text-center">
        <h1 class="text-5xl font-bold mb-4 leading-tight">Find Your Dream Property</h1>
        <p class="text-indigo-200 text-lg mb-10">Browse hundreds of properties across the Villa Rosalina Homes Corp. Buy, reserve, or inquire — all in one place.</p>

        {{-- Search Bar --}}
        <form action="{{ route('home.browse') }}" method="GET" class="bg-white rounded-2xl p-3 flex flex-col md:flex-row gap-3 shadow-2xl">
            <input type="text" name="search" placeholder="Search by location or property name..."
                class="flex-1 px-4 py-3 text-gray-800 text-sm rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-400">
            <select name="type" class="px-4 py-3 text-gray-600 text-sm rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-400">
                <option value="">All Types</option>
                @foreach($propertyTypes as $type)
                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                @endforeach
            </select>
            <button type="submit" class="bg-indigo-600 text-white px-8 py-3 rounded-xl font-medium hover:bg-indigo-700 transition">
                <i class="fas fa-search mr-2"></i>Search
            </button>
        </form>
    </div>
</section>

{{-- Stats --}}
<section class="bg-white border-b border-gray-100">
    <div class="max-w-7xl mx-auto px-6 py-8 grid grid-cols-2 md:grid-cols-4 gap-6 text-center">
        <div>
            <p class="text-3xl font-bold text-indigo-600">{{ $totalProperties }}+</p>
            <p class="text-sm text-gray-500 mt-1">Total Properties</p>
        </div>
        <div>
            <p class="text-3xl font-bold text-green-600">{{ $availableCount }}</p>
            <p class="text-sm text-gray-500 mt-1">Available Now</p>
        </div>
        <div>
            <p class="text-3xl font-bold text-blue-600">{{ $propertyTypes->count() }}</p>
            <p class="text-sm text-gray-500 mt-1">Property Types</p>
        </div>
        <div>
            <p class="text-3xl font-bold text-purple-600">100%</p>
            <p class="text-sm text-gray-500 mt-1">Trusted Listings</p>
        </div>
    </div>
</section>

{{-- Featured Properties --}}
@if($featuredProperties->count())
<section class="max-w-7xl mx-auto px-6 py-16">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Featured Properties</h2>
            <p class="text-gray-500 text-sm mt-1">Hand-picked listings just for you</p>
        </div>
        <a href="{{ route('home.browse') }}" class="text-sm text-indigo-600 hover:underline font-medium">View all →</a>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($featuredProperties as $property)
            @include('partials.property-card', ['property' => $property])
        @endforeach
    </div>
</section>
@endif

{{-- Latest Properties --}}
<section class="bg-gray-50 py-16">
    <div class="max-w-7xl mx-auto px-6">
        <div class="flex items-center justify-between mb-8">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Latest Listings</h2>
                <p class="text-gray-500 text-sm mt-1">Newly added properties</p>
            </div>
            <a href="{{ route('home.browse') }}" class="text-sm text-indigo-600 hover:underline font-medium">Browse all →</a>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($latestProperties as $property)
                @include('partials.property-card', ['property' => $property])
            @endforeach
        </div>
    </div>
</section>

{{-- Browse by Type --}}
<section class="max-w-7xl mx-auto px-6 py-16">
    <h2 class="text-2xl font-bold text-gray-900 mb-8 text-center">Browse by Type</h2>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        @foreach($propertyTypes as $type)
        <a href="{{ route('home.browse', ['type' => $type->id]) }}"
            class="bg-white rounded-xl p-6 text-center shadow-sm hover:shadow-md hover:border-indigo-300 border border-gray-100 transition group">
            <div class="w-12 h-12 bg-indigo-100 rounded-xl flex items-center justify-center mx-auto mb-3 group-hover:bg-indigo-600 transition">
                <i class="fas fa-building text-indigo-600 group-hover:text-white transition"></i>
            </div>
            <p class="font-semibold text-gray-800">{{ $type->name }}</p>
            <p class="text-xs text-gray-400 mt-1">
                {{ \App\Models\Property::where('property_type_id', $type->id)->where('status', 'available')->count() }} available
            </p>
        </a>
        @endforeach
    </div>
</section>

{{-- CTA (hide for clients since they already have account) --}}
@guest
<section class="bg-indigo-600 text-white py-16 px-6 text-center">
    <h2 class="text-3xl font-bold mb-3">Ready to find your property?</h2>
    <p class="text-indigo-200 mb-8">Create a free account to reserve properties, track your transactions, and chat with agents.</p>
    <div class="flex items-center justify-center gap-4">
        <a href="{{ route('register') }}" class="bg-white text-indigo-600 px-8 py-3 rounded-xl font-semibold hover:bg-indigo-50 transition">
            Get Started Free
        </a>
        <a href="{{ route('home.browse') }}" class="border border-indigo-400 text-white px-8 py-3 rounded-xl font-semibold hover:bg-indigo-700 transition">
            Browse Properties
        </a>
    </div>
</section>
@endguest

{{-- Footer --}}
<footer class="bg-gray-900 text-gray-400 py-10 px-6 text-center text-sm">
    <div class="flex items-center justify-center gap-2 mb-3">
        <div class="w-6 h-6 rounded flex items-center justify-center overflow-hidden">
            <img src="{{ asset('logo.png') }}" alt="EstateFlow" class="w-6 h-6 object-contain">
        </div>
        <span class="text-white font-semibold">EstateFlow</span>
    </div>
    <p>© {{ date('Y') }} EstateFlow. All rights reserved.</p>
    <div class="flex items-center justify-center gap-6 mt-3">
        <a href="{{ route('home.browse') }}" class="hover:text-white transition">Browse</a>
        @guest
        <a href="{{ route('login') }}" class="hover:text-white transition">Login</a>
        <a href="{{ route('register') }}" class="hover:text-white transition">Register</a>
        @endguest
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

if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/estateflow/public/sw.js')
            .then(reg => console.log('SW registered:', reg.scope))
            .catch(err => console.log('SW error:', err));
    });
}
</script>

@include('partials.chatbot')

</body>
</html>