@extends('layouts.app')

@section('title', '{{ $client->full_name }} - EstateFlow')
@section('page-title', '{{ $client->full_name }}')
@section('page-subtitle', 'Client Details')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Left: Client Info --}}
    <div class="lg:col-span-1 space-y-6">
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center gap-4 mb-6">
                <div class="w-14 h-14 bg-indigo-100 rounded-full flex items-center justify-center text-indigo-600 font-bold text-xl">
                    {{ strtoupper(substr($client->first_name, 0, 1)) }}
                </div>
                <div>
                    <h2 class="font-bold text-gray-800 text-lg">{{ $client->full_name }}</h2>
                    <span class="text-xs px-2 py-0.5 rounded-full font-medium
                        {{ $client->status === 'active'      ? 'bg-green-100 text-green-700' : '' }}
                        {{ $client->status === 'inactive'    ? 'bg-gray-100 text-gray-600'   : '' }}
                        {{ $client->status === 'blacklisted' ? 'bg-red-100 text-red-700'     : '' }}">
                        {{ ucfirst($client->status) }}
                    </span>
                </div>
            </div>

            <div class="space-y-3 text-sm">
                <div class="flex items-start gap-3">
                    <i class="fas fa-envelope text-gray-400 mt-0.5 w-4"></i>
                    <span class="text-gray-700">{{ $client->email ?? '—' }}</span>
                </div>
                <div class="flex items-start gap-3">
                    <i class="fas fa-phone text-gray-400 mt-0.5 w-4"></i>
                    <div>
                        <p class="text-gray-700">{{ $client->phone }}</p>
                        @if($client->phone_alt)
                            <p class="text-gray-400 text-xs">{{ $client->phone_alt }}</p>
                        @endif
                    </div>
                </div>
                @if($client->address)
                <div class="flex items-start gap-3">
                    <i class="fas fa-map-marker-alt text-gray-400 mt-0.5 w-4"></i>
                    <span class="text-gray-700">{{ $client->address }}</span>
                </div>
                @endif
            </div>

            @if(auth()->user()->isAdmin() || auth()->user()->isAgent())
            <div class="mt-6 flex gap-2">
                <a href="{{ route('clients.edit', $client) }}" class="flex-1 text-center bg-indigo-600 text-white py-2 rounded-lg text-sm hover:bg-indigo-700 transition">
                    <i class="fas fa-edit mr-1"></i> Edit
                </a>
                <form method="POST" action="{{ route('clients.destroy', $client) }}"
                    onsubmit="return confirm('Delete {{ $client->full_name }}?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="px-4 py-2 bg-red-50 text-red-600 rounded-lg text-sm hover:bg-red-100 transition">
                        <i class="fas fa-trash"></i>
                    </button>
                </form>
            </div>
            @endif
        </div>

        {{-- ID Info --}}
        @if($client->id_type)
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="font-semibold text-gray-800 mb-4 text-sm">Identification</h3>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-500">Type</span>
                    <span class="text-gray-800 font-medium">{{ $client->id_type }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Number</span>
                    <span class="text-gray-800 font-medium">{{ $client->id_number }}</span>
                </div>
                @if($client->id_expiry)
                <div class="flex justify-between">
                    <span class="text-gray-500">Expiry</span>
                    <span class="text-gray-800 font-medium">{{ $client->id_expiry->format('M d, Y') }}</span>
                </div>
                @endif
            </div>
        </div>
        @endif

        {{-- Notes --}}
        @if($client->notes)
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="font-semibold text-gray-800 mb-2 text-sm">Notes</h3>
            <p class="text-sm text-gray-600">{{ $client->notes }}</p>
        </div>
        @endif

        {{-- Purchase Intent --}}
        @if($client->interestedProperty)
        <div class="bg-indigo-50 border border-indigo-200 rounded-xl p-5">
            <h3 class="font-semibold text-indigo-800 mb-3 text-sm flex items-center gap-2">
                <i class="fas fa-home text-indigo-500"></i> Purchase Intent
            </h3>
            <div class="flex items-center gap-3 mb-3">
                @if($client->interestedProperty->image_main)
                    <img src="{{ asset($client->interestedProperty->image_main) }}" class="w-14 h-14 rounded-lg object-cover flex-shrink-0">
                @else
                    <div class="w-14 h-14 bg-indigo-200 rounded-lg flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-building text-indigo-500"></i>
                    </div>
                @endif
                <div>
                    <p class="font-semibold text-gray-800 text-sm">{{ $client->interestedProperty->title }}</p>
                    <p class="text-xs text-gray-500">{{ $client->interestedProperty->location ?? '' }}</p>
                    <p class="text-xs font-bold text-indigo-600">₱{{ number_format($client->interestedProperty->price, 0) }}</p>
                </div>
            </div>
            @if($client->purchase_notes)
                <p class="text-xs text-indigo-700 bg-white rounded-lg p-3 border border-indigo-100">
                    <i class="fas fa-comment-alt mr-1"></i> {{ $client->purchase_notes }}
                </p>
            @endif
            @if(auth()->user()->isAdmin() || auth()->user()->isAgent())
            <a href="{{ route('reservations.create', ['property_id' => $client->interestedProperty->id, 'client_id' => $client->id]) }}"
                class="mt-3 block text-center bg-indigo-600 text-white py-2 rounded-lg text-sm hover:bg-indigo-700 transition">
                <i class="fas fa-calendar-check mr-1"></i> Create Reservation for This Property
            </a>
            @endif
        </div>
        @endif
    </div>

    {{-- Right: Reservations & Payments --}}
    <div class="lg:col-span-2 space-y-6">

        {{-- Reservations --}}
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="font-semibold text-gray-800 mb-4">Reservations ({{ $client->reservations->count() }})</h3>
            @forelse($client->reservations as $reservation)
            <div class="flex items-center justify-between py-3 border-b border-gray-50 last:border-0">
                <div>
                    <p class="text-sm font-medium text-gray-800">{{ $reservation->property->title ?? 'N/A' }}</p>
                    <p class="text-xs text-gray-400">{{ $reservation->reservation_date }}</p>
                </div>
                <span class="text-xs px-2.5 py-1 rounded-full font-medium
                    {{ $reservation->status === 'confirmed' ? 'bg-green-100 text-green-700' : '' }}
                    {{ $reservation->status === 'pending'   ? 'bg-yellow-100 text-yellow-700' : '' }}
                    {{ $reservation->status === 'cancelled' ? 'bg-red-100 text-red-700' : '' }}
                    {{ $reservation->status === 'completed' ? 'bg-blue-100 text-blue-700' : '' }}">
                    {{ ucfirst($reservation->status) }}
                </span>
            </div>
            @empty
            <p class="text-sm text-gray-400">No reservations yet.</p>
            @endforelse
        </div>

        {{-- Payments --}}
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="font-semibold text-gray-800 mb-4">Payments ({{ $client->payments->count() }})</h3>
            @forelse($client->payments as $payment)
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
