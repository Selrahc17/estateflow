@extends('layouts.app')

@section('title', 'Finance Dashboard - EstateFlow')
@section('page-title', 'Finance Dashboard')
@section('page-subtitle', 'Payment overview and financial summary')

@section('content')

{{-- Stats --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-6 mb-8">
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
            <i class="fas fa-peso-sign text-green-600 text-xl"></i>
        </div>
        <div>
            <p class="text-sm text-gray-500">Total Collected</p>
            <p class="text-lg font-bold text-gray-800">₱{{ number_format($totalCollected, 0) }}</p>
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
    <div class="bg-white rounded-xl shadow-sm p-6 flex items-center gap-4">
        <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
            <i class="fas fa-calendar text-blue-600 text-xl"></i>
        </div>
        <div>
            <p class="text-sm text-gray-500">This Month</p>
            <p class="text-lg font-bold text-gray-800">₱{{ number_format($thisMonthTotal, 0) }}</p>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Recent Payments --}}
    <div class="lg:col-span-2 bg-white rounded-xl shadow-sm p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-semibold text-gray-800">Recent Payments</h3>
            <a href="{{ route('finance.payments') }}" class="text-sm text-indigo-600 hover:underline">View all</a>
        </div>
        <div class="space-y-3">
            @forelse($recentPayments as $payment)
            <div class="flex items-center justify-between py-2 border-b border-gray-50 last:border-0">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full flex items-center justify-center
                        {{ $payment->payment_method === 'cash'          ? 'bg-green-100'  : '' }}
                        {{ $payment->payment_method === 'bank_transfer' ? 'bg-indigo-100' : '' }}
                        {{ $payment->payment_method === 'credit_card'   ? 'bg-purple-100' : '' }}
                        {{ $payment->payment_method === 'check'         ? 'bg-gray-100'   : '' }}">
                        @if($payment->payment_method === 'cash')
                            <i class="fas fa-money-bill text-green-500 text-sm"></i>
                        @elseif($payment->payment_method === 'bank_transfer')
                            <i class="fas fa-university text-indigo-500 text-sm"></i>
                        @elseif($payment->payment_method === 'credit_card')
                            <i class="fas fa-credit-card text-purple-500 text-sm"></i>
                        @else
                            <i class="fas fa-file-alt text-gray-400 text-sm"></i>
                        @endif
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-800">{{ $payment->client->full_name ?? '—' }}</p>
                        <p class="text-xs text-gray-400">{{ $payment->reservation->property->title ?? 'No property' }} · {{ $payment->payment_date->format('M d, Y') }}</p>
                    </div>
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
            <p class="text-sm text-gray-400 text-center py-6">No payments recorded yet.</p>
            @endforelse
        </div>
    </div>

    {{-- Payment by Method + Quick Actions --}}
    <div class="space-y-4">

        {{-- By Method --}}
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="font-semibold text-gray-800 mb-4">Collections by Method</h3>
            @forelse($paymentsByMethod as $method => $total)
            <div class="flex items-center justify-between py-2 border-b border-gray-50 last:border-0">
                <span class="text-sm text-gray-600 capitalize">{{ str_replace('_', ' ', $method) }}</span>
                <span class="text-sm font-bold text-gray-800">₱{{ number_format($total, 0) }}</span>
            </div>
            @empty
            <p class="text-sm text-gray-400">No data yet.</p>
            @endforelse
        </div>

        {{-- Quick Actions --}}
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="font-semibold text-gray-800 mb-4">Quick Actions</h3>
            <div class="space-y-2">
                <a href="{{ route('finance.payments.create') }}"
                    class="flex items-center gap-3 p-3 bg-indigo-50 rounded-xl hover:bg-indigo-100 transition">
                    <i class="fas fa-plus text-indigo-600 w-5 text-center"></i>
                    <span class="text-sm font-medium text-indigo-700">Record Payment</span>
                </a>
                <a href="{{ route('finance.payments') }}"
                    class="flex items-center gap-3 p-3 bg-green-50 rounded-xl hover:bg-green-100 transition">
                    <i class="fas fa-list text-green-600 w-5 text-center"></i>
                    <span class="text-sm font-medium text-green-700">All Payments</span>
                </a>
                <a href="{{ route('finance.export.csv') }}"
                    class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl hover:bg-gray-100 transition">
                    <i class="fas fa-file-csv text-gray-600 w-5 text-center"></i>
                    <span class="text-sm font-medium text-gray-700">Export CSV</span>
                </a>
                <a href="{{ route('finance.export.pdf') }}" target="_blank"
                    class="flex items-center gap-3 p-3 bg-red-50 rounded-xl hover:bg-red-100 transition">
                    <i class="fas fa-file-pdf text-red-600 w-5 text-center"></i>
                    <span class="text-sm font-medium text-red-700">Export PDF</span>
                </a>
                <a href="{{ route('finance.reports.monthly') }}"
                    class="flex items-center gap-3 p-3 bg-purple-50 rounded-xl hover:bg-purple-100 transition">
                    <i class="fas fa-chart-bar text-purple-600 w-5 text-center"></i>
                    <span class="text-sm font-medium text-purple-700">Monthly Report</span>
                </a>
                <a href="{{ route('finance.reports.aging') }}"
                    class="flex items-center gap-3 p-3 bg-orange-50 rounded-xl hover:bg-orange-100 transition">
                    <i class="fas fa-exclamation-triangle text-orange-600 w-5 text-center"></i>
                    <span class="text-sm font-medium text-orange-700">Aging Report</span>
                </a>
            </div>
        </div>
    </div>
</div>

@endsection
