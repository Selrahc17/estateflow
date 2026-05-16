@extends('layouts.app')
@section('title', 'Payment Schedules — EstateFlow')
@section('page-title', 'Payment Schedules')
@section('page-subtitle', 'Equity installment schedules per reservation')
@section('content')

<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-100">
            <tr>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Client</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Property</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Scheme</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Installments</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Total Due</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Total Paid</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Status</th>
                <th class="px-5 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @forelse($reservations as $res)
            @php
                $totalDue  = $res->paymentSchedules->sum('amount_due');
                $totalPaid = $res->paymentSchedules->sum('amount_paid');
                $hasSchedule = $res->paymentSchedules->count() > 0;
            @endphp
            <tr class="hover:bg-gray-50 transition">
                <td class="px-5 py-4">
                    <p class="font-medium text-gray-800">{{ $res->client->full_name ?? '—' }}</p>
                    <p class="text-xs text-gray-400">{{ $res->client->phone ?? '' }}</p>
                </td>
                <td class="px-5 py-4">
                    <p class="font-medium text-gray-800">{{ $res->property->title ?? '—' }}</p>
                    @if($res->property?->block)
                        <p class="text-xs text-gray-400">Blk {{ $res->property->block }}, Lot {{ $res->property->lot }}</p>
                    @endif
                </td>
                <td class="px-5 py-4">
                    <span class="text-xs px-2.5 py-1 rounded-full font-medium {{ $res->payment_scheme === 'pagibig' ? 'bg-red-100 text-red-700' : 'bg-blue-100 text-blue-700' }}">
                        {{ $res->payment_scheme === 'pagibig' ? 'Pag-IBIG' : 'Cash / Bank' }}
                    </span>
                </td>
                <td class="px-5 py-4 text-gray-700">
                    {{ $res->paymentSchedules->count() }} installment(s)
                </td>
                <td class="px-5 py-4 font-medium text-gray-800">
                    {{ $hasSchedule ? '₱' . number_format($totalDue, 2) : '—' }}
                </td>
                <td class="px-5 py-4 font-medium {{ $totalPaid >= $totalDue && $hasSchedule ? 'text-green-600' : 'text-gray-800' }}">
                    {{ $hasSchedule ? '₱' . number_format($totalPaid, 2) : '—' }}
                </td>
                <td class="px-5 py-4">
                    <span class="text-xs px-2.5 py-1 rounded-full font-medium
                        {{ $res->status === 'reservation_paid' ? 'bg-indigo-100 text-indigo-700' : 'bg-blue-100 text-blue-700' }}">
                        {{ $res->status === 'reservation_paid' ? 'RF Paid' : 'Completed' }}
                    </span>
                </td>
                <td class="px-5 py-4 text-right">
                    @if($hasSchedule)
                        <a href="{{ route('finance.schedule.show', $res) }}"
                            class="text-xs bg-indigo-600 text-white px-3 py-1.5 rounded-lg hover:bg-indigo-700 transition">
                            View
                        </a>
                    @else
                        <a href="{{ route('finance.schedule.create', $res) }}"
                            class="text-xs bg-green-600 text-white px-3 py-1.5 rounded-lg hover:bg-green-700 transition">
                            Create Schedule
                        </a>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="px-5 py-16 text-center text-gray-400">
                    <i class="fas fa-calendar-alt text-4xl mb-3 block text-gray-200"></i>
                    No reservations with RF paid status yet.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    @if($reservations->hasPages())
        <div class="px-5 py-4 border-t border-gray-100">{{ $reservations->links() }}</div>
    @endif
</div>

@endsection
