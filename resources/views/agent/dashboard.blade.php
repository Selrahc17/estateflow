@extends('layouts.app')

@section('title', 'Agent Dashboard - EstateFlow')
@section('page-title', 'Agent Dashboard')
@section('page-subtitle', 'Welcome back, ' . auth()->user()->name)

@section('content')

@if(!$agentRecord)
<div class="mb-6 bg-yellow-50 border border-yellow-200 rounded-xl p-4 flex items-start gap-3">
    <i class="fas fa-exclamation-triangle text-yellow-500 mt-0.5"></i>
    <div>
        <p class="text-sm font-medium text-yellow-800">Your account is not linked to an agent profile yet.</p>
        <p class="text-xs text-yellow-600 mt-1">Please contact an administrator to complete your setup.</p>
    </div>
</div>
@endif

{{-- Stats --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-6 mb-8">
    <div class="bg-white rounded-xl shadow-sm p-6 flex items-center gap-4">
        <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
            <i class="fas fa-calendar-alt text-blue-600 text-xl"></i>
        </div>
        <div>
            <p class="text-sm text-gray-500">My Reservations</p>
            <p class="text-2xl font-bold text-gray-800">{{ $myReservations }}</p>
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
            <i class="fas fa-users text-green-600 text-xl"></i>
        </div>
        <div>
            <p class="text-sm text-gray-500">My Clients</p>
            <p class="text-2xl font-bold text-gray-800">{{ $myClients }}</p>
        </div>
    </div>
    <div class="bg-indigo-600 rounded-xl shadow-sm p-6 flex items-center gap-4">
        <div class="w-12 h-12 bg-indigo-500 rounded-xl flex items-center justify-center">
            <i class="fas fa-percent text-white text-xl"></i>
        </div>
        <div>
            <p class="text-sm text-indigo-200">Commission Earned</p>
            <p class="text-lg font-bold text-white">₱{{ number_format($myCommission, 0) }}</p>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Recent Reservations --}}
    <div class="lg:col-span-2 bg-white rounded-xl shadow-sm p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-semibold text-gray-800">My Recent Reservations</h3>
            <a href="{{ route('agent.reservations') }}" class="text-sm text-indigo-600 hover:underline">View all</a>
        </div>

        @forelse($recentReservations as $res)
        <div class="flex items-center justify-between py-3 border-b border-gray-50 last:border-0">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-indigo-50 rounded-lg flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-building text-indigo-400 text-sm"></i>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-800">{{ $res->property->title ?? '—' }}</p>
                    <p class="text-xs text-gray-400">{{ $res->client->full_name ?? '—' }} · {{ $res->reservation_date->format('M d, Y') }}</p>
                </div>
            </div>
            <span class="text-xs px-2.5 py-1 rounded-full font-medium flex-shrink-0
                {{ $res->status === 'confirmed' ? 'bg-green-100 text-green-700'   : '' }}
                {{ $res->status === 'pending'   ? 'bg-yellow-100 text-yellow-700' : '' }}
                {{ $res->status === 'cancelled' ? 'bg-red-100 text-red-700'       : '' }}
                {{ $res->status === 'completed' ? 'bg-blue-100 text-blue-700'     : '' }}">
                {{ ucfirst($res->status) }}
            </span>
        </div>
        @empty
        <div class="text-center py-8 text-gray-400">
            <i class="fas fa-calendar-check text-3xl mb-2 block text-gray-200"></i>
            <p class="text-sm">No reservations assigned yet.</p>
        </div>
        @endforelse
    </div>

    {{-- Quick Actions + Agent Info --}}
    <div class="space-y-4">
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="font-semibold text-gray-800 mb-4">Quick Actions</h3>
            <div class="space-y-2">
                <a href="{{ route('agent.reservations') }}"
                    class="flex items-center gap-3 p-3 bg-blue-50 rounded-xl hover:bg-blue-100 transition">
                    <i class="fas fa-calendar-check text-blue-600 w-5 text-center"></i>
                    <span class="text-sm font-medium text-blue-700">My Reservations</span>
                    @if($pendingCount > 0)
                        <span class="ml-auto bg-yellow-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">{{ $pendingCount }}</span>
                    @endif
                </a>
                <a href="{{ route('properties.index') }}"
                    class="flex items-center gap-3 p-3 bg-green-50 rounded-xl hover:bg-green-100 transition">
                    <i class="fas fa-building text-green-600 w-5 text-center"></i>
                    <span class="text-sm font-medium text-green-700">Browse Properties</span>
                </a>
                <a href="{{ route('properties.create') }}"
                    class="flex items-center gap-3 p-3 bg-indigo-50 rounded-xl hover:bg-indigo-100 transition">
                    <i class="fas fa-plus-circle text-indigo-600 w-5 text-center"></i>
                    <span class="text-sm font-medium text-indigo-700">Add Property</span>
                </a>
                <a href="{{ route('clients.index') }}"
                    class="flex items-center gap-3 p-3 bg-purple-50 rounded-xl hover:bg-purple-100 transition">
                    <i class="fas fa-users text-purple-600 w-5 text-center"></i>
                    <span class="text-sm font-medium text-purple-700">Clients</span>
                </a>
                <a href="{{ route('messages.index') }}"
                    class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl hover:bg-gray-100 transition">
                    <i class="fas fa-comments text-gray-600 w-5 text-center"></i>
                    <span class="text-sm font-medium text-gray-700">Messages</span>
                </a>
                <a href="{{ route('pipeline.index') }}"
                    class="flex items-center gap-3 p-3 bg-indigo-50 rounded-xl hover:bg-indigo-100 transition">
                    <i class="fas fa-stream text-indigo-600 w-5 text-center"></i>
                    <span class="text-sm font-medium text-indigo-700">Sales Pipeline</span>
                </a>
            </div>
        </div>

        {{-- Agent Profile Card --}}
        @if($agentRecord)
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="font-semibold text-gray-800 mb-3 text-sm">My Profile</h3>
            <div class="space-y-2 text-sm">
                <div class="flex items-center gap-2 text-gray-600">
                    <i class="fas fa-id-card text-gray-400 w-4"></i>
                    {{ $agentRecord->license_number ?? 'No license on file' }}
                </div>
                <div class="flex items-center gap-2 text-gray-600">
                    <i class="fas fa-phone text-gray-400 w-4"></i>
                    {{ $agentRecord->phone }}
                </div>
                <div class="flex items-center gap-2 text-gray-600">
                    <i class="fas fa-percent text-gray-400 w-4"></i>
                    {{ $agentRecord->commission_rate }}% commission rate
                </div>
                <span class="inline-block text-xs px-2 py-0.5 rounded-full mt-1
                    {{ $agentRecord->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                    {{ ucfirst($agentRecord->status) }}
                </span>
            </div>
        </div>
        @endif
    </div>

</div>
@endsection
