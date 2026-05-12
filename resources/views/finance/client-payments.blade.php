@extends('layouts.app')

@section('title', $client->full_name . ' — Payments')
@section('page-title', $client->full_name)
@section('page-subtitle', 'Payment history for this client')

@section('content')

{{-- Back + Export --}}
<div class="flex items-center justify-between mb-6">
    <a href="{{ route('finance.payments') }}"
        class="flex items-center gap-2 text-sm text-gray-500 hover:text-indigo-600 transition">
        <i class="fas fa-arrow-left"></i> Back to Payments
    </a>
    <div class="flex items-center gap-2">
        <a href="{{ route('finance.client.payments', [$client, 'export' => 'csv'] + request()->query()) }}"
            class="flex items-center gap-1.5 bg-green-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-green-700 transition">
            <i class="fas fa-file-csv"></i> Export CSV
        </a>
        <a href="{{ route('finance.export.pdf', ['client_id' => $client->id] + request()->query()) }}" target="_blank"
            class="flex items-center gap-1.5 bg-red-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-red-700 transition">
            <i class="fas fa-file-pdf"></i> Export PDF
        </a>
    </div>
</div>

{{-- Client Info + Summary --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">

    {{-- Client Card --}}
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

    {{-- Total Paid --}}
    <div class="bg-white rounded-xl shadow-sm p-6 flex items-center gap-4">
        <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
            <i class="fas fa-check-circle text-green-600 text-xl"></i>
        </div>
        <div>
            <p class="text-sm text-gray-500">Total Paid</p>
            <p class="text-xl font-bold text-green-600">₱{{ number_format($totalPaid, 2) }}</p>
            <p class="text-xs text-gray-400">{{ $payments->where('status','completed')->count() }} completed</p>
        </div>
    </div>

    {{-- Pending --}}
    <div class="bg-white rounded-xl shadow-sm p-6 flex items-center gap-4">
        <div class="w-12 h-12 bg-yellow-100 rounded-xl flex items-center justify-center">
            <i class="fas fa-clock text-yellow-600 text-xl"></i>
        </div>
        <div>
            <p class="text-sm text-gray-500">Pending Amount</p>
            <p class="text-xl font-bold text-yellow-600">₱{{ number_format($totalPending, 2) }}</p>
            <p class="text-xs text-gray-400">{{ $payments->where('status','pending')->count() }} pending</p>
        </div>
    </div>
</div>

{{-- Filters --}}
<div class="bg-white rounded-xl shadow-sm p-4 mb-5">
    <form method="GET" action="{{ route('finance.client.payments', $client) }}" class="flex flex-wrap gap-3 items-end">
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Status</label>
            <select name="status" class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">All Status</option>
                @foreach(['pending','completed','failed','cancelled'] as $s)
                    <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Method</label>
            <select name="payment_method" class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">All Methods</option>
                @foreach(['cash','bank_transfer','credit_card','gcash','check','pagibig'] as $m)
                    <option value="{{ $m }}" {{ request('payment_method') === $m ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ',$m)) }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-indigo-700 transition">
            <i class="fas fa-search mr-1"></i> Filter
        </button>
        <a href="{{ route('finance.client.payments', $client) }}" class="bg-gray-100 text-gray-600 px-4 py-2 rounded-lg text-sm hover:bg-gray-200 transition">Reset</a>
    </form>
</div>

{{-- Payments Table --}}
<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
        <p class="text-sm font-semibold text-gray-700">{{ $payments->count() }} transaction(s)</p>
        <a href="{{ route('finance.payments.create', ['client_id' => $client->id]) }}"
            class="text-xs bg-indigo-600 text-white px-3 py-1.5 rounded-lg hover:bg-indigo-700 transition">
            <i class="fas fa-plus mr-1"></i> Record Payment
        </a>
    </div>
    <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-100">
            <tr>
                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Property</th>
                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Type</th>
                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Amount</th>
                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Method</th>
                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Reference</th>
                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Date</th>
                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Proof</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @forelse($payments as $payment)
            <tr class="hover:bg-gray-50 transition">
                <td class="px-6 py-4 text-xs text-gray-700 font-medium">
                    {{ $payment->reservation->property->title ?? '—' }}
                </td>
                <td class="px-6 py-4">
                    <span class="text-xs px-2 py-1 bg-gray-100 text-gray-600 rounded-full">
                        {{ ucfirst(str_replace('_', ' ', $payment->payment_type)) }}
                    </span>
                </td>
                <td class="px-6 py-4 font-bold text-gray-800">₱{{ number_format($payment->amount, 2) }}</td>
                <td class="px-6 py-4 text-gray-600 text-xs">{{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}</td>
                <td class="px-6 py-4 text-xs text-gray-400">{{ $payment->reference_number ?? '—' }}</td>
                <td class="px-6 py-4 text-xs text-gray-600">{{ $payment->payment_date->format('M d, Y') }}</td>
                <td class="px-6 py-4">
                    <span class="text-xs px-2.5 py-1 rounded-full font-medium
                        {{ $payment->status === 'completed' ? 'bg-green-100 text-green-700'  : '' }}
                        {{ $payment->status === 'pending'   ? 'bg-yellow-100 text-yellow-700' : '' }}
                        {{ $payment->status === 'failed'    ? 'bg-red-100 text-red-700'      : '' }}
                        {{ $payment->status === 'cancelled' ? 'bg-gray-100 text-gray-600'    : '' }}">
                        {{ ucfirst($payment->status) }}
                    </span>
                </td>
                <td class="px-6 py-4">
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
            @empty
            <tr>
                <td colspan="8" class="px-6 py-16 text-center text-gray-400">
                    <i class="fas fa-receipt text-4xl mb-3 block text-gray-200"></i>
                    No payments found for this client.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Total Summary Row --}}
    @if($payments->count())
    <div class="px-6 py-4 border-t border-gray-100 bg-gray-50 flex items-center justify-between">
        <p class="text-sm text-gray-500">{{ $payments->count() }} transactions</p>
        <div class="flex items-center gap-6 text-sm">
            <span class="text-gray-500">Total Paid: <span class="font-bold text-green-600">₱{{ number_format($totalPaid, 2) }}</span></span>
            @if($totalPending > 0)
                <span class="text-gray-500">Pending: <span class="font-bold text-yellow-600">₱{{ number_format($totalPending, 2) }}</span></span>
            @endif
        </div>
    </div>
    @endif
</div>

@endsection
