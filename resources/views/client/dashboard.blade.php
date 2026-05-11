<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Dashboard — EstateFlow</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50 font-sans">

{{-- Public Navbar --}}
<nav class="bg-white shadow-sm sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">
        <a href="{{ route('home') }}" class="flex items-center gap-2">
            <div class="w-8 h-8 bg-indigo-600 rounded-lg flex items-center justify-center">
                <i class="fas fa-building text-white text-sm"></i>
            </div>
            <span class="font-bold text-gray-900 text-lg">EstateFlow</span>
        </a>
        <div class="flex items-center gap-3">
            <a href="{{ route('home.browse') }}" class="text-sm text-gray-600 hover:text-indigo-600 transition font-medium">Browse</a>
            @auth
            <div class="relative" id="navDropdown">
                <button onclick="toggleNav()" class="text-sm text-gray-600 hover:text-indigo-600 transition font-medium px-3 py-1.5 bg-gray-100 rounded-lg flex items-center gap-1">
                    <i class="fas fa-user"></i> My Account <i class="fas fa-chevron-down text-xs ml-1" id="navArrow"></i>
                </button>
                <div id="navMenu" class="hidden absolute right-0 mt-1 w-48 bg-white rounded-lg shadow-lg border border-gray-100 z-50">
                    <a href="{{ route('client.dashboard') }}" class="block px-4 py-2.5 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 rounded-t-lg"><i class="fas fa-home w-5 text-gray-400"></i> Dashboard</a>
                    <a href="{{ route('client.reservations') }}" class="block px-4 py-2.5 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-600"><i class="fas fa-calendar-check w-5 text-gray-400"></i> My Reservations</a>
                    <a href="{{ route('client.payments') }}" class="block px-4 py-2.5 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-600"><i class="fas fa-receipt w-5 text-gray-400"></i> My Payments</a>
                    <a href="{{ route('client.documents') }}" class="block px-4 py-2.5 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-600"><i class="fas fa-file-alt w-5 text-gray-400"></i> My Documents</a>
                    <a href="{{ route('profile.edit') }}" class="block px-4 py-2.5 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-600"><i class="fas fa-user-cog w-5 text-gray-400"></i> My Profile</a>
                    <hr class="border-gray-100">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full text-left px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 rounded-b-lg"><i class="fas fa-sign-out-alt w-5"></i> Logout</button>
                    </form>
                </div>
            </div>
            @endauth
        </div>
    </div>
</nav>

<div class="max-w-7xl mx-auto px-6 py-8">

    <h1 class="text-2xl font-bold text-gray-900 mb-2">My Dashboard</h1>
    <p class="text-gray-500 text-sm mb-8">Welcome back, {{ auth()->user()->name }}</p>

@if(!$clientRecord)
<div class="mb-6 bg-yellow-50 border border-yellow-200 rounded-xl p-4 flex items-start gap-3">
    <i class="fas fa-exclamation-triangle text-yellow-500 mt-0.5"></i>
    <div>
        <p class="text-sm font-medium text-yellow-800">Your account is not fully set up yet.</p>
        <p class="text-xs text-yellow-600 mt-1">Please contact an administrator to complete your profile setup.</p>
    </div>
</div>
@endif

{{-- Stats --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="bg-white rounded-xl shadow-sm p-6 flex items-center gap-4">
        <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
            <i class="fas fa-building text-blue-600 text-xl"></i>
        </div>
        <div>
            <p class="text-sm text-gray-500">Available Properties</p>
            <p class="text-2xl font-bold text-gray-800">{{ $totalProperties }}</p>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-6 flex items-center gap-4">
        <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
            <i class="fas fa-calendar-check text-green-600 text-xl"></i>
        </div>
        <div>
            <p class="text-sm text-gray-500">My Reservations</p>
            <p class="text-2xl font-bold text-gray-800">{{ $myReservations }}</p>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm p-6 flex items-center gap-4">
        <div class="w-12 h-12 bg-indigo-100 rounded-xl flex items-center justify-center">
            <i class="fas fa-receipt text-indigo-600 text-xl"></i>
        </div>
        <div>
            <p class="text-sm text-gray-500">Total Paid</p>
            <p class="text-2xl font-bold text-gray-800">₱{{ number_format($totalPayments, 0) }}</p>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Recent Reservations --}}
    <div class="lg:col-span-2 bg-white rounded-xl shadow-sm p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-semibold text-gray-800">My Reservations</h3>
            <a href="{{ route('client.reservations') }}" class="text-sm text-indigo-600 hover:underline">View all</a>
        </div>

        @forelse($recentReservations as $res)
        <div class="flex items-center justify-between py-3 border-b border-gray-50 last:border-0">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-indigo-50 rounded-lg flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-building text-indigo-400"></i>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-800">{{ $res->property->title ?? '—' }}</p>
                    <p class="text-xs text-gray-400">
                        {{ $res->reservation_date->format('M d, Y') }}
                        @if($res->agent) · Agent: {{ $res->agent->full_name }} @endif
                    </p>
                </div>
            </div>
            <div class="text-right">
                <span class="text-xs px-2.5 py-1 rounded-full font-medium
                    {{ $res->status === 'confirmed' ? 'bg-green-100 text-green-700'   : '' }}
                    {{ $res->status === 'pending'   ? 'bg-yellow-100 text-yellow-700' : '' }}
                    {{ $res->status === 'cancelled' ? 'bg-red-100 text-red-700'       : '' }}
                    {{ $res->status === 'completed' ? 'bg-blue-100 text-blue-700'     : '' }}
                    {{ $res->status === 'expired'   ? 'bg-gray-100 text-gray-600'     : '' }}">
                    {{ ucfirst($res->status) }}
                </span>
                <p class="text-xs text-gray-400 mt-1">₱{{ number_format($res->reservation_fee, 0) }}</p>
            </div>
        </div>
        @empty
        <div class="text-center py-8 text-gray-400">
            <i class="fas fa-calendar-check text-3xl mb-2 block text-gray-200"></i>
            <p class="text-sm">No reservations yet.</p>
            <a href="{{ route('home.browse') }}" class="mt-3 inline-block bg-indigo-600 text-white px-4 py-2 rounded-lg text-xs hover:bg-indigo-700 transition">
                Browse Properties
            </a>
        </div>
        @endforelse
    </div>

    {{-- Quick Actions --}}
    <div class="space-y-4">
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="font-semibold text-gray-800 mb-4">Quick Actions</h3>
            <div class="space-y-2">
                <a href="{{ route('home.browse') }}"
                    class="flex items-center gap-3 p-3 bg-blue-50 rounded-xl hover:bg-blue-100 transition">
                    <i class="fas fa-building text-blue-600 w-5 text-center"></i>
                    <span class="text-sm font-medium text-blue-700">Browse Properties</span>
                </a>
                <a href="{{ route('client.reservations') }}"
                    class="flex items-center gap-3 p-3 bg-green-50 rounded-xl hover:bg-green-100 transition">
                    <i class="fas fa-calendar-check text-green-600 w-5 text-center"></i>
                    <span class="text-sm font-medium text-green-700">My Reservations</span>
                </a>
                <a href="{{ route('client.payments') }}"
                    class="flex items-center gap-3 p-3 bg-purple-50 rounded-xl hover:bg-purple-100 transition">
                    <i class="fas fa-receipt text-purple-600 w-5 text-center"></i>
                    <span class="text-sm font-medium text-purple-700">My Payments</span>
                </a>
                <a href="{{ route('messages.index') }}"
                    class="flex items-center gap-3 p-3 bg-indigo-50 rounded-xl hover:bg-indigo-100 transition">
                    <i class="fas fa-comments text-indigo-600 w-5 text-center"></i>
                    <span class="text-sm font-medium text-indigo-700">Messages</span>
                    @php $unreadMsgs = \App\Models\Message::where('to_user_id', auth()->id())->whereNull('read_at')->count(); @endphp
                    @if($unreadMsgs > 0)
                        <span class="ml-auto bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">{{ $unreadMsgs }}</span>
                    @endif
                </a>
                <a href="{{ route('profile.edit') }}"
                    class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl hover:bg-gray-100 transition">
                    <i class="fas fa-user-cog text-gray-600 w-5 text-center"></i>
                    <span class="text-sm font-medium text-gray-700">My Profile</span>
                </a>
            </div>
        </div>

        {{-- Client Info --}}
        @if($clientRecord)
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="font-semibold text-gray-800 mb-3 text-sm">My Profile</h3>
            <div class="space-y-2 text-sm">
                <div class="flex items-center gap-2 text-gray-600">
                    <i class="fas fa-user text-gray-400 w-4"></i>
                    {{ $clientRecord->full_name }}
                </div>
                <div class="flex items-center gap-2 text-gray-600">
                    <i class="fas fa-phone text-gray-400 w-4"></i>
                    {{ $clientRecord->phone }}
                </div>
                @if($clientRecord->email)
                <div class="flex items-center gap-2 text-gray-600">
                    <i class="fas fa-envelope text-gray-400 w-4"></i>
                    {{ $clientRecord->email }}
                </div>
                @endif
                <span class="inline-block text-xs px-2 py-0.5 rounded-full mt-1
                    {{ $clientRecord->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                    {{ ucfirst($clientRecord->status) }}
                </span>
            </div>
        </div>
        @endif
    </div>

    </div>
</div>

{{-- Footer --}}
<footer class="bg-gray-900 text-gray-400 py-8 px-6 text-center text-sm mt-16">
    <p>© {{ date('Y') }} EstateFlow. All rights reserved.</p>
    <div class="flex items-center justify-center gap-6 mt-2">
        <a href="{{ route('home') }}" class="hover:text-white transition">Home</a>
        <a href="{{ route('home.browse') }}" class="hover:text-white transition">Browse</a>
    </div>
</footer>

<script>
    function toggleNav() {
        const menu = document.getElementById('navMenu');
        const arrow = document.getElementById('navArrow');
        menu.classList.toggle('hidden');
        arrow && arrow.classList.toggle('rotate-180');
    }
    document.addEventListener('click', function(e) {
        const dd = document.getElementById('navDropdown');
        const menu = document.getElementById('navMenu');
        if (dd && menu && !dd.contains(e.target) && !menu.classList.contains('hidden')) {
            menu.classList.add('hidden');
            const arrow = document.getElementById('navArrow');
            if (arrow) arrow.classList.remove('rotate-180');
        }
    });
</script>

</body>
</html>
