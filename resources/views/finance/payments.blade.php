@extends('layouts.app')

@section('title', 'Payments - EstateFlow')
@section('page-title', 'Payments')
@section('page-subtitle', 'All payment transactions')

@section('content')

{{-- Stats --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-6 mb-6">
    <div class="bg-white rounded-xl shadow-sm p-6 flex items-center gap-4">
        <div class="w-12 h-12 bg-indigo-100 rounded-xl flex items-center justify-center">
            <i class="fas fa-receipt text-indigo-600 text-xl"></i>
        </div>
        <div>
            <p class="text-sm text-gray-500">Total</p>
            <p class="text-2xl font-bold text-gray-800">{{ $totalPayments }}</p>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-6 flex items-center gap-4">
        <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
            <i class="fas fa-check-circle text-green-600 text-xl"></i>
        </div>
        <div>
            <p class="text-sm text-gray-500">Completed</p>
            <p class="text-2xl font-bold text-gray-800">{{ $completedCount }}</p>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-6 flex items-center gap-4">
        <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
            <i class="fas fa-peso-sign text-blue-600 text-xl"></i>
        </div>
        <div>
            <p class="text-sm text-gray-500">Total Collected</p>
            <p class="text-lg font-bold text-gray-800">₱{{ number_format($totalAmount, 0) }}</p>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-6 flex items-center gap-4">
        <div class="w-12 h-12 bg-yellow-100 rounded-xl flex items-center justify-center">
            <i class="fas fa-clock text-yellow-600 text-xl"></i>
        </div>
        <div>
            <p class="text-sm text-gray-500">Pending</p>
            <p class="text-lg font-bold text-gray-800">₱{{ number_format($pendingAmount, 0) }}</p>
        </div>
    </div>
</div>

{{-- Filters --}}
<div class="bg-white rounded-xl shadow-sm p-5 mb-6">
    <form method="GET" action="{{ route('finance.payments') }}" class="flex flex-wrap gap-3 items-end">
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Search</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Client or reference..."
                class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 w-56">
        </div>
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
        <a href="{{ route('finance.payments') }}" class="bg-gray-100 text-gray-600 px-4 py-2 rounded-lg text-sm hover:bg-gray-200 transition">Reset</a>
    </form>
</div>

{{-- Header --}}
<div class="flex items-center justify-between mb-4">
    <p class="text-sm text-gray-500">{{ $payments->total() }} payments found</p>
    <div class="flex items-center gap-2">
        <a href="{{ route('finance.export.csv', request()->query()) }}"
            class="flex items-center gap-1.5 bg-green-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-green-700 transition">
            <i class="fas fa-file-csv"></i> CSV
        </a>
        <a href="{{ route('finance.export.pdf', request()->query()) }}" target="_blank"
            class="flex items-center gap-1.5 bg-red-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-red-700 transition">
            <i class="fas fa-file-pdf"></i> PDF
        </a>
        <a href="{{ route('finance.payments.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-indigo-700 transition">
            <i class="fas fa-plus mr-1"></i> Record Payment
        </a>
    </div>
</div>

{{-- Table --}}
<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-100">
            <tr>
                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Client</th>
                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Property</th>
                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Type</th>
                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Amount</th>
                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Method</th>
                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Date</th>
                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Proof</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @forelse($payments as $payment)
            <tr class="hover:bg-gray-50 transition">
                <td class="px-6 py-4 font-medium text-gray-800">{{ $payment->client->full_name ?? '—' }}</td>
                <td class="px-6 py-4 text-xs text-gray-600">{{ $payment->reservation->property->title ?? '—' }}</td>
                <td class="px-6 py-4">
                    <span class="text-xs px-2 py-1 bg-gray-100 text-gray-600 rounded-full">
                        {{ ucfirst(str_replace('_', ' ', $payment->payment_type)) }}
                    </span>
                </td>
                <td class="px-6 py-4 font-bold text-gray-800">₱{{ number_format($payment->amount, 2) }}</td>
                <td class="px-6 py-4 text-gray-600">{{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}</td>
                <td class="px-6 py-4 text-gray-600">{{ $payment->payment_date->format('M d, Y') }}</td>
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
                    No payments found.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    @if($payments->hasPages())
        <div class="px-6 py-4 border-t border-gray-100">{{ $payments->links() }}</div>
    @endif
</div>

@endsection
