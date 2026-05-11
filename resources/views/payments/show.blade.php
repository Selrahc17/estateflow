@extends('layouts.app')

@section('title', 'Payment Details - EstateFlow')
@section('page-title', 'Payment Details')
@section('page-subtitle', 'Transaction record')

@section('content')
<div class="max-w-2xl">
    <div class="bg-white rounded-xl shadow-sm p-6">

        {{-- Header --}}
        <div class="flex items-center justify-between mb-6 pb-6 border-b border-gray-100">
            <div>
                <p class="text-3xl font-bold text-indigo-600">₱{{ number_format($payment->amount, 2) }}</p>
                <p class="text-sm text-gray-400 mt-1">{{ $payment->payment_date->format('F d, Y') }}</p>
            </div>
            <span class="text-sm px-3 py-1.5 rounded-full font-medium
                {{ $payment->status === 'completed' ? 'bg-green-100 text-green-700'  : '' }}
                {{ $payment->status === 'pending'   ? 'bg-yellow-100 text-yellow-700' : '' }}
                {{ $payment->status === 'failed'    ? 'bg-red-100 text-red-700'      : '' }}
                {{ $payment->status === 'cancelled' ? 'bg-gray-100 text-gray-600'    : '' }}">
                {{ ucfirst($payment->status) }}
            </span>
        </div>

        {{-- Details --}}
        <div class="space-y-4 text-sm">
            <div class="flex justify-between py-2 border-b border-gray-50">
                <span class="text-gray-500">Payment Type</span>
                <span class="font-medium text-gray-800">{{ ucfirst(str_replace('_', ' ', $payment->payment_type)) }}</span>
            </div>
            <div class="flex justify-between py-2 border-b border-gray-50">
                <span class="text-gray-500">Payment Method</span>
                <span class="font-medium text-gray-800">{{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}</span>
            </div>
            @if($payment->reference_number)
            <div class="flex justify-between py-2 border-b border-gray-50">
                <span class="text-gray-500">Reference No.</span>
                <span class="font-medium text-gray-800">{{ $payment->reference_number }}</span>
            </div>
            @endif
            <div class="flex justify-between py-2 border-b border-gray-50">
                <span class="text-gray-500">Currency</span>
                <span class="font-medium text-gray-800">{{ $payment->currency }}</span>
            </div>
            <div class="flex justify-between py-2 border-b border-gray-50">
                <span class="text-gray-500">Client</span>
                <a href="{{ route('clients.show', $payment->client) }}" class="font-medium text-indigo-600 hover:underline">
                    {{ $payment->client->full_name ?? '—' }}
                </a>
            </div>
            @if($payment->agent)
            <div class="flex justify-between py-2 border-b border-gray-50">
                <span class="text-gray-500">Agent</span>
                <a href="{{ route('agents.show', $payment->agent) }}" class="font-medium text-indigo-600 hover:underline">
                    {{ $payment->agent->full_name }}
                </a>
            </div>
            @endif
            @if($payment->reservation)
            <div class="flex justify-between py-2 border-b border-gray-50">
                <span class="text-gray-500">Property</span>
                <span class="font-medium text-gray-800">{{ $payment->reservation->property->title ?? '—' }}</span>
            </div>
            <div class="flex justify-between py-2 border-b border-gray-50">
                <span class="text-gray-500">Reservation</span>
                <a href="{{ route('reservations.show', $payment->reservation) }}" class="font-medium text-indigo-600 hover:underline">
                    View Reservation #{{ $payment->reservation->id }}
                </a>
            </div>
            @endif
            @if($payment->description)
            <div class="py-2">
                <p class="text-gray-500 mb-1">Description</p>
                <p class="text-gray-800">{{ $payment->description }}</p>
            </div>
            @endif

            @if($payment->proof_image)
            <div class="py-2 border-t border-gray-50">
                <p class="text-gray-500 mb-2">Payment Proof</p>
                <a href="{{ asset('storage/' . $payment->proof_image) }}" target="_blank">
                    <img src="{{ asset('storage/' . $payment->proof_image) }}"
                        class="max-w-xs rounded-xl border border-gray-200 hover:opacity-90 transition"
                        alt="Payment proof">
                </a>
                <p class="text-xs text-gray-400 mt-1">Click image to view full size</p>
            </div>
            @endif
        </div>

        @if(auth()->user()->isAdmin() || auth()->user()->isAgent())
        <div class="mt-6 flex gap-3">
            <a href="{{ route('payments.edit', $payment) }}" class="bg-indigo-600 text-white px-6 py-2 rounded-lg text-sm hover:bg-indigo-700 transition font-medium">
                <i class="fas fa-edit mr-1"></i> Edit
            </a>
            <form method="POST" action="{{ route('payments.destroy', $payment) }}"
                onsubmit="return confirm('Delete this payment?')">
                @csrf @method('DELETE')
                <button type="submit" class="bg-red-50 text-red-600 px-6 py-2 rounded-lg text-sm hover:bg-red-100 transition">
                    <i class="fas fa-trash mr-1"></i> Delete
                </button>
            </form>
            <a href="{{ route('payments.index') }}" class="bg-gray-100 text-gray-600 px-6 py-2 rounded-lg text-sm hover:bg-gray-200 transition">Back</a>
        </div>
        @endif
    </div>
</div>
@endsection
