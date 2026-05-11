@extends('layouts.app')

@section('title', 'Clients - EstateFlow')
@section('page-title', 'Clients')
@section('page-subtitle', 'Manage all clients')

@section('content')

{{-- Stats --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
    <div class="bg-white rounded-xl shadow-sm p-6 flex items-center gap-4">
        <div class="w-12 h-12 bg-indigo-100 rounded-xl flex items-center justify-center">
            <i class="fas fa-users text-indigo-600 text-xl"></i>
        </div>
        <div>
            <p class="text-sm text-gray-500">Total Clients</p>
            <p class="text-2xl font-bold text-gray-800">{{ $totalClients }}</p>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-6 flex items-center gap-4">
        <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
            <i class="fas fa-user-check text-green-600 text-xl"></i>
        </div>
        <div>
            <p class="text-sm text-gray-500">Active</p>
            <p class="text-2xl font-bold text-gray-800">{{ $activeClients }}</p>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-6 flex items-center gap-4">
        <div class="w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center">
            <i class="fas fa-user-times text-red-600 text-xl"></i>
        </div>
        <div>
            <p class="text-sm text-gray-500">Inactive</p>
            <p class="text-2xl font-bold text-gray-800">{{ $inactiveClients }}</p>
        </div>
    </div>
</div>

{{-- Filters --}}
<div class="bg-white rounded-xl shadow-sm p-5 mb-6">
    <form method="GET" action="{{ route('clients.index') }}" class="flex flex-wrap gap-3 items-end">
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Search</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Name, email or phone..."
                class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 w-64">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Status</label>
            <select name="status" class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">All Status</option>
                <option value="active"      {{ request('status') === 'active'      ? 'selected' : '' }}>Active</option>
                <option value="inactive"    {{ request('status') === 'inactive'    ? 'selected' : '' }}>Inactive</option>
                <option value="blacklisted" {{ request('status') === 'blacklisted' ? 'selected' : '' }}>Blacklisted</option>
            </select>
        </div>
        <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-indigo-700 transition">
            <i class="fas fa-search mr-1"></i> Filter
        </button>
        <a href="{{ route('clients.index') }}" class="bg-gray-100 text-gray-600 px-4 py-2 rounded-lg text-sm hover:bg-gray-200 transition">Reset</a>
    </form>
</div>

{{-- Header Row --}}
<div class="flex items-center justify-between mb-4">
    <p class="text-sm text-gray-500">{{ $clients->total() }} clients found</p>
    @if(auth()->user()->isAdmin() || auth()->user()->isAgent())
        <a href="{{ route('clients.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-indigo-700 transition">
            <i class="fas fa-plus mr-1"></i> Add Client
        </a>
    @endif
</div>

{{-- Table --}}
<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-100">
            <tr>
                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Client</th>
                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Contact</th>
                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">ID</th>
                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                <th class="text-right px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @forelse($clients as $client)
            <tr class="hover:bg-gray-50 transition">
                <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 bg-indigo-100 rounded-full flex items-center justify-center text-indigo-600 font-semibold text-sm">
                            {{ strtoupper(substr($client->first_name, 0, 1)) }}
                        </div>
                        <div>
                            <p class="font-medium text-gray-800">{{ $client->full_name }}</p>
                            <p class="text-xs text-gray-400">{{ $client->email ?? 'No email' }}</p>
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4">
                    <p class="text-gray-700">{{ $client->phone }}</p>
                    @if($client->phone_alt)
                        <p class="text-xs text-gray-400">{{ $client->phone_alt }}</p>
                    @endif
                </td>
                <td class="px-6 py-4">
                    @if($client->id_type)
                        <p class="text-gray-700">{{ $client->id_type }}</p>
                        <p class="text-xs text-gray-400">{{ $client->id_number }}</p>
                    @else
                        <span class="text-gray-300">—</span>
                    @endif
                </td>
                <td class="px-6 py-4">
                    <span class="text-xs px-2.5 py-1 rounded-full font-medium
                        {{ $client->status === 'active'      ? 'bg-green-100 text-green-700' : '' }}
                        {{ $client->status === 'inactive'    ? 'bg-gray-100 text-gray-600'   : '' }}
                        {{ $client->status === 'blacklisted' ? 'bg-red-100 text-red-700'     : '' }}">
                        {{ ucfirst($client->status) }}
                    </span>
                </td>
                <td class="px-6 py-4">
                    <div class="flex items-center justify-end gap-2">
                        <a href="{{ route('clients.show', $client) }}" class="text-xs px-3 py-1.5 bg-indigo-50 text-indigo-600 rounded-lg hover:bg-indigo-100 transition">View</a>
                        @if(auth()->user()->isAdmin() || auth()->user()->isAgent())
                            <a href="{{ route('clients.edit', $client) }}" class="text-xs px-3 py-1.5 bg-gray-50 text-gray-600 rounded-lg hover:bg-gray-100 transition">Edit</a>
                            <form method="POST" action="{{ route('clients.destroy', $client) }}"
                                onsubmit="return confirm('Delete {{ $client->full_name }}?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-xs px-3 py-1.5 bg-gray-50 text-gray-500 rounded-lg hover:bg-red-50 hover:text-red-600 transition">
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
                    No clients found.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    @if($clients->hasPages())
        <div class="px-6 py-4 border-t border-gray-100">{{ $clients->links() }}</div>
    @endif
</div>

@endsection
