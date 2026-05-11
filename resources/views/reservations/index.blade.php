@extends('layouts.app')

@section('title', 'Reservations - EstateFlow')
@section('page-title', 'Reservations')
@section('page-subtitle', 'Manage all property reservations')

@section('content')

{{-- Stats --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-6 mb-6">
    <div class="bg-white rounded-xl shadow-sm p-6 flex items-center gap-4">
        <div class="w-12 h-12 bg-indigo-100 rounded-xl flex items-center justify-center">
            <i class="fas fa-calendar-alt text-indigo-600 text-xl"></i>
        </div>
        <div>
            <p class="text-sm text-gray-500">Total</p>
            <p class="text-2xl font-bold text-gray-800">{{ $totalReservations }}</p>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-6 flex items-center gap-4">
        <div class="w-12 h-12 bg-yellow-100 rounded-xl flex items-center justify-center">
            <i class="fas fa-clock text-yellow-600 text-xl"></i>
        </div>
        <div>
            <p class="text-sm text-gray-500">Pending</p>
            <p class="text-2xl font-bold text-gray-800">{{ $pendingCount }}</p>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-6 flex items-center gap-4">
        <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
            <i class="fas fa-check-circle text-green-600 text-xl"></i>
        </div>
        <div>
            <p class="text-sm text-gray-500">Confirmed</p>
            <p class="text-2xl font-bold text-gray-800">{{ $confirmedCount }}</p>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-6 flex items-center gap-4">
        <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
            <i class="fas fa-flag-checkered text-blue-600 text-xl"></i>
        </div>
        <div>
            <p class="text-sm text-gray-500">Completed</p>
            <p class="text-2xl font-bold text-gray-800">{{ $completedCount }}</p>
        </div>
    </div>
</div>

{{-- Filters --}}
<div class="bg-white rounded-xl shadow-sm p-5 mb-6">
    <form method="GET" action="{{ route('reservations.index') }}" class="flex flex-wrap gap-3 items-end">
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Search</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Client or property..."
                class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 w-64">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Status</label>
            <select name="status" class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">All Status</option>
                <option value="pending"   {{ request('status') === 'pending'   ? 'selected' : '' }}>Pending</option>
                <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                <option value="expired"   {{ request('status') === 'expired'   ? 'selected' : '' }}>Expired</option>
                <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
            </select>
        </div>
        <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-indigo-700 transition">
            <i class="fas fa-search mr-1"></i> Filter
        </button>
        <a href="{{ route('reservations.index') }}" class="bg-gray-100 text-gray-600 px-4 py-2 rounded-lg text-sm hover:bg-gray-200 transition">Reset</a>
    </form>
</div>

{{-- Header Row --}}
<div class="flex items-center justify-between mb-4">
    <p class="text-sm text-gray-500">{{ $reservations->total() }} reservations found</p>
    @if(auth()->user()->isAdmin() || auth()->user()->isAgent())
        <a href="{{ route('reservations.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-indigo-700 transition">
            <i class="fas fa-plus mr-1"></i> New Reservation
        </a>
    @endif
</div>

{{-- Table --}}
<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-100">
            <tr>
                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Property</th>
                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Client</th>
                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Agent</th>
                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Date</th>
                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Fee</th>
                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                <th class="text-right px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @forelse($reservations as $reservation)
            <tr class="hover:bg-gray-50 transition">
                <td class="px-6 py-4">
                    <p class="font-medium text-gray-800">{{ $reservation->property->title ?? '—' }}</p>
                    <p class="text-xs text-gray-400">{{ $reservation->property->location ?? '' }}</p>
                </td>
                <td class="px-6 py-4">
                    <p class="text-gray-700">{{ $reservation->client->full_name ?? '—' }}</p>
                    <p class="text-xs text-gray-400">{{ $reservation->client->phone ?? '' }}</p>
                </td>
                <td class="px-6 py-4 text-gray-700">{{ $reservation->agent->full_name ?? '—' }}</td>
                <td class="px-6 py-4">
                    <p class="text-gray-700">{{ $reservation->reservation_date->format('M d, Y') }}</p>
                    @if($reservation->expiry_date)
                        <p class="text-xs text-gray-400">Exp: {{ $reservation->expiry_date->format('M d, Y') }}</p>
                    @endif
                </td>
                <td class="px-6 py-4 font-medium text-gray-800">₱{{ number_format($reservation->reservation_fee, 2) }}</td>
                <td class="px-6 py-4">
                    @if(auth()->user()->isAdmin() || auth()->user()->isAgent())
                    <form method="POST" action="{{ route('reservations.update-status', $reservation) }}">
                        @csrf
                        <input type="hidden" name="_method" value="PATCH">
                        <select name="status" onchange="this.form.submit()"
                            class="text-xs border rounded-lg px-2 py-1.5 font-medium focus:outline-none focus:ring-2 focus:ring-indigo-500 cursor-pointer
                            {{ $reservation->status === 'pending'   ? 'bg-yellow-50 text-yellow-700 border-yellow-200' : '' }}
                            {{ $reservation->status === 'confirmed' ? 'bg-green-50 text-green-700 border-green-200'   : '' }}
                            {{ $reservation->status === 'cancelled' ? 'bg-red-50 text-red-700 border-red-200'         : '' }}
                            {{ $reservation->status === 'expired'   ? 'bg-gray-50 text-gray-600 border-gray-200'      : '' }}
                            {{ $reservation->status === 'completed' ? 'bg-blue-50 text-blue-700 border-blue-200'      : '' }}">
                            <option value="pending"   {{ $reservation->status === 'pending'   ? 'selected' : '' }}>Pending</option>
                            <option value="confirmed" {{ $reservation->status === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                            <option value="cancelled" {{ $reservation->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            <option value="expired"   {{ $reservation->status === 'expired'   ? 'selected' : '' }}>Expired</option>
                            <option value="completed" {{ $reservation->status === 'completed' ? 'selected' : '' }}>Completed</option>
                        </select>
                    </form>
                    @else
                    <span class="text-xs px-2.5 py-1 rounded-full font-medium
                        {{ $reservation->status === 'pending'   ? 'bg-yellow-100 text-yellow-700' : '' }}
                        {{ $reservation->status === 'confirmed' ? 'bg-green-100 text-green-700'   : '' }}
                        {{ $reservation->status === 'cancelled' ? 'bg-red-100 text-red-700'       : '' }}
                        {{ $reservation->status === 'expired'   ? 'bg-gray-100 text-gray-600'     : '' }}
                        {{ $reservation->status === 'completed' ? 'bg-blue-100 text-blue-700'     : '' }}">
                        {{ ucfirst($reservation->status) }}
                    </span>
                    @endif
                </td>
                <td class="px-6 py-4">
                    <div class="flex items-center justify-end gap-2">
                        <a href="{{ route('reservations.show', $reservation) }}" class="text-xs px-3 py-1.5 bg-indigo-50 text-indigo-600 rounded-lg hover:bg-indigo-100 transition">View</a>
                        @if(auth()->user()->isAdmin())
                            <a href="{{ route('reservations.edit', $reservation) }}" class="text-xs px-3 py-1.5 bg-gray-50 text-gray-600 rounded-lg hover:bg-gray-100 transition">Edit</a>
                            <form method="POST" action="{{ route('reservations.destroy', $reservation) }}"
                                onsubmit="return confirm('Delete this reservation?')">
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
                <td colspan="7" class="px-6 py-16 text-center text-gray-400">
                    <i class="fas fa-calendar-alt text-4xl mb-3 block text-gray-200"></i>
                    No reservations found.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    @if($reservations->hasPages())
        <div class="px-6 py-4 border-t border-gray-100">{{ $reservations->links() }}</div>
    @endif
</div>

@endsection
