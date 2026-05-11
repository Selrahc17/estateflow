@extends('layouts.app')

@section('title', 'Staff - EstateFlow')
@section('page-title', 'Staff')
@section('page-subtitle', 'Manage all staff members')

@section('content')

{{-- Stats --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
    <div class="bg-white rounded-xl shadow-sm p-6 flex items-center gap-4">
        <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
            <i class="fas fa-hard-hat text-green-600 text-xl"></i>
        </div>
        <div>
            <p class="text-sm text-gray-500">Total Staff</p>
            <p class="text-2xl font-bold text-gray-800">{{ $totalContractors }}</p>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-6 flex items-center gap-4">
        <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
            <i class="fas fa-check-circle text-blue-600 text-xl"></i>
        </div>
        <div>
            <p class="text-sm text-gray-500">Active</p>
            <p class="text-2xl font-bold text-gray-800">{{ $activeCount }}</p>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-6 flex items-center gap-4">
        <div class="w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center">
            <i class="fas fa-times-circle text-red-600 text-xl"></i>
        </div>
        <div>
            <p class="text-sm text-gray-500">Inactive</p>
            <p class="text-2xl font-bold text-gray-800">{{ $inactiveCount }}</p>
        </div>
    </div>
</div>

{{-- Filters --}}
<div class="bg-white rounded-xl shadow-sm p-5 mb-6">
    <form method="GET" action="{{ route('contractors.index') }}" class="flex flex-wrap gap-3 items-end">
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Search</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Company, contact, email..."
                class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 w-64">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Type</label>
            <select name="type" class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">All Types</option>
                <option value="general_contractor" {{ request('type') === 'general_contractor' ? 'selected' : '' }}>General Contractor</option>
                <option value="subcontractor"      {{ request('type') === 'subcontractor'      ? 'selected' : '' }}>Subcontractor</option>
                <option value="supplier"           {{ request('type') === 'supplier'           ? 'selected' : '' }}>Supplier</option>
                <option value="consultant"         {{ request('type') === 'consultant'         ? 'selected' : '' }}>Consultant</option>
            </select>
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
        <a href="{{ route('contractors.index') }}" class="bg-gray-100 text-gray-600 px-4 py-2 rounded-lg text-sm hover:bg-gray-200 transition">Reset</a>
    </form>
</div>

{{-- Header Row --}}
<div class="flex items-center justify-between mb-4">
    <p class="text-sm text-gray-500">{{ $contractors->total() }} staff found</p>
    @if(auth()->user()->isAdmin())
        <a href="{{ route('contractors.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-indigo-700 transition">
            <i class="fas fa-plus mr-1"></i> Add Staff
        </a>
    @endif
</div>

{{-- Table --}}
<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-100">
            <tr>
                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Company</th>
                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Contact</th>
                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Type</th>
                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Specialization</th>
                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                <th class="text-right px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @forelse($contractors as $contractor)
            <tr class="hover:bg-gray-50 transition">
                <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 bg-green-100 rounded-full flex items-center justify-center text-green-600 font-semibold text-sm">
                            {{ strtoupper(substr($contractor->company_name, 0, 1)) }}
                        </div>
                        <div>
                            <p class="font-medium text-gray-800">{{ $contractor->company_name }}</p>
                            <p class="text-xs text-gray-400">{{ $contractor->email ?? 'No email' }}</p>
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4">
                    <p class="text-gray-700">{{ $contractor->contact_person }}</p>
                    <p class="text-xs text-gray-400">{{ $contractor->phone }}</p>
                </td>
                <td class="px-6 py-4">
                    <span class="text-xs px-2 py-1 bg-blue-50 text-blue-700 rounded-full">
                        {{ ucfirst(str_replace('_', ' ', $contractor->type)) }}
                    </span>
                </td>
                <td class="px-6 py-4 text-gray-600 text-xs">{{ $contractor->specialization ?? '—' }}</td>
                <td class="px-6 py-4">
                    <span class="text-xs px-2.5 py-1 rounded-full font-medium
                        {{ $contractor->status === 'active'      ? 'bg-green-100 text-green-700' : '' }}
                        {{ $contractor->status === 'inactive'    ? 'bg-gray-100 text-gray-600'   : '' }}
                        {{ $contractor->status === 'blacklisted' ? 'bg-red-100 text-red-700'     : '' }}">
                        {{ ucfirst($contractor->status) }}
                    </span>
                </td>
                <td class="px-6 py-4">
                    <div class="flex items-center justify-end gap-2">
                        <a href="{{ route('contractors.show', $contractor) }}" class="text-xs px-3 py-1.5 bg-indigo-50 text-indigo-600 rounded-lg hover:bg-indigo-100 transition">View</a>
                        @if(auth()->user()->isAdmin())
                            <a href="{{ route('contractors.edit', $contractor) }}" class="text-xs px-3 py-1.5 bg-gray-50 text-gray-600 rounded-lg hover:bg-gray-100 transition">Edit</a>
                            <form method="POST" action="{{ route('contractors.destroy', $contractor) }}"
                                onsubmit="return confirm('Delete {{ $contractor->company_name }}?')">
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
                    <i class="fas fa-hard-hat text-4xl mb-3 block text-gray-200"></i>
                    No staff found.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    @if($contractors->hasPages())
        <div class="px-6 py-4 border-t border-gray-100">{{ $contractors->links() }}</div>
    @endif
</div>

@endsection
