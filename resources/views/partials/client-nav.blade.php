<nav class="bg-white shadow-sm sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">

        {{-- Logo --}}
        <a href="{{ route('home') }}" class="flex items-center gap-2">
            <div class="w-8 h-8 rounded-lg flex items-center justify-center overflow-hidden">
                <img src="/estateflow/public/logo.png" alt="EstateFlow" class="w-8 h-8 object-contain">
            </div>
            <span class="font-bold text-gray-900 text-lg">EstateFlow</span>
        </a>

        {{-- Right side --}}
        <div class="flex items-center gap-3">
            <a href="{{ route('home') }}" class="text-sm font-medium text-gray-600 hover:text-indigo-600 transition px-3 py-1.5">Home</a>
        <a href="{{ route('home.browse') }}" class="text-sm font-medium text-gray-600 hover:text-indigo-600 transition px-3 py-1.5">Browse</a>

            {{-- My Account Dropdown --}}
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
                        @php $unreadMsgs = \App\Models\Message::where('to_user_id', auth()->id())->whereNull('read_at')->count(); @endphp
                        @if($unreadMsgs > 0)
                            <span class="ml-auto bg-red-500 text-white text-xs rounded-full px-1.5 py-0.5">{{ $unreadMsgs }}</span>
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

    </div>
</nav>

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
