@extends('layouts.client')
@section('title', 'Payment Schedule — EstateFlow')
@section('page-title', 'Payment Schedule')
@section('page-subtitle', $reservation->property->title ?? '')
@section('content')

<div class="max-w-3xl">

    <a href="{{ route('client.reservations') }}" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-indigo-600 transition mb-6">
        <i class="fas fa-arrow-left"></i> Back to My Reservations
    </a>

    {{-- Property Info --}}
    <div class="bg-white rounded-xl shadow-sm p-5 mb-6 flex items-center gap-4 border border-indigo-100">
        <div class="w-16 h-16 bg-indigo-50 rounded-xl flex items-center justify-center flex-shrink-0 overflow-hidden">
            @if($reservation->property?->image_main)
                <img src="{{ asset($reservation->property->image_main) }}" class="w-16 h-16 object-cover">
            @else
                <i class="fas fa-building text-indigo-300 text-2xl"></i>
            @endif
        </div>
        <div class="flex-1 min-w-0">
            <p class="font-semibold text-gray-800">{{ $reservation->property->title ?? '—' }}</p>
            @if($reservation->property?->block)
                <p class="text-xs text-gray-400">Block {{ $reservation->property->block }}, Lot {{ $reservation->property->lot }}</p>
            @endif
            <p class="text-sm font-bold text-indigo-600 mt-1">₱{{ number_format($reservation->property->price ?? 0, 0) }}</p>
        </div>
        <span class="text-xs px-2.5 py-1 rounded-full font-medium bg-indigo-100 text-indigo-700 flex-shrink-0">
            {{ $reservation->payment_scheme === 'pagibig' ? 'Pag-IBIG' : 'Cash / Bank Transfer' }}
        </span>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm p-4 text-center">
            <p class="text-xs text-gray-400 mb-1">Total Due</p>
            <p class="text-lg font-bold text-gray-800">₱{{ number_format($totalDue, 2) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4 text-center">
            <p class="text-xs text-gray-400 mb-1">Total Paid</p>
            <p class="text-lg font-bold text-green-600">₱{{ number_format($totalPaid, 2) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4 text-center">
            <p class="text-xs text-gray-400 mb-1">Remaining</p>
            <p class="text-lg font-bold {{ $remaining <= 0 ? 'text-green-600' : 'text-red-600' }}">
                {{ $remaining <= 0 ? 'Fully Paid ✓' : '₱' . number_format($remaining, 2) }}
            </p>
        </div>
    </div>

    @if($reservation->paymentSchedules->count() === 0)
    <div class="bg-white rounded-xl shadow-sm p-16 text-center text-gray-400">
        <i class="fas fa-calendar-alt text-4xl mb-3 block text-gray-200"></i>
        <p>Your payment schedule has not been issued yet.</p>
        <p class="text-xs mt-2">Finance will issue your schedule after document verification.</p>
    </div>
    @else

    {{-- Progress Bar --}}
    @php $pct = $totalDue > 0 ? min(100, round(($totalPaid / $totalDue) * 100)) : 0; @endphp
    <div class="bg-white rounded-xl shadow-sm p-5 mb-6">
        <div class="flex items-center justify-between mb-2">
            <p class="text-sm font-medium text-gray-700">Overall Progress</p>
            <p class="text-sm font-bold text-indigo-600">{{ $pct }}%</p>
        </div>
        <div class="w-full bg-gray-100 rounded-full h-3">
            <div class="bg-indigo-600 h-3 rounded-full transition-all" style="width: {{ $pct }}%"></div>
        </div>
    </div>

    {{-- Installment Cards --}}
    <div class="space-y-3">
        @foreach($reservation->paymentSchedules->sortBy('installment_number') as $schedule)
        @php
            $balance = max(0, (float)$schedule->amount_due - (float)$schedule->amount_paid);
            $colors  = \App\Models\PaymentSchedule::STATUS_COLORS;
            $labels  = \App\Models\PaymentSchedule::STATUSES;
        @endphp
        <div class="bg-white rounded-xl shadow-sm p-5 border-l-4
            {{ $schedule->status === 'paid'          ? 'border-green-400' : '' }}
            {{ $schedule->status === 'partially_paid'? 'border-blue-400'  : '' }}
            {{ $schedule->status === 'overdue'       ? 'border-red-400'   : '' }}
            {{ $schedule->status === 'due'           ? 'border-yellow-400': '' }}
            {{ $schedule->status === 'upcoming'      ? 'border-gray-200'  : '' }}">

            <div class="flex items-start justify-between gap-4">
                <div class="flex-1">
                    <div class="flex items-center gap-3 mb-2">
                        <p class="text-sm font-semibold text-gray-800">Installment #{{ $schedule->installment_number }}</p>
                        <span class="text-xs px-2.5 py-1 rounded-full font-medium {{ $colors[$schedule->status] ?? 'bg-gray-100 text-gray-600' }}">
                            {{ $labels[$schedule->status] ?? ucfirst($schedule->status) }}
                        </span>
                    </div>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-xs">
                        <div>
                            <p class="text-gray-400">Due Date</p>
                            <p class="font-medium {{ $schedule->status === 'overdue' ? 'text-red-600' : 'text-gray-700' }}">
                                {{ $schedule->due_date->format('M d, Y') }}
                            </p>
                        </div>
                        <div>
                            <p class="text-gray-400">Amount Due</p>
                            <p class="font-medium text-gray-700">₱{{ number_format($schedule->amount_due, 2) }}</p>
                        </div>
                        <div>
                            <p class="text-gray-400">Amount Paid</p>
                            <p class="font-medium text-green-600">₱{{ number_format($schedule->amount_paid, 2) }}</p>
                        </div>
                        <div>
                            <p class="text-gray-400">Balance</p>
                            <p class="font-medium {{ $balance <= 0 ? 'text-green-600' : 'text-red-600' }}">
                                {{ $balance <= 0 ? 'Paid ✓' : '₱' . number_format($balance, 2) }}
                            </p>
                        </div>
                    </div>
                    @if($schedule->receipt_number)
                    <p class="text-xs text-gray-400 mt-2"><i class="fas fa-receipt mr-1"></i>OR# {{ $schedule->receipt_number }}</p>
                    @endif
                    @if($schedule->notes)
                    <p class="text-xs text-gray-400 mt-1"><i class="fas fa-comment-alt mr-1"></i>{{ $schedule->notes }}</p>
                    @endif
                </div>
            </div>

            {{-- Payment history for this installment --}}
            @if($schedule->payments->count())
            <div class="mt-3 pt-3 border-t border-gray-50">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Payments</p>
                <div class="space-y-1.5">
                    @foreach($schedule->payments as $p)
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-gray-600">
                            {{ $p->payment_date->format('M d, Y') }}
                            · {{ ucfirst(str_replace('_', ' ', $p->payment_method)) }}
                            @if($p->receipt_number) · OR# {{ $p->receipt_number }} @endif
                        </span>
                        <span class="font-bold text-green-600">₱{{ number_format($p->amount, 2) }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

        </div>
        @endforeach
    </div>
    @endif

</div>
@endsection
