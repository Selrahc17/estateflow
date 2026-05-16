@extends('layouts.app')

@section('title', 'User Management - EstateFlow')
@section('page-title', 'User Management')
@section('page-subtitle', 'Manage all system users')

@section('content')

{{-- Stats --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
    <div class="bg-white rounded-xl shadow-sm p-6 flex items-center gap-4">
        <div class="w-12 h-12 bg-indigo-100 rounded-xl flex items-center justify-center">
            <i class="fas fa-users text-indigo-600 text-xl"></i>
        </div>
        <div>
            <p class="text-sm text-gray-500">Total Users</p>
            <p class="text-2xl font-bold text-gray-800">{{ $totalUsers }}</p>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-6 flex items-center gap-4">
        <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
            <i class="fas fa-user-check text-green-600 text-xl"></i>
        </div>
        <div>
            <p class="text-sm text-gray-500">Active Users</p>
            <p class="text-2xl font-bold text-gray-800">{{ $activeUsers }}</p>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-6 flex items-center gap-4">
        <div class="w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center">
            <i class="fas fa-user-times text-red-600 text-xl"></i>
        </div>
        <div>
            <p class="text-sm text-gray-500">Inactive Users</p>
            <p class="text-2xl font-bold text-gray-800">{{ $inactiveUsers }}</p>
        </div>
    </div>
</div>

{{-- Filters --}}
<div class="bg-white rounded-xl shadow-sm p-5 mb-6">
    <form method="GET" action="{{ route('admin.users') }}" class="flex flex-wrap gap-3 items-end">
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Search</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Name or email..." class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 w-56">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Role</label>
            <select name="role" class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">All Roles</option>
                <option value="admin"      {{ request('role') === 'admin'      ? 'selected' : '' }}>Admin</option>
                <option value="agent"      {{ request('role') === 'agent'      ? 'selected' : '' }}>Agent</option>
                <option value="finance"    {{ request('role') === 'finance'    ? 'selected' : '' }}>Finance</option>
                <option value="staff" {{ request('role') === 'staff' ? 'selected' : '' }}>Staff</option>
                <option value="client"     {{ request('role') === 'client'     ? 'selected' : '' }}>Client</option>
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Status</label>
            <select name="status" class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">All Status</option>
                <option value="active"   {{ request('status') === 'active'   ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
            </select>
        </div>
        <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-indigo-700 transition">
            <i class="fas fa-search mr-1"></i> Filter
        </button>
        <a href="{{ route('admin.users') }}" class="bg-gray-100 text-gray-600 px-4 py-2 rounded-lg text-sm hover:bg-gray-200 transition">Reset</a>
    </form>
</div>

{{-- Header Row --}}
<div class="flex items-center justify-between mb-4">
    <p class="text-sm text-gray-500">{{ $users->total() }} users found</p>
    <a href="{{ route('admin.users.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-indigo-700 transition">
        <i class="fas fa-plus mr-1"></i> Create User
    </a>
</div>
@if(session('error'))
    <div class="mb-4 bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3 rounded-lg">
        <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
    </div>
@endif

{{-- Users Table --}}
<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-100">
            <tr>
                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">User</th>
                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Role</th>
                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Joined</th>
                <th class="text-right px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @forelse($users as $user)
            <tr class="hover:bg-gray-50 transition">
                <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-full flex items-center justify-center font-semibold text-sm text-white
                            {{ $user->role === 'admin' ? 'bg-indigo-500' : '' }}
                            {{ $user->role === 'agent' ? 'bg-blue-500' : '' }}
                            {{ $user->role === 'staff' ? 'bg-green-500' : '' }}
                            {{ $user->role === 'client' ? 'bg-purple-500' : '' }}">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                        <div>
                            <p class="font-medium text-gray-800">{{ $user->name }}</p>
                            <p class="text-xs text-gray-400">{{ $user->email }}</p>
                            @if($user->role === 'client')
                                @php $clientRecord = $user->client ?? \App\Models\Client::where('user_id', $user->id)->with('interestedProperty')->first(); @endphp
                                @if($clientRecord?->interestedProperty)
                                    <span class="inline-flex items-center gap-1 text-xs bg-indigo-100 text-indigo-700 px-2 py-0.5 rounded-full mt-1">
                                        <i class="fas fa-home text-xs"></i> {{ Str::limit($clientRecord->interestedProperty->title, 25) }}
                                    </span>
                                @endif
                            @endif
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4">
                    <form method="POST" action="{{ route('admin.users.update-role', $user) }}">
                        @csrf @method('PATCH')
                        <div class="flex items-center gap-2">
                            <select name="role" onchange="this.form.submit()" class="text-xs border border-gray-200 rounded-lg px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-indigo-500
                                {{ $user->role === 'admin' ? 'bg-indigo-50 text-indigo-700' : '' }}
                                {{ $user->role === 'agent' ? 'bg-blue-50 text-blue-700' : '' }}
                                {{ $user->role === 'staff' ? 'bg-green-50 text-green-700' : '' }}
                                {{ $user->role === 'client' ? 'bg-purple-50 text-purple-700' : '' }}">
                                <option value="admin"      {{ $user->role === 'admin'      ? 'selected' : '' }}>Admin</option>
                                <option value="agent"      {{ $user->role === 'agent'      ? 'selected' : '' }}>Agent</option>
                                <option value="finance"    {{ $user->role === 'finance'    ? 'selected' : '' }}>Finance</option>
                                <option value="staff"     {{ $user->role === 'staff'     ? 'selected' : '' }}>Staff</option>
                                <option value="client"     {{ $user->role === 'client'     ? 'selected' : '' }}>Client</option>
                            </select>
                        </div>
                    </form>
                </td>
                <td class="px-6 py-4">
                    <span class="text-xs px-2.5 py-1 rounded-full font-medium {{ $user->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                        {{ $user->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </td>
                <td class="px-6 py-4 text-xs text-gray-400">
                    {{ $user->created_at->format('M d, Y') }}
                </td>
                <td class="px-6 py-4">
                    <div class="flex items-center justify-end gap-2">
                        {{-- Toggle Status --}}
                        <form method="POST" action="{{ route('admin.users.toggle-status', $user) }}">
                            @csrf @method('PATCH')
                            <button type="submit" title="{{ $user->is_active ? 'Deactivate' : 'Activate' }}"
                                class="text-xs px-3 py-1.5 rounded-lg transition font-medium
                                {{ $user->is_active ? 'bg-red-50 text-red-600 hover:bg-red-100' : 'bg-green-50 text-green-600 hover:bg-green-100' }}">
                                {{ $user->is_active ? 'Deactivate' : 'Activate' }}
                            </button>
                        </form>

                        {{-- Reject (only for inactive clients pending approval) --}}
                        @if(!$user->is_active && $user->role === 'client')
                        <button type="button"
                            onclick="openRejectModal({{ $user->id }}, '{{ addslashes($user->name) }}', '{{ route('admin.users.reject', $user) }}')"
                            class="text-xs px-3 py-1.5 rounded-lg bg-orange-50 text-orange-600 hover:bg-orange-100 transition font-medium">
                            Reject
                        </button>
                        @endif

                        {{-- Delete --}}
                        @if($user->id !== auth()->id())
                        <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                            onsubmit="return confirm('Delete {{ $user->name }}? This cannot be undone.')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-xs px-3 py-1.5 rounded-lg bg-gray-50 text-gray-500 hover:bg-red-50 hover:text-red-600 transition">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="px-6 py-16 text-center text-gray-400">
                    <i class="fas fa-users text-4xl mb-3 block text-gray-200"></i>
                    No users found.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    @if($users->hasPages())
        <div class="px-6 py-4 border-t border-gray-100">
            {{ $users->links() }}
        </div>
    @endif
</div>

@endsection

@push('scripts')
{{-- Reject Modal --}}
<div id="rejectModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-md mx-4 p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-1">Reject Registration</h3>
        <p class="text-sm text-gray-500 mb-4">Rejecting <strong id="rejectUserName"></strong>. They will be notified by email and their account will be removed.</p>
        <form id="rejectForm" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Reason for Rejection <span class="text-gray-400">(optional)</span></label>
                <textarea name="reason" rows="3" placeholder="e.g. Incomplete information, duplicate account..." class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-400"></textarea>
            </div>
            <div class="flex justify-end gap-2">
                <button type="button" onclick="closeRejectModal()" class="px-4 py-2 text-sm rounded-lg bg-gray-100 text-gray-600 hover:bg-gray-200 transition">Cancel</button>
                <button type="submit" class="px-4 py-2 text-sm rounded-lg bg-red-600 text-white hover:bg-red-700 transition">Reject & Notify</button>
            </div>
        </form>
    </div>
</div>
<script>
    function openRejectModal(userId, userName, actionUrl) {
        document.getElementById('rejectUserName').textContent = userName;
        document.getElementById('rejectForm').action = actionUrl;
        document.getElementById('rejectModal').classList.remove('hidden');
        document.getElementById('rejectModal').classList.add('flex');
    }
    function closeRejectModal() {
        document.getElementById('rejectModal').classList.add('hidden');
        document.getElementById('rejectModal').classList.remove('flex');
    }
</script>
@endpush
