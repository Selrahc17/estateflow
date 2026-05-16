@extends('layouts.app')

@section('title', '{{ $agent->full_name }} - EstateFlow')
@section('page-title', '{{ $agent->full_name }}')
@section('page-subtitle', 'Agent Details')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Left: Agent Info --}}
    <div class="lg:col-span-1 space-y-6">
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center gap-4 mb-6">
                <div class="w-14 h-14 bg-blue-100 rounded-full flex items-center justify-center text-blue-600 font-bold text-xl">
                    {{ strtoupper(substr($agent->first_name, 0, 1)) }}
                </div>
                <div>
                    <h2 class="font-bold text-gray-800 text-lg">{{ $agent->full_name }}</h2>
                    <span class="font-mono text-xs bg-indigo-100 text-indigo-700 px-2 py-0.5 rounded-full font-semibold">{{ $agent->agent_code }}</span>
                    <span class="ml-1 text-xs px-2 py-0.5 rounded-full font-medium
                        {{ $agent->status === 'active'    ? 'bg-green-100 text-green-700'   : '' }}
                        {{ $agent->status === 'inactive'  ? 'bg-gray-100 text-gray-600'     : '' }}
                        {{ $agent->status === 'suspended' ? 'bg-yellow-100 text-yellow-700' : '' }}">
                        {{ ucfirst($agent->status) }}
                    </span>
                </div>
            </div>

            <div class="space-y-3 text-sm">
                <div class="flex items-center gap-3">
                    <i class="fas fa-envelope text-gray-400 w-4"></i>
                    <span class="text-gray-700">{{ $agent->email }}</span>
                </div>
                <div class="flex items-center gap-3">
                    <i class="fas fa-phone text-gray-400 w-4"></i>
                    <span class="text-gray-700">{{ $agent->phone }}</span>
                </div>
                @if($agent->license_number)
                <div class="flex items-center gap-3">
                    <i class="fas fa-id-card text-gray-400 w-4"></i>
                    <span class="text-gray-700">{{ $agent->license_number }}</span>
                </div>
                @endif
                <div class="flex items-center gap-3">
                    <i class="fas fa-percent text-gray-400 w-4"></i>
                    <span class="text-gray-700">{{ $agent->commission_rate }}% commission</span>
                </div>
                @if($agent->address)
                <div class="flex items-start gap-3">
                    <i class="fas fa-map-marker-alt text-gray-400 mt-0.5 w-4"></i>
                    <span class="text-gray-700">{{ $agent->address }}</span>
                </div>
                @endif
            </div>

            @if(auth()->user()->isAdmin())
            <div class="mt-6 flex gap-2">
                <a href="{{ route('agents.edit', $agent) }}" class="flex-1 text-center bg-indigo-600 text-white py-2 rounded-lg text-sm hover:bg-indigo-700 transition">
                    <i class="fas fa-edit mr-1"></i> Edit
                </a>
                <form method="POST" action="{{ route('agents.destroy', $agent) }}"
                    onsubmit="return confirm('Delete {{ $agent->full_name }}?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="px-4 py-2 bg-red-50 text-red-600 rounded-lg text-sm hover:bg-red-100 transition">
                        <i class="fas fa-trash"></i>
                    </button>
                </form>
            </div>
            @endif
        </div>

        @if($agent->notes)
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="font-semibold text-gray-800 mb-2 text-sm">Notes</h3>
            <p class="text-sm text-gray-600">{{ $agent->notes }}</p>
        </div>
        @endif
    </div>

    {{-- Right: Reservations & Payments --}}
    <div class="lg:col-span-2 space-y-6">

        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="font-semibold text-gray-800 mb-4">Reservations ({{ $agent->reservations->count() }})</h3>
            @forelse($agent->reservations as $reservation)
            <div class="flex items-center justify-between py-3 border-b border-gray-50 last:border-0">
                <div>
                    <p class="text-sm font-medium text-gray-800">{{ $reservation->property->title ?? 'N/A' }}</p>
                    <p class="text-xs text-gray-400">{{ $reservation->reservation_date }} · {{ $reservation->client->full_name ?? '' }}</p>
                </div>
                <span class="text-xs px-2.5 py-1 rounded-full font-medium
                    {{ $reservation->status === 'confirmed' ? 'bg-green-100 text-green-700'  : '' }}
                    {{ $reservation->status === 'pending'   ? 'bg-yellow-100 text-yellow-700' : '' }}
                    {{ $reservation->status === 'cancelled' ? 'bg-red-100 text-red-700'      : '' }}
                    {{ $reservation->status === 'completed' ? 'bg-blue-100 text-blue-700'    : '' }}">
                    {{ ucfirst($reservation->status) }}
                </span>
            </div>
            @empty
            <p class="text-sm text-gray-400">No reservations yet.</p>
            @endforelse
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="font-semibold text-gray-800 mb-4">Payments ({{ $agent->payments->count() }})</h3>
            @forelse($agent->payments as $payment)
            <div class="flex items-center justify-between py-3 border-b border-gray-50 last:border-0">
                <div>
                    <p class="text-sm font-medium text-gray-800">{{ ucfirst($payment->payment_type) }}</p>
                    <p class="text-xs text-gray-400">{{ $payment->payment_date }} · {{ ucfirst($payment->payment_method) }}</p>
                </div>
                <div class="text-right">
                    <p class="text-sm font-bold text-gray-800">₱{{ number_format($payment->amount, 2) }}</p>
                    <span class="text-xs px-2 py-0.5 rounded-full
                        {{ $payment->status === 'completed' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                        {{ ucfirst($payment->status) }}
                    </span>
                </div>
            </div>
            @empty
            <p class="text-sm text-gray-400">No payments yet.</p>
            @endforelse
        </div>

    </div>
</div>
@endsection
