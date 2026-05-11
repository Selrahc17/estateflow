@extends('layouts.app')

@section('title', 'Agents - EstateFlow')
@section('page-title', 'Agents')
@section('page-subtitle', 'Manage all agents')

@section('content')

{{-- Stats --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
    <div class="bg-white rounded-xl shadow-sm p-6 flex items-center gap-4">
        <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
            <i class="fas fa-user-tie text-blue-600 text-xl"></i>
        </div>
        <div>
            <p class="text-sm text-gray-500">Total Agents</p>
            <p class="text-2xl font-bold text-gray-800">{{ $totalAgents }}</p>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-6 flex items-center gap-4">
        <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
            <i class="fas fa-user-check text-green-600 text-xl"></i>
        </div>
        <div>
            <p class="text-sm text-gray-500">Active</p>
            <p class="text-2xl font-bold text-gray-800">{{ $activeAgents }}</p>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-6 flex items-center gap-4">
        <div class="w-12 h-12 bg-yellow-100 rounded-xl flex items-center justify-center">
            <i class="fas fa-user-clock text-yellow-600 text-xl"></i>
        </div>
        <div>
            <p class="text-sm text-gray-500">Suspended</p>
            <p class="text-2xl font-bold text-gray-800">{{ $suspendedAgents }}</p>
        </div>
    </div>
</div>

{{-- Filters --}}
<div class="bg-white rounded-xl shadow-sm p-5 mb-6">
    <form method="GET" action="{{ route('agents.index') }}" class="flex flex-wrap gap-3 items-end">
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Search</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Name, email, license..."
                class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 w-64">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Status</label>
            <select name="status" class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">All Status</option>
                <option value="active"    {{ request('status') === 'active'    ? 'selected' : '' }}>Active</option>
                <option value="inactive"  {{ request('status') === 'inactive'  ? 'selected' : '' }}>Inactive</option>
                <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Suspended</option>
            </select>
        </div>
        <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-indigo-700 transition">
            <i class="fas fa-search mr-1"></i> Filter
        </button>
        <a href="{{ route('agents.index') }}" class="bg-gray-100 text-gray-600 px-4 py-2 rounded-lg text-sm hover:bg-gray-200 transition">Reset</a>
    </form>
</div>

{{-- Header Row --}}
<div class="flex items-center justify-between mb-4">
    <p class="text-sm text-gray-500">{{ $agents->total() }} agents found</p>
    @if(auth()->user()->isAdmin())
        <a href="{{ route('agents.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-indigo-700 transition">
            <i class="fas fa-plus mr-1"></i> Add Agent
        </a>
    @endif
</div>

{{-- Table --}}
<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-100">
            <tr>
                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Agent</th>
                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Contact</th>
                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">License</th>
                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Commission</th>
                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                <th class="text-right px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @forelse($agents as $agent)
            <tr class="hover:bg-gray-50 transition">
                <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 bg-blue-100 rounded-full flex items-center justify-center text-blue-600 font-semibold text-sm">
                            {{ strtoupper(substr($agent->first_name, 0, 1)) }}
                        </div>
                        <div>
                            <p class="font-medium text-gray-800">{{ $agent->full_name }}</p>
                            <p class="text-xs text-gray-400">{{ $agent->email }}</p>
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4 text-gray-700">{{ $agent->phone }}</td>
                <td class="px-6 py-4 text-gray-700">{{ $agent->license_number ?? '—' }}</td>
                <td class="px-6 py-4 text-gray-700">{{ $agent->commission_rate }}%</td>
                <td class="px-6 py-4">
                    <span class="text-xs px-2.5 py-1 rounded-full font-medium
                        {{ $agent->status === 'active'    ? 'bg-green-100 text-green-700'  : '' }}
                        {{ $agent->status === 'inactive'  ? 'bg-gray-100 text-gray-600'    : '' }}
                        {{ $agent->status === 'suspended' ? 'bg-yellow-100 text-yellow-700' : '' }}">
                        {{ ucfirst($agent->status) }}
                    </span>
                </td>
                <td class="px-6 py-4">
                    <div class="flex items-center justify-end gap-2">
                        <a href="{{ route('agents.show', $agent) }}" class="text-xs px-3 py-1.5 bg-indigo-50 text-indigo-600 rounded-lg hover:bg-indigo-100 transition">View</a>
                        @if(auth()->user()->isAdmin())
                            <a href="{{ route('agents.edit', $agent) }}" class="text-xs px-3 py-1.5 bg-gray-50 text-gray-600 rounded-lg hover:bg-gray-100 transition">Edit</a>
                            <form method="POST" action="{{ route('agents.destroy', $agent) }}"
                                onsubmit="return confirm('Delete {{ $agent->full_name }}?')">
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
                <td colspan="6" class="px-6 py-16 text-center text-gray-400">
                    <i class="fas fa-user-tie text-4xl mb-3 block text-gray-200"></i>
                    No agents found.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    @if($agents->hasPages())
        <div class="px-6 py-4 border-t border-gray-100">{{ $agents->links() }}</div>
    @endif
</div>

@endsection
