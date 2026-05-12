@extends('layouts.app')

@section('title', $client->full_name . ' — Payments')
@section('page-title', $client->full_name)
@section('page-subtitle', 'Payment history grouped by property')

@section('content')

{{-- Back + Export --}}
<div class="flex items-center justify-between mb-6">
    <a href="{{ route('finance.payments') }}"
        class="flex items-center gap-2 text-sm text-gray-500 hover:text-indigo-600 transition">
        <i class="fas fa-arrow-left"></i> Back to Payments
    </a>
    <div class="flex items-center gap-2">
        <a href="{{ route('finance.client.payments', [$client, 'export' => 'csv']) }}"
            class="flex items-center gap-1.5 bg-green-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-green-700 transition">
            <i class="fas fa-file-csv"></i> Export All CSV
        </a>
        <a href="{{ route('finance.payments.create', ['client_id' => $client->id]) }}"
            class="flex items-center gap-1.5 bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-indigo-700 transition">
            <i class="fas fa-plus"></i> Record Payment
        </a>
    </div>
</div>

{{-- Client Info + Summary --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <div class="bg-white rounded-xl shadow-sm p-6 flex items-center gap-4">
        <div class="w-12 h-12 bg-indigo-100 rounded-full flex items-center justify-center flex-shrink-0">
            <span class="text-indigo-600 font-bold text-lg">{{ strtoupper(substr($client->full_name, 0, 1)) }}</span>
        </div>
        <div>
            <p class="font-semibold text-gray-800">{{ $client->full_name }}</p>
            <p class="text-xs text-gray-400 mt-0.5">{{ $client->email ?? '—' }}</p>
            <p class="text-xs text-gray-400">{{ $client->phone ?? '—' }}</p>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-6 flex items-center gap-4">
        <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
            <i class="fas fa-check-circle text-green-600 text-xl"></i>
        </div>
        <div>
            <p class="text-sm text-gray-500">Total Paid</p>
            <p class="text-xl font-bold text-green-600">₱{{ number_format($totalPaid, 2) }}</p>
            <p class="text-xs text-gray-400">{{ $reservations->count() }} reservation(s)</p>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-6 flex items-center gap-4">
        <div class="w-12 h-12 bg-yellow-100 rounded-xl flex items-center justify-center">
            <i class="fas fa-clock text-yellow-600 text-xl"></i>
        </div>
        <div>
            <p class="text-sm text-gray-500">Pending Amount</p>
            <p class="text-xl font-bold text-yellow-600">₱{{ number_format($totalPending, 2) }}</p>
        </div>
    </div>
</div>

{{-- Grouped by Property/Reservation --}}
@forelse($reservations as $reservation)
<div class="bg-white rounded-xl shadow-sm overflow-hidden mb-5">

    {{-- Property Header --}}
    <div class="flex items-center justify-between px-6 py-4 bg-gray-50 border-b border-gray-100">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 bg-indigo-100 rounded-lg flex items-center justify-center flex-shrink-0">
                <i class="fas fa-building text-indigo-500 text-sm"></i>
            </div>
            <div>
                <p class="font-semibold text-gray-800">{{ $reservation->property->title ?? '—' }}</p>
                <p class="text-xs text-gray-400 mt-0.5">
                    <i class="fas fa-map-marker-alt mr-1"></i>{{ $reservation->property->location ?? 'No location' }}
                    · Reserved {{ $reservation->reservation_date->format('M d, Y') }}
                    · <span class="capitalize font-medium
                        {{ $reservation->status === 'confirmed' ? 'text-green-600' : '' }}
                        {{ $reservation->status === 'pending'   ? 'text-yellow-600' : '' }}
                        {{ $reservation->status === 'completed' ? 'text-blue-600' : '' }}
                        {{ $reservation->status === 'cancelled' ? 'text-red-500' : '' }}">
                        {{ ucfirst($reservation->status) }}
                    </span>
                </p>
            </div>
        </div>
        <div class="text-right flex items-center gap-4">
            {{-- Balance --}}
            <div>
                @if($reservation->remaining <= 0)
                    <span class="text-xs bg-green-100 text-green-700 px-2.5 py-1 rounded-full font-medium">
                        <i class="fas fa-check mr-1"></i>Fully Paid
                    </span>
                @else
                    <p class="text-xs text-gray-400">Remaining</p>
                    <p class="text-sm font-bold text-red-600">₱{{ number_format($reservation->remaining, 2) }}</p>
                @endif
            </div>
            {{-- Per-property export --}}
            <a href="{{ route('finance.reservation.payments', [$client, $reservation]) }}"
                class="text-xs bg-indigo-50 text-indigo-600 px-3 py-1.5 rounded-lg hover:bg-indigo-100 transition">
                <i class="fas fa-eye mr-1"></i>View
            </a>
        </div>
    </div>

    {{-- Property Price Summary --}}
    <div class="px-6 py-3 bg-indigo-50 border-b border-indigo-100 flex items-center gap-6 text-xs">
        <span class="text-gray-500">Property Price: <span class="font-semibold text-gray-700">₱{{ number_format($reservation->property->price ?? 0, 2) }}</span></span>
        <span class="text-gray-500">Total Paid: <span class="font-semibold text-green-600">₱{{ number_format($reservation->total_paid, 2) }}</span></span>
        @if($reservation->total_pending > 0)
            <span class="text-gray-500">Pending: <span class="font-semibold text-yellow-600">₱{{ number_format($reservation->total_pending, 2) }}</span></span>
        @endif
    </div>

    {{-- Payments Table --}}
    @if($reservation->payments->count())
    <table class="w-full text-sm">
        <thead class="border-b border-gray-100">
            <tr>
                <th class="text-left px-6 py-2.5 text-xs font-semibold text-gray-400 uppercase">Type</th>
                <th class="text-left px-6 py-2.5 text-xs font-semibold text-gray-400 uppercase">Amount</th>
                <th class="text-left px-6 py-2.5 text-xs font-semibold text-gray-400 uppercase">Method</th>
                <th class="text-left px-6 py-2.5 text-xs font-semibold text-gray-400 uppercase">Reference</th>
                <th class="text-left px-6 py-2.5 text-xs font-semibold text-gray-400 uppercase">Date</th>
                <th class="text-left px-6 py-2.5 text-xs font-semibold text-gray-400 uppercase">Status</th>
                <th class="text-left px-6 py-2.5 text-xs font-semibold text-gray-400 uppercase">Proof</th>
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
    @else
    <div class="px-6 py-8 text-center text-gray-400 text-sm">
        <i class="fas fa-receipt text-2xl mb-2 block text-gray-200"></i>
        No payments recorded for this property yet.
    </div>
    @endif

</div>
@empty
<div class="bg-white rounded-xl shadow-sm p-16 text-center text-gray-400">
    <i class="fas fa-receipt text-4xl mb-3 block text-gray-200"></i>
    <p>No reservations found for this client.</p>
</div>
@endforelse

@endsection
