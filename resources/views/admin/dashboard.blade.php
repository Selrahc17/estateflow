@extends('layouts.app')

@section('title', 'Admin Dashboard - EstateFlow')
@section('page-title', 'Admin Dashboard')
@section('page-subtitle', 'Overview of all system activity')

@section('content')

{{-- Stats Row --}}
<div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-8">
    <div class="bg-white rounded-xl shadow-sm p-5 flex flex-col gap-1">
        <p class="text-xs text-gray-500">Total Users</p>
        <p class="text-2xl font-bold text-gray-800">{{ $totalUsers }}</p>
        @if($pendingUsers > 0)
            <a href="{{ route('admin.users', ['status' => 'inactive']) }}" class="text-xs text-yellow-600 font-medium">
                {{ $pendingUsers }} pending approval →
            </a>
        @endif
    </div>
    <div class="bg-white rounded-xl shadow-sm p-5 flex flex-col gap-1">
        <p class="text-xs text-gray-500">Properties</p>
        <p class="text-2xl font-bold text-gray-800">{{ $totalProperties }}</p>
        <p class="text-xs text-green-600">{{ $propertiesByStatus['available'] ?? 0 }} available</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-5 flex flex-col gap-1">
        <p class="text-xs text-gray-500">Active Projects</p>
        <p class="text-2xl font-bold text-gray-800">{{ $activeProjects }}</p>
        <a href="{{ route('projects.index') }}" class="text-xs text-indigo-600">View all →</a>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-5 flex flex-col gap-1">
        <p class="text-xs text-gray-500">Reservations</p>
        <p class="text-2xl font-bold text-gray-800">{{ $totalReservations }}</p>
        <a href="{{ route('reservations.index') }}" class="text-xs text-indigo-600">View all →</a>
    </div>
    <div class="col-span-2 bg-indigo-600 rounded-xl shadow-sm p-5 flex flex-col gap-1 text-white">
        <p class="text-xs text-indigo-200">Total Revenue</p>
        <p class="text-2xl font-bold">₱{{ number_format($totalRevenue, 0) }}</p>
        <a href="{{ route('payments.index') }}" class="text-xs text-indigo-200 hover:text-white">View payments →</a>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Pending Approvals --}}
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-semibold text-gray-800">
                Pending Approvals
                @if($pendingUsers > 0)
                    <span class="ml-2 bg-red-500 text-white text-xs rounded-full px-2 py-0.5">{{ $pendingUsers }}</span>
                @endif
            </h3>
            <a href="{{ route('admin.users', ['status' => 'inactive']) }}" class="text-sm text-indigo-600 hover:underline">View all</a>
        </div>
        <div class="space-y-3">
            @forelse($recentUsers as $u)
            <div class="flex items-center justify-between py-2 border-b border-gray-50 last:border-0">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center text-purple-600 font-semibold text-sm">
                        {{ strtoupper(substr($u->name, 0, 1)) }}
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-800">{{ $u->name }}</p>
                        <p class="text-xs text-gray-400">{{ $u->created_at->diffForHumans() }}</p>
                    </div>
                </div>
                <form method="POST" action="{{ route('admin.users.toggle-status', $u) }}">
                    @csrf @method('PATCH')
                    <button type="submit" class="text-xs px-2.5 py-1 bg-green-50 text-green-600 rounded-lg hover:bg-green-100 transition font-medium">
                        Activate
                    </button>
                </form>
            </div>
            @empty
            <div class="text-center py-4">
                <i class="fas fa-check-circle text-green-400 text-2xl mb-1 block"></i>
                <p class="text-sm text-gray-400">No pending approvals</p>
            </div>
            @endforelse
        </div>
        <div class="mt-4 pt-4 border-t border-gray-100">
            <a href="{{ route('admin.users.create') }}" class="block text-center bg-indigo-600 text-white py-2 rounded-lg text-sm hover:bg-indigo-700 transition">
                <i class="fas fa-plus mr-1"></i> Create User Account
            </a>
        </div>
    </div>

    {{-- Properties by Status --}}
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-semibold text-gray-800">Properties by Status</h3>
            <a href="{{ route('properties.index') }}" class="text-sm text-indigo-600 hover:underline">View all</a>
        </div>
        <div class="space-y-3">
            @foreach(['available' => ['green','Available'], 'reserved' => ['yellow','Reserved'], 'sold' => ['red','Sold'], 'under_construction' => ['blue','Under Construction']] as $status => [$color, $label])
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-{{ $color }}-500"></span>
                    <span class="text-sm text-gray-600">{{ $label }}</span>
                </div>
                <span class="font-semibold text-gray-800">{{ $propertiesByStatus[$status] ?? 0 }}</span>
            </div>
            @endforeach
        </div>
        <div class="mt-6 pt-4 border-t border-gray-100 grid grid-cols-2 gap-3">
            <a href="{{ route('properties.create') }}" class="bg-indigo-600 text-white text-sm text-center py-2 rounded-lg hover:bg-indigo-700 transition">
                <i class="fas fa-plus mr-1"></i> Add Property
            </a>
            <a href="{{ route('property-types.index') }}" class="bg-gray-100 text-gray-700 text-sm text-center py-2 rounded-lg hover:bg-gray-200 transition">
                <i class="fas fa-tags mr-1"></i> Types
            </a>
        </div>
    </div>

    {{-- Recent Reservations --}}
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-semibold text-gray-800">Recent Reservations</h3>
            <a href="{{ route('reservations.index') }}" class="text-sm text-indigo-600 hover:underline">View all</a>
        </div>
        <div class="space-y-3">
            @forelse($recentReservations as $res)
            <div class="py-2 border-b border-gray-50 last:border-0">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-medium text-gray-800 truncate max-w-xs">{{ $res->property->title ?? '—' }}</p>
                    <span class="text-xs px-2 py-0.5 rounded-full font-medium ml-2 flex-shrink-0
                        {{ $res->status === 'confirmed' ? 'bg-green-100 text-green-700'   : '' }}
                        {{ $res->status === 'pending'   ? 'bg-yellow-100 text-yellow-700' : '' }}
                        {{ $res->status === 'cancelled' ? 'bg-red-100 text-red-700'       : '' }}
                        {{ $res->status === 'completed' ? 'bg-blue-100 text-blue-700'     : '' }}">
                        {{ ucfirst($res->status) }}
                    </span>
                </div>
                <p class="text-xs text-gray-400 mt-0.5">{{ $res->client->full_name ?? '—' }} · {{ $res->reservation_date->format('M d, Y') }}</p>
            </div>
            @empty
            <p class="text-sm text-gray-400">No reservations yet.</p>
            @endforelse
        </div>
    </div>

    {{-- Recent Payments --}}
    <div class="bg-white rounded-xl shadow-sm p-6 lg:col-span-3">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-semibold text-gray-800">Recent Payments</h3>
            <a href="{{ route('payments.index') }}" class="text-sm text-indigo-600 hover:underline">View all</a>
        </div>
        <table class="w-full text-sm">
            <thead>
                <tr class="text-xs text-gray-500 border-b border-gray-100">
                    <th class="text-left pb-2">Client</th>
                    <th class="text-left pb-2">Type</th>
                    <th class="text-left pb-2">Method</th>
                    <th class="text-left pb-2">Amount</th>
                    <th class="text-left pb-2">Date</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($recentPayments as $payment)
                <tr class="hover:bg-gray-50">
                    <td class="py-2.5 text-gray-800">{{ $payment->client->full_name ?? '—' }}</td>
                    <td class="py-2.5 text-gray-600">{{ ucfirst(str_replace('_',' ',$payment->payment_type)) }}</td>
                    <td class="py-2.5 text-gray-600">{{ ucfirst(str_replace('_',' ',$payment->payment_method)) }}</td>
                    <td class="py-2.5 font-bold text-indigo-600">₱{{ number_format($payment->amount, 2) }}</td>
                    <td class="py-2.5 text-gray-400 text-xs">{{ $payment->payment_date->format('M d, Y') }}</td>
                </tr>
                @empty
                <tr><td colspan="5" class="py-4 text-center text-gray-400 text-sm">No payments yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>
@endsection
