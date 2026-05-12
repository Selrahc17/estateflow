@extends('layouts.app')

@section('title', ($reservation->property->title ?? 'Property') . ' — Payments')
@section('page-title', $reservation->property->title ?? 'Property Payments')
@section('page-subtitle', 'Payment transactions for this property')

@section('content')

{{-- Back + Exports --}}
<div class="flex items-center justify-between mb-6">
    <a href="{{ route('finance.client.payments', $client) }}"
        class="flex items-center gap-2 text-sm text-gray-500 hover:text-indigo-600 transition">
        <i class="fas fa-arrow-left"></i> Back to {{ $client->full_name }}
    </a>
    <div class="flex items-center gap-2">
        <a href="{{ route('finance.reservation.payments', [$client, $reservation, 'export' => 'csv']) }}"
            class="flex items-center gap-1.5 bg-green-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-green-700 transition">
            <i class="fas fa-file-csv"></i> Export CSV
        </a>
        <a href="{{ route('finance.reservation.payments', [$client, $reservation, 'export' => 'pdf']) }}" target="_blank"
            class="flex items-center gap-1.5 bg-red-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-red-700 transition">
            <i class="fas fa-file-pdf"></i> Export PDF
        </a>
        <a href="{{ route('finance.payments.create', ['reservation_id' => $reservation->id]) }}"
            class="flex items-center gap-1.5 bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-indigo-700 transition">
            <i class="fas fa-plus"></i> Record Payment
        </a>
    </div>
</div>

{{-- Summary Cards --}}
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">

    {{-- Client --}}
    <div class="bg-white rounded-xl shadow-sm p-5 flex items-center gap-3">
        <div class="w-10 h-10 bg-indigo-100 rounded-full flex items-center justify-center flex-shrink-0">
            <span class="text-indigo-600 font-bold">{{ strtoupper(substr($client->full_name, 0, 1)) }}</span>
        </div>
        <div>
            <p class="text-xs text-gray-400">Client</p>
            <p class="font-semibold text-gray-800 text-sm">{{ $client->full_name }}</p>
            <p class="text-xs text-gray-400">{{ $client->phone ?? '—' }}</p>
        </div>
    </div>

    {{-- Property Price --}}
    <div class="bg-white rounded-xl shadow-sm p-5 flex items-center gap-3">
        <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center">
            <i class="fas fa-building text-blue-600"></i>
        </div>
        <div>
            <p class="text-xs text-gray-400">Property Price</p>
            <p class="font-bold text-gray-800">₱{{ number_format($reservation->property->price ?? 0, 2) }}</p>
        </div>
    </div>

    {{-- Total Paid --}}
    <div class="bg-white rounded-xl shadow-sm p-5 flex items-center gap-3">
        <div class="w-10 h-10 bg-green-100 rounded-xl flex items-center justify-center">
            <i class="fas fa-check-circle text-green-600"></i>
        </div>
        <div>
            <p class="text-xs text-gray-400">Total Paid</p>
            <p class="font-bold text-green-600">₱{{ number_format($reservation->total_paid, 2) }}</p>
        </div>
    </div>

    {{-- Remaining --}}
    <div class="bg-white rounded-xl shadow-sm p-5 flex items-center gap-3">
        <div class="w-10 h-10 {{ $reservation->remaining <= 0 ? 'bg-green-100' : 'bg-red-100' }} rounded-xl flex items-center justify-center">
            <i class="fas {{ $reservation->remaining <= 0 ? 'fa-check' : 'fa-clock' }} {{ $reservation->remaining <= 0 ? 'text-green-600' : 'text-red-600' }}"></i>
        </div>
        <div>
            <p class="text-xs text-gray-400">Remaining Balance</p>
            @if($reservation->remaining <= 0)
                <p class="font-bold text-green-600">Fully Paid ✓</p>
            @else
                <p class="font-bold text-red-600">₱{{ number_format($reservation->remaining, 2) }}</p>
            @endif
        </div>
    </div>
</div>

{{-- Reservation Info --}}
<div class="bg-white rounded-xl shadow-sm p-5 mb-5 flex flex-wrap gap-6 text-sm">
    <div>
        <p class="text-xs text-gray-400">Property</p>
        <p class="font-medium text-gray-800">{{ $reservation->property->title ?? '—' }}</p>
    </div>
    <div>
        <p class="text-xs text-gray-400">Location</p>
        <p class="font-medium text-gray-800">{{ $reservation->property->location ?? '—' }}</p>
    </div>
    <div>
        <p class="text-xs text-gray-400">Reservation Date</p>
        <p class="font-medium text-gray-800">{{ $reservation->reservation_date->format('M d, Y') }}</p>
    </div>
    <div>
        <p class="text-xs text-gray-400">Status</p>
        <span class="text-xs px-2.5 py-1 rounded-full font-medium
            {{ $reservation->status === 'confirmed' ? 'bg-green-100 text-green-700'   : '' }}
            {{ $reservation->status === 'pending'   ? 'bg-yellow-100 text-yellow-700' : '' }}
            {{ $reservation->status === 'completed' ? 'bg-blue-100 text-blue-700'     : '' }}
            {{ $reservation->status === 'cancelled' ? 'bg-red-100 text-red-700'       : '' }}">
            {{ ucfirst($reservation->status) }}
        </span>
    </div>
    @if($reservation->agent)
    <div>
        <p class="text-xs text-gray-400">Agent</p>
        <p class="font-medium text-gray-800">{{ $reservation->agent->full_name }}</p>
    </div>
    @endif
</div>

{{-- Payments Table --}}
<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-100">
        <p class="text-sm font-semibold text-gray-700">{{ $reservation->payments->count() }} transaction(s)</p>
    </div>

    @if($reservation->payments->count())
    <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-100">
            <tr>
                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Type</th>
                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Amount</th>
                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Method</th>
                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Reference</th>
                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Date</th>
                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Status</th>
                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase">Proof</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @foreach($reservation->payments as $payment)
            <tr class="hover:bg-gray-50 transition">
                <td class="px-6 py-3">
                    <span class="text-xs px-2 py-1 bg-gray-100 text-gray-600 rounded-full">
                        {{ ucfirst(str_replace('_', ' ', $payment->payment_type)) }}
                    </span>
                </td>
                <td class="px-6 py-3 font-bold text-gray-800">₱{{ number_format($payment->amount, 2) }}</td>
                <td class="px-6 py-3 text-xs text-gray-600">{{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}</td>
                <td class="px-6 py-3 text-xs text-gray-400">{{ $payment->reference_number ?? '—' }}</td>
                <td class="px-6 py-3 text-xs text-gray-600">{{ $payment->payment_date->format('M d, Y') }}</td>
                <td class="px-6 py-3">
                    <span class="text-xs px-2.5 py-1 rounded-full font-medium
                        {{ $payment->status === 'completed' ? 'bg-green-100 text-green-700'   : '' }}
                        {{ $payment->status === 'pending'   ? 'bg-yellow-100 text-yellow-700' : '' }}
                        {{ $payment->status === 'failed'    ? 'bg-red-100 text-red-700'       : '' }}
                        {{ $payment->status === 'cancelled' ? 'bg-gray-100 text-gray-600'     : '' }}">
                        {{ ucfirst($payment->status) }}
                    </span>
                </td>
                <td class="px-6 py-3">
                    @if($payment->proof_image)
                        <a href="{{ asset('storage/' . $payment->proof_image) }}" target="_blank"
                            class="text-xs text-indigo-600 hover:underline">
                            <i class="fas fa-image mr-1"></i>View
                        </a>
                    @else
                        <span class="text-xs text-gray-300">—</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Totals --}}
    <div class="px-6 py-4 border-t border-gray-100 bg-gray-50 flex items-center justify-end gap-8 text-sm">
        <span class="text-gray-500">Total Paid: <span class="font-bold text-green-600">₱{{ number_format($reservation->total_paid, 2) }}</span></span>
        @if($reservation->total_pending > 0)
            <span class="text-gray-500">Pending: <span class="font-bold text-yellow-600">₱{{ number_format($reservation->total_pending, 2) }}</span></span>
        @endif
        @if($reservation->remaining > 0)
            <span class="text-gray-500">Remaining: <span class="font-bold text-red-600">₱{{ number_format($reservation->remaining, 2) }}</span></span>
        @else
            <span class="font-bold text-green-600"><i class="fas fa-check-circle mr-1"></i>Fully Paid</span>
        @endif
    </div>

    @else
    <div class="px-6 py-16 text-center text-gray-400">
        <i class="fas fa-receipt text-4xl mb-3 block text-gray-200"></i>
        No payments recorded for this property yet.
    </div>
    @endif
</div>

@endsection
