<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'EstateFlow')</title>
    <link rel="manifest" href="/estateflow/public/manifest.json">
    <meta name="theme-color" content="#4f46e5">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="EstateFlow">
    <link rel="apple-touch-icon" href="/estateflow/public/icons/icon-192x192.png">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Sidebar transition */
        #sidebar { transition: transform 0.25s ease; }
        #sidebar-overlay { transition: opacity 0.25s ease; }
    </style>
</head>
<body class="bg-gray-100 font-sans">

{{-- Mobile Top Bar --}}
<div class="lg:hidden fixed top-0 left-0 right-0 z-40 bg-gray-900 flex items-center justify-between px-4 py-3">
    <button onclick="toggleSidebar()" class="text-white p-1">
        <i class="fas fa-bars text-lg" id="hamburger-icon"></i>
    </button>
    <a href="{{ route('dashboard') }}" class="flex items-center gap-2">
        <div class="w-7 h-7 rounded-lg overflow-hidden flex items-center justify-center">
            <img src="{{ asset('logo.png') }}" alt="EstateFlow" class="w-7 h-7 object-contain">
        </div>
        <span class="text-white font-bold text-base">EstateFlow</span>
    </a>
    <div class="flex items-center gap-3">
        @php $unreadMsgsMobile = \App\Models\Message::where('to_user_id', auth()->id())->whereNull('read_at')->count(); @endphp
        <a href="{{ route('messages.index') }}" class="relative text-gray-300">
            <i class="fas fa-comments text-lg"></i>
            @if($unreadMsgsMobile > 0)
                <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-4 h-4 flex items-center justify-center">{{ $unreadMsgsMobile }}</span>
            @endif
        </a>
        <a href="{{ route('notifications.index') }}" class="relative text-gray-300">
            <i class="fas fa-bell text-lg"></i>
            @php $unreadMobile = \App\Models\EstateNotification::where('notifiable_id', auth()->id())->where('notifiable_type', \App\Models\User::class)->unread()->count(); @endphp
            @if($unreadMobile > 0)
                <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-4 h-4 flex items-center justify-center">{{ $unreadMobile }}</span>
            @endif
        </a>
    </div>
</div>

{{-- Sidebar Overlay (mobile) --}}
<div id="sidebar-overlay" onclick="toggleSidebar()"
    class="hidden fixed inset-0 z-40 bg-black bg-opacity-50 lg:hidden"></div>

<div class="flex h-screen overflow-hidden pt-12 lg:pt-0">

    {{-- Sidebar --}}
    <aside id="sidebar" class="fixed lg:static inset-y-0 left-0 z-50 w-64 bg-gray-900 flex flex-col flex-shrink-0 transform -translate-x-full lg:translate-x-0 h-full">
        {{-- Logo --}}
        <div class="flex items-center gap-3 px-6 py-5 border-b border-gray-700">
            <div class="w-8 h-8 rounded-lg flex items-center justify-center overflow-hidden">
                <img src="{{ asset('logo.png') }}" alt="EstateFlow" class="w-8 h-8 object-contain">
            </div>
            <span class="text-white font-bold text-lg">EstateFlow</span>
            <button onclick="toggleSidebar()" class="ml-auto text-gray-400 hover:text-white lg:hidden">
                <i class="fas fa-times"></i>
            </button>
        </div>

        {{-- User Info --}}
        <div class="px-4 py-4 border-b border-gray-700">
            <div class="flex items-center gap-3">
                @if(auth()->user()->avatar)
                    <img src="{{ asset('storage/' . auth()->user()->avatar) }}"
                        class="w-9 h-9 rounded-full object-cover ring-2 ring-indigo-500">
                @else
                    <div class="w-9 h-9 bg-indigo-500 rounded-full flex items-center justify-center text-white font-semibold text-sm">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                @endif
                <div>
                    <p class="text-white text-sm font-medium">{{ auth()->user()->name }}</p>
                    <p class="text-gray-400 text-xs capitalize">{{ auth()->user()->role === 'contractor' ? 'staff' : auth()->user()->role }}</p>
                </div>
            </div>
        </div>

        {{-- Navigation --}}
        <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-1">

            <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm {{ request()->routeIs('dashboard') ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }} transition-all">
                <i class="fas fa-home w-4"></i> Dashboard
            </a>

            @if(auth()->user()->isAdmin())
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider px-4 pt-4 pb-1">Admin</p>
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm {{ request()->routeIs('admin.dashboard') ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }} transition-all">
                    <i class="fas fa-tachometer-alt w-4"></i> Admin Panel
                </a>
                <a href="{{ route('admin.users') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm {{ request()->routeIs('admin.users') ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }} transition-all">
                    <i class="fas fa-users w-4"></i> Users
                </a>
                <a href="{{ route('audit-logs.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm {{ request()->routeIs('audit-logs.*') ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }} transition-all">
                    <i class="fas fa-history w-4"></i> Audit Logs
                </a>
                <a href="{{ route('ai-predictions.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm {{ request()->routeIs('ai-predictions.*') ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }} transition-all">
                    <i class="fas fa-brain w-4"></i> AI Predictions
                </a>

                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider px-4 pt-4 pb-1">Oversight</p>
                <a href="{{ route('agent.reservations') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm {{ request()->routeIs('agent.reservations') ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }} transition-all">
                    <i class="fas fa-calendar-check w-4"></i> All Reservations
                </a>
                <a href="{{ route('contractor.projects') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm {{ request()->routeIs('contractor.projects') ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }} transition-all">
                    <i class="fas fa-project-diagram w-4"></i> All Projects
                </a>
                <a href="{{ route('contractor.tasks') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm {{ request()->routeIs('contractor.tasks') ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }} transition-all">
                    <i class="fas fa-tasks w-4"></i> All Tasks
                </a>
            @endif

            @if(auth()->user()->isAdmin() || auth()->user()->isAgent())
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider px-4 pt-4 pb-1">Sales</p>
                <a href="{{ route('properties.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm {{ request()->routeIs('properties.*') ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }} transition-all">
                    <i class="fas fa-building w-4"></i> Properties
                </a>
                <a href="{{ route('property-types.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm {{ request()->routeIs('property-types.*') ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }} transition-all">
                    <i class="fas fa-tags w-4"></i> Property Types
                </a>
                <a href="{{ route('clients.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm {{ request()->routeIs('clients.*') ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }} transition-all">
                    <i class="fas fa-users w-4"></i> Clients
                </a>
                <a href="{{ route('agents.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm {{ request()->routeIs('agents.*') ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }} transition-all">
                    <i class="fas fa-user-tie w-4"></i> Agents
                </a>
                <a href="{{ route('reservations.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm {{ request()->routeIs('reservations.*') ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }} transition-all">
                    <i class="fas fa-calendar-check w-4"></i> Reservations
                </a>
                @if(auth()->user()->isAdmin())
                <a href="{{ route('payments.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm {{ request()->routeIs('payments.*') ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }} transition-all">
                    <i class="fas fa-receipt w-4"></i> Payments
                </a>
                @endif
            @endif

            @if(auth()->user()->isAdmin())
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider px-4 pt-4 pb-1">Construction</p>
                <a href="{{ route('contractors.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm {{ request()->routeIs('contractors.*') ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }} transition-all">
                    <i class="fas fa-hard-hat w-4"></i> Staff
                </a>
                <a href="{{ route('projects.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm {{ request()->routeIs('projects.*') ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }} transition-all">
                    <i class="fas fa-project-diagram w-4"></i> Projects
                </a>
                <a href="{{ route('tasks.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm {{ request()->routeIs('tasks.*') ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }} transition-all">
                    <i class="fas fa-tasks w-4"></i> Tasks
                </a>
                <a href="{{ route('milestones.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm {{ request()->routeIs('milestones.*') ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }} transition-all">
                    <i class="fas fa-flag w-4"></i> Milestones
                </a>
                <a href="{{ route('budgets.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm {{ request()->routeIs('budgets.*') ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }} transition-all">
                    <i class="fas fa-wallet w-4"></i> Budgets
                </a>
                <a href="{{ route('progress-logs.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm {{ request()->routeIs('progress-logs.*') ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }} transition-all">
                    <i class="fas fa-clipboard-list w-4"></i> Progress Logs
                </a>
                <a href="{{ route('resources.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm {{ request()->routeIs('resources.*') ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }} transition-all">
                    <i class="fas fa-boxes w-4"></i> Resources
                </a>
                <a href="{{ route('documents.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm {{ request()->routeIs('documents.index') || request()->routeIs('documents.create') || request()->routeIs('documents.show') || request()->routeIs('documents.edit') ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }} transition-all">
                    <i class="fas fa-file-alt w-4"></i> Documents
                </a>
                <a href="{{ route('documents.checker') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm {{ request()->routeIs('documents.checker') ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }} transition-all">
                    <i class="fas fa-clipboard-check w-4"></i> Doc Checker
                </a>
            @endif

@if(auth()->user()->isContractor())
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider px-4 pt-4 pb-1">My Work</p>
                <a href="{{ route('contractor.dashboard') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm {{ request()->routeIs('contractor.dashboard') ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }} transition-all">
                    <i class="fas fa-tachometer-alt w-4"></i> Dashboard
                </a>
                <a href="{{ route('contractor.projects') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm {{ request()->routeIs('contractor.projects') || request()->routeIs('contractor.project.detail') ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }} transition-all">
                    <i class="fas fa-project-diagram w-4"></i> My Projects
                </a>
                <a href="{{ route('progress-logs.create') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm {{ request()->routeIs('progress-logs.create') ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }} transition-all">
                    <i class="fas fa-camera w-4"></i> Upload Progress
                </a>
            @endif

            @if(auth()->user()->isAgent())
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider px-4 pt-4 pb-1">My Work</p>
                <a href="{{ route('agent.dashboard') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm {{ request()->routeIs('agent.dashboard') ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }} transition-all">
                    <i class="fas fa-tachometer-alt w-4"></i> Agent Panel
                </a>
                <a href="{{ route('agent.reservations') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm {{ request()->routeIs('agent.reservations') ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }} transition-all">
                    <i class="fas fa-calendar-check w-4"></i> Reservations
                </a>
            @endif

            @if(auth()->user()->isFinance())
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider px-4 pt-4 pb-1">Finance</p>
                <a href="{{ route('finance.dashboard') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm {{ request()->routeIs('finance.dashboard') ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }} transition-all">
                    <i class="fas fa-tachometer-alt w-4"></i> Finance Panel
                </a>
                <a href="{{ route('finance.payments') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm {{ request()->routeIs('finance.payments') ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }} transition-all">
                    <i class="fas fa-receipt w-4"></i> Payments
                </a>
                <a href="{{ route('finance.payments.create') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm {{ request()->routeIs('finance.payments.create') ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }} transition-all">
                    <i class="fas fa-plus w-4"></i> Record Payment
                </a>
                <a href="{{ route('finance.pagibig') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm {{ request()->routeIs('finance.pagibig') ? 'bg-indigo-600 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' }} transition-all">
                    <i class="fas fa-home w-4"></i> Pag-IBIG Payments
                </a>
            @endif

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
                @php $unread = \App\Models\EstateNotification::where('notifiable_id', auth()->id())->where('notifiable_type', \App\Models\User::class)->unread()->count(); @endphp
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
    <div class="flex-1 flex flex-col overflow-hidden lg:ml-0">

        {{-- Top Bar (desktop only) --}}
        <header class="hidden lg:flex bg-white shadow-sm px-6 py-4 items-center justify-between flex-shrink-0">
            <div>
                <h1 class="text-xl font-bold text-gray-800">@yield('page-title', 'Dashboard')</h1>
                @hasSection('page-subtitle')
                    <p class="text-sm text-gray-500">@yield('page-subtitle')</p>
                @endif
            </div>
            <div class="flex items-center gap-4">
                <span class="text-sm text-gray-400">{{ now()->format('l, F j, Y') }}</span>
                @if(session('success'))
                    <span class="bg-green-100 text-green-700 text-xs px-3 py-1.5 rounded-full font-medium">
                        <i class="fas fa-check mr-1"></i>{{ session('success') }}
                    </span>
                @endif
            </div>
        </header>

        {{-- Page Content --}}
        <main class="flex-1 overflow-y-auto p-4 lg:p-6">
            @if($errors->any())
                <div class="mb-4 bg-red-50 border border-red-200 rounded-lg p-4">
                    <ul class="text-sm text-red-600 list-disc list-inside space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</div>
</body>
</html>

<script>
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/estateflow/public/sw.js')
            .then(reg => console.log('SW registered:', reg.scope))
            .catch(err => console.log('SW error:', err));
    });
}

function toggleSidebar() {
    const sidebar  = document.getElementById('sidebar');
    const overlay  = document.getElementById('sidebar-overlay');
    const isOpen   = !sidebar.classList.contains('-translate-x-full');
    if (isOpen) {
        sidebar.classList.add('-translate-x-full');
        overlay.classList.add('hidden');
    } else {
        sidebar.classList.remove('-translate-x-full');
        overlay.classList.remove('hidden');
    }
}
</script>

@include('partials.chatbot')
