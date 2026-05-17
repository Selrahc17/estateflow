@extends('layouts.client')
@section('title', 'My Payments — EstateFlow')
@section('page-title', 'My Payments')
@section('page-subtitle', 'Your complete payment history')
@section('content')

{{-- Stats --}}
<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
    <div class="bg-white rounded-xl shadow-sm p-6 flex items-center gap-4">
        <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
            <i class="fas fa-check-circle text-green-600 text-xl"></i>
        </div>
        <div>
            <p class="text-sm text-gray-500">Total Paid</p>
            <p class="text-2xl font-bold text-gray-800">₱{{ number_format($payments->where('status', 'completed')->sum('amount'), 2) }}</p>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-6 flex items-center gap-4">
        <div class="w-12 h-12 bg-yellow-100 rounded-xl flex items-center justify-center">
            <i class="fas fa-clock text-yellow-600 text-xl"></i>
        </div>
        <div>
            <p class="text-sm text-gray-500">Pending Amount</p>
            <p class="text-2xl font-bold text-gray-800">₱{{ number_format($payments->where('status', 'pending')->sum('amount'), 2) }}</p>
        </div>
    </div>
</div>

{{-- Payment Schedules --}}
@if($reservationsWithSchedules->count())
<div class="mb-6">
    <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wider mb-3"><i class="fas fa-calendar-alt mr-1 text-indigo-400"></i> Payment Schedules</h2>
    <div class="space-y-4">
        @foreach($reservationsWithSchedules as $res)
        @php
            $totalDue  = $res->paymentSchedules->sum('amount_due');
            $totalPaid = $res->paymentSchedules->sum('amount_paid');
            $remaining = $totalDue - $totalPaid;
            $pct = $totalDue > 0 ? min(100, round(($totalPaid / $totalDue) * 100)) : 0;
            $statusColors = \App\Models\PaymentSchedule::STATUS_COLORS;
            $statusLabels = \App\Models\PaymentSchedule::STATUSES;
        @endphp
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="p-4 border-b border-gray-50 flex items-center justify-between gap-4">
                <div>
                    <p class="font-semibold text-gray-800 text-sm">{{ $res->property->title ?? '—' }}</p>
                    @if($res->property?->block)
                        <p class="text-xs text-gray-400">Block {{ $res->property->block }}, Lot {{ $res->property->lot }}</p>
                    @endif
                </div>
                <div class="flex items-center gap-4 text-xs">
                    <div class="text-center">
                        <p class="text-gray-400">Total Due</p>
                        <p class="font-bold text-gray-700">₱{{ number_format($totalDue, 2) }}</p>
                    </div>
                    <div class="text-center">
                        <p class="text-gray-400">Paid</p>
                        <p class="font-bold text-green-600">₱{{ number_format($totalPaid, 2) }}</p>
                    </div>
                    <div class="text-center">
                        <p class="text-gray-400">Remaining</p>
                        <p class="font-bold {{ $remaining <= 0 ? 'text-green-600' : 'text-red-500' }}">{{ $remaining <= 0 ? 'Paid ✓' : '₱' . number_format($remaining, 2) }}</p>
                    </div>
                    <a href="{{ route('client.schedule', $res) }}" class="bg-indigo-600 text-white px-3 py-1.5 rounded-lg hover:bg-indigo-700 transition whitespace-nowrap">
                        <i class="fas fa-eye mr-1"></i> View Full
                    </a>
                </div>
            </div>
            {{-- Progress --}}
            <div class="px-4 py-2 bg-gray-50">
                <div class="flex items-center justify-between text-xs text-gray-400 mb-1">
                    <span>Progress</span><span>{{ $pct }}%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-1.5">
                    <div class="bg-indigo-500 h-1.5 rounded-full" style="width: {{ $pct }}%"></div>
                </div>
            </div>
            {{-- Installments --}}
            <table class="w-full text-xs">
                <thead class="bg-gray-50 border-t border-gray-100">
                    <tr>
                        <th class="text-left px-4 py-2 text-gray-500 font-semibold">#</th>
                        <th class="text-left px-4 py-2 text-gray-500 font-semibold">Due Date</th>
                        <th class="text-left px-4 py-2 text-gray-500 font-semibold">Amount Due</th>
                        <th class="text-left px-4 py-2 text-gray-500 font-semibold">Amount Paid</th>
                        <th class="text-left px-4 py-2 text-gray-500 font-semibold">Balance</th>
                        <th class="text-left px-4 py-2 text-gray-500 font-semibold">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($res->paymentSchedules->sortBy('installment_number') as $s)
                    @php $bal = max(0, (float)$s->amount_due - (float)$s->amount_paid); @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-2 text-gray-600">{{ $s->installment_number }}</td>
                        <td class="px-4 py-2 {{ $s->status === 'overdue' ? 'text-red-600 font-medium' : 'text-gray-600' }}">{{ $s->due_date->format('M d, Y') }}</td>
                        <td class="px-4 py-2 text-gray-700">₱{{ number_format($s->amount_due, 2) }}</td>
                        <td class="px-4 py-2 text-green-600">₱{{ number_format($s->amount_paid, 2) }}</td>
                        <td class="px-4 py-2 {{ $bal <= 0 ? 'text-green-600' : 'text-red-500' }}">{{ $bal <= 0 ? 'Paid ✓' : '₱' . number_format($bal, 2) }}</td>
                        <td class="px-4 py-2">
                            <span class="px-2 py-0.5 rounded-full font-medium {{ $statusColors[$s->status] ?? 'bg-gray-100 text-gray-600' }}">
                                {{ $statusLabels[$s->status] ?? ucfirst($s->status) }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- Payments List --}}
<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-100">
            <tr>
                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Property</th>
                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Type</th>
                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Method</th>
                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Reference</th>
                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Date</th>
                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Amount</th>
                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @forelse($payments as $payment)
            <tr class="hover:bg-gray-50 transition">
                <td class="px-6 py-4">
                    <p class="font-medium text-gray-800">{{ $payment->reservation->property->title ?? '—' }}</p>
                    <p class="text-xs text-gray-400">Reservation #{{ $payment->reservation_id ?? '—' }}</p>
                </td>
                <td class="px-6 py-4">
                    <span class="text-xs px-2 py-1 bg-gray-100 text-gray-600 rounded-full">
                        {{ ucfirst(str_replace('_', ' ', $payment->payment_type)) }}
                    </span>
                </td>
                <td class="px-6 py-4">
                    <div class="flex items-center gap-1.5 text-gray-600">
                        @if($payment->payment_method === 'cash')
                            <i class="fas fa-money-bill text-green-500"></i>
                        @elseif($payment->payment_method === 'bank_transfer')
                            <i class="fas fa-university text-indigo-500"></i>
                        @elseif($payment->payment_method === 'credit_card')
                            <i class="fas fa-credit-card text-purple-500"></i>
                        @else
                            <i class="fas fa-file-alt text-gray-400"></i>
                        @endif
                        <span class="text-xs">{{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}</span>
                    </div>
                </td>
                <td class="px-6 py-4 text-xs text-gray-500">{{ $payment->reference_number ?? '—' }}</td>
                <td class="px-6 py-4 text-gray-600 text-xs">{{ $payment->payment_date->format('M d, Y') }}</td>
                <td class="px-6 py-4 font-bold text-gray-800">₱{{ number_format($payment->amount, 2) }}</td>
                <td class="px-6 py-4">
                    <span class="text-xs px-2.5 py-1 rounded-full font-medium
                        {{ $payment->status === 'completed' ? 'bg-green-100 text-green-700'   : '' }}
                        {{ $payment->status === 'pending'   ? 'bg-yellow-100 text-yellow-700' : '' }}
                        {{ $payment->status === 'failed'    ? 'bg-red-100 text-red-700'       : '' }}
                        {{ $payment->status === 'cancelled' ? 'bg-gray-100 text-gray-600'     : '' }}">
                        {{ ucfirst($payment->status) }}
                    </span>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="px-6 py-16 text-center text-gray-400">
                    <i class="fas fa-receipt text-4xl mb-3 block text-gray-200"></i>
                    <p>No payment records yet.</p>
                    <a href="{{ route('client.reservations') }}" class="mt-4 inline-block bg-indigo-600 text-white px-6 py-2 rounded-lg text-sm hover:bg-indigo-700 transition">
                        View My Reservations
                    </a>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    @if(method_exists($payments, 'hasPages') && $payments->hasPages())
        <div class="px-6 py-4 border-t border-gray-100">{{ $payments->links() }}</div>
    @endif
</div>

@endsection
