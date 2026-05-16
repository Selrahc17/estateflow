<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'EstateFlow')</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        #client-sidebar { transition: transform 0.25s ease; }
    </style>
</head>
<body class="bg-gray-50 font-sans">

{{-- Mobile Top Bar --}}
<div class="lg:hidden fixed top-0 left-0 right-0 z-40 bg-gray-900 flex items-center justify-between px-4 py-3">
    <button onclick="toggleSidebar()" class="text-white p-1">
        <i class="fas fa-bars text-lg"></i>
    </button>
    <a href="{{ route('home') }}" class="flex items-center gap-2">
        <img src="{{ asset('logo.png') }}" class="w-7 h-7 object-contain">
        <span class="text-white font-bold text-base">EstateFlow</span>
    </a>
    <a href="{{ route('notifications.index') }}" class="relative text-white">
        <i class="fas fa-bell text-lg"></i>
        @php $unreadMobile = \App\Models\EstateNotification::where('notifiable_id', auth()->id())->where('notifiable_type', \App\Models\User::class)->where('is_read', false)->count(); @endphp
        @if($unreadMobile > 0)
            <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-4 h-4 flex items-center justify-center">{{ $unreadMobile }}</span>
        @endif
    </a>
</div>

{{-- Sidebar Overlay --}}
<div id="sidebar-overlay" onclick="toggleSidebar()" class="hidden fixed inset-0 z-40 bg-black bg-opacity-50 lg:hidden"></div>

<div class="flex h-screen overflow-hidden pt-12 lg:pt-0">

    {{-- Sidebar --}}
    <aside id="client-sidebar" class="fixed lg:static inset-y-0 left-0 z-50 w-64 bg-gray-900 flex flex-col flex-shrink-0 transform -translate-x-full lg:translate-x-0 h-full">

        {{-- Logo --}}
        <div class="flex items-center gap-3 px-6 py-5 border-b border-gray-700">
            <img src="{{ asset('logo.png') }}" class="w-8 h-8 object-contain">
            <span class="text-white font-bold text-lg">EstateFlow</span>
            <button onclick="toggleSidebar()" class="ml-auto text-gray-400 hover:text-white lg:hidden">
                <i class="fas fa-times"></i>
            </button>
        </div>

        {{-- User Info --}}
        <div class="px-4 py-4 border-b border-gray-700">
            <div class="flex items-center gap-3">
                @if(auth()->user()->avatar)
                    <img src="{{ asset('storage/' . auth()->user()->avatar) }}" class="w-9 h-9 rounded-full object-cover ring-2 ring-indigo-500">
                @else
                    <div class="w-9 h-9 bg-indigo-500 rounded-full flex items-center justify-center text-white font-semibold text-sm">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                @endif
                <div class="min-w-0">
                    <p class="text-white text-sm font-medium truncate">{{ auth()->user()->name }}</p>
                    <p class="text-gray-400 text-xs">Client</p>
                </div>
            </div>
        </div>

        {{-- Navigation --}}
        <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-1">
            <a href="{{ route('client.dashboard') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm transition-all {{ request()->routeIs('client.dashboard') ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                <i class="fas fa-home w-4"></i> Dashboard
            </a>
            <a href="{{ route('client.reservations') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm transition-all {{ request()->routeIs('client.reservations') || request()->routeIs('reservations.create') ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                <i class="fas fa-calendar-check w-4"></i> My Reservations
            </a>
            <a href="{{ route('client.payments') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm transition-all {{ request()->routeIs('client.payments') ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                <i class="fas fa-receipt w-4"></i> My Payments
            </a>
            <a href="{{ route('client.documents') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm transition-all {{ request()->routeIs('client.documents') ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                <i class="fas fa-file-alt w-4"></i> My Documents
            </a>
            <a href="{{ route('home.browse') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm text-gray-300 hover:bg-gray-700 hover:text-white transition-all">
                <i class="fas fa-building w-4"></i> Browse Properties
            </a>
            <a href="{{ route('client.follow-ups') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm transition-all {{ request()->routeIs('client.follow-ups') ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }}">
                <i class="fas fa-calendar w-4"></i> Follow-Ups
            </a>
        </nav>

        {{-- Bottom --}}
        <div class="px-3 py-4 border-t border-gray-700 space-y-1">
            <a href="{{ route('messages.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm {{ request()->routeIs('messages.*') ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }} transition-all">
                <i class="fas fa-comments w-4"></i> Messages
                @php $unreadMsgs = \App\Models\Message::where('to_user_id', auth()->id())->whereNull('read_at')->count(); @endphp
                @if($unreadMsgs > 0)
                    <span class="ml-auto bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">{{ $unreadMsgs }}</span>
                @endif
            </a>
            <a href="{{ route('notifications.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm {{ request()->routeIs('notifications.*') ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }} transition-all">
                <i class="fas fa-bell w-4"></i> Notifications
                @php $unread = \App\Models\EstateNotification::where('notifiable_id', auth()->id())->where('notifiable_type', \App\Models\User::class)->where('is_read', false)->count(); @endphp
                @if($unread > 0)
                    <span class="ml-auto bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">{{ $unread }}</span>
                @endif
            </a>
            <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm text-gray-300 hover:bg-gray-700 hover:text-white transition-all">
                <i class="fas fa-user-cog w-4"></i> Profile
            </a>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="w-full flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm text-red-400 hover:bg-red-900 hover:text-red-300 transition-all">
                    <i class="fas fa-sign-out-alt w-4"></i> Logout
                </button>
            </form>
        </div>
    </aside>

    {{-- Main Content --}}
    <div class="flex-1 flex flex-col overflow-hidden">

        {{-- Top Bar --}}
        <header class="hidden lg:flex bg-white shadow-sm px-6 py-4 items-center justify-between flex-shrink-0">
            <div>
                <h1 class="text-xl font-bold text-gray-800">@yield('page-title', 'Dashboard')</h1>
                @hasSection('page-subtitle')
                    <p class="text-sm text-gray-500 mt-0.5">@yield('page-subtitle')</p>
                @endif
            </div>
            <div class="flex items-center gap-4">
                <span class="text-sm text-gray-400">{{ now()->format('l, F j, Y') }}</span>
                @if(session('success'))
                    <span class="bg-green-100 text-green-700 text-xs px-3 py-1.5 rounded-full font-medium">
                        <i class="fas fa-check mr-1"></i>{{ session('success') }}
                    </span>
                @endif
                @if(session('error'))
                    <span class="bg-red-100 text-red-700 text-xs px-3 py-1.5 rounded-full font-medium">
                        <i class="fas fa-exclamation-circle mr-1"></i>{{ session('error') }}
                    </span>
                @endif
            </div>
        </header>

        {{-- Mobile session messages --}}
        @if(session('success'))
        <div class="lg:hidden mx-4 mt-2 bg-green-50 border border-green-200 text-green-700 text-sm px-4 py-3 rounded-xl">
            <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
        </div>
        @endif
        @if(session('error'))
        <div class="lg:hidden mx-4 mt-2 bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3 rounded-xl">
            <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
        </div>
        @endif

        <main class="flex-1 overflow-y-auto p-4 lg:p-6">
            @if($errors->any())
                <div class="mb-4 bg-red-50 border border-red-200 rounded-xl p-4">
                    <ul class="text-sm text-red-600 list-disc list-inside space-y-1">
                        @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                    </ul>
                </div>
            @endif
            @yield('content')
        </main>
    </div>
</div>

<script>
function toggleSidebar() {
    const sidebar = document.getElementById('client-sidebar');
    const overlay = document.getElementById('sidebar-overlay');
    const isOpen  = !sidebar.classList.contains('-translate-x-full');
    if (isOpen) {
        sidebar.classList.add('-translate-x-full');
        overlay.classList.add('hidden');
    } else {
        sidebar.classList.remove('-translate-x-full');
        overlay.classList.remove('hidden');
    }
}
</script>

@stack('scripts')
</body>
</html>
