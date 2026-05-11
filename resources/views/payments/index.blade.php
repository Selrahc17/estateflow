@extends('layouts.app')

@section('title', 'Payments - EstateFlow')
@section('page-title', 'Payments')
@section('page-subtitle', 'Track all payment transactions')

@section('content')

{{-- Stats --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-6 mb-6">
    <div class="bg-white rounded-xl shadow-sm p-6 flex items-center gap-4">
        <div class="w-12 h-12 bg-indigo-100 rounded-xl flex items-center justify-center">
            <i class="fas fa-receipt text-indigo-600 text-xl"></i>
        </div>
        <div>
            <p class="text-sm text-gray-500">Total Transactions</p>
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
            <p class="text-sm text-gray-500">Pending Amount</p>
            <p class="text-lg font-bold text-gray-800">₱{{ number_format($pendingAmount, 0) }}</p>
        </div>
    </div>
</div>

{{-- Filters --}}
<div class="bg-white rounded-xl shadow-sm p-5 mb-6">
    <form method="GET" action="{{ route('payments.index') }}" class="flex flex-wrap gap-3 items-end">
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Search</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Client or reference no..."
                class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 w-56">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Status</label>
            <select name="status" class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">All Status</option>
                <option value="pending"   {{ request('status') === 'pending'   ? 'selected' : '' }}>Pending</option>
                <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                <option value="failed"    {{ request('status') === 'failed'    ? 'selected' : '' }}>Failed</option>
                <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Method</label>
            <select name="payment_method" class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">All Methods</option>
                <option value="cash"          {{ request('payment_method') === 'cash'          ? 'selected' : '' }}>Cash</option>
                <option value="bank_transfer" {{ request('payment_method') === 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                <option value="credit_card"   {{ request('payment_method') === 'credit_card'   ? 'selected' : '' }}>Credit Card</option>
                <option value="gcash"         {{ request('payment_method') === 'gcash'         ? 'selected' : '' }}>GCash</option>
                <option value="check"         {{ request('payment_method') === 'check'         ? 'selected' : '' }}>Check</option>
                <option value="pagibig"       {{ request('payment_method') === 'pagibig'       ? 'selected' : '' }}>Pag-IBIG</option>
            </select>
        </div>
        <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-indigo-700 transition">
            <i class="fas fa-search mr-1"></i> Filter
        </button>
        <a href="{{ route('payments.index') }}" class="bg-gray-100 text-gray-600 px-4 py-2 rounded-lg text-sm hover:bg-gray-200 transition">Reset</a>
    </form>
</div>

{{-- Header Row --}}
<div class="flex items-center justify-between mb-4">
    <p class="text-sm text-gray-500">{{ $payments->total() }} payments found</p>
    <div class="flex items-center gap-2">
        @if(auth()->user()->isAdmin() || auth()->user()->isAgent())
            <a href="{{ route('payments.export.csv', request()->query()) }}"
                class="flex items-center gap-1.5 bg-green-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-green-700 transition">
                <i class="fas fa-file-csv"></i> CSV
            </a>
            <a href="{{ route('payments.export.pdf', request()->query()) }}" target="_blank"
                class="flex items-center gap-1.5 bg-red-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-red-700 transition">
                <i class="fas fa-file-pdf"></i> PDF
            </a>
            <a href="{{ route('payments.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-indigo-700 transition">
                <i class="fas fa-plus mr-1"></i> Record Payment
            </a>
        @endif
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
                <th class="text-right px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @forelse($payments as $payment)
            <tr class="hover:bg-gray-50 transition">
                <td class="px-6 py-4">
                    <p class="font-medium text-gray-800">{{ $payment->client->full_name ?? '—' }}</p>
                </td>
                <td class="px-6 py-4 text-gray-600 text-xs">
                    {{ $payment->reservation->property->title ?? '—' }}
                </td>
                <td class="px-6 py-4">
                    <span class="text-xs px-2 py-1 bg-gray-100 text-gray-600 rounded-full">
                        {{ ucfirst(str_replace('_', ' ', $payment->payment_type)) }}
                    </span>
                </td>
                <td class="px-6 py-4 font-bold text-gray-800">₱{{ number_format($payment->amount, 2) }}</td>
                <td class="px-6 py-4 text-gray-600">
                    <div class="flex items-center gap-1.5">
                        @if($payment->payment_method === 'gcash')
                            <i class="fas fa-mobile-alt text-blue-500"></i>
                        @elseif($payment->payment_method === 'cash')
                            <i class="fas fa-money-bill text-green-500"></i>
                        @elseif($payment->payment_method === 'bank_transfer')
                            <i class="fas fa-university text-indigo-500"></i>
                        @elseif($payment->payment_method === 'credit_card')
                            <i class="fas fa-credit-card text-purple-500"></i>
                        @elseif($payment->payment_method === 'pagibig')
                            <i class="fas fa-home text-red-500"></i>
                        @else
                            <i class="fas fa-file-alt text-gray-400"></i>
                        @endif
                        {{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}
                    </div>
                </td>
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
                    <div class="flex items-center justify-end gap-2">
                        <a href="{{ route('payments.show', $payment) }}" class="text-xs px-3 py-1.5 bg-indigo-50 text-indigo-600 rounded-lg hover:bg-indigo-100 transition">View</a>
                        @if(auth()->user()->isAdmin() || auth()->user()->isAgent())
                            <a href="{{ route('payments.edit', $payment) }}" class="text-xs px-3 py-1.5 bg-gray-50 text-gray-600 rounded-lg hover:bg-gray-100 transition">Edit</a>
                            <form method="POST" action="{{ route('payments.destroy', $payment) }}"
                                onsubmit="return confirm('Delete this payment?')">
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
