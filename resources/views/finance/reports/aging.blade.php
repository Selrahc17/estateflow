@extends('layouts.app')
@section('title', 'Aging Report — EstateFlow')
@section('page-title', 'Aging Report')
@section('page-subtitle', 'Overdue equity installments and Pag-IBIG amortizations')

@section('content')

{{-- Summary --}}
<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
    <div class="bg-red-50 border border-red-200 rounded-xl p-5 flex items-center gap-4">
        <div class="w-11 h-11 bg-red-100 rounded-xl flex items-center justify-center flex-shrink-0">
            <i class="fas fa-exclamation-triangle text-red-600"></i>
        </div>
        <div>
            <p class="text-xs text-red-600 font-medium">Overdue Equity Installments</p>
            <p class="text-xl font-bold text-red-700">₱{{ number_format($totalOverdueEquity, 2) }}</p>
            <p class="text-xs text-red-500">{{ $overdue->count() }} installment(s)</p>
        </div>
    </div>
    <div class="bg-orange-50 border border-orange-200 rounded-xl p-5 flex items-center gap-4">
        <div class="w-11 h-11 bg-orange-100 rounded-xl flex items-center justify-center flex-shrink-0">
            <i class="fas fa-home text-orange-600"></i>
        </div>
        <div>
            <p class="text-xs text-orange-600 font-medium">Overdue Pag-IBIG Amortizations</p>
            <p class="text-xl font-bold text-orange-700">₱{{ number_format($totalOverduePagibig, 2) }}</p>
            <p class="text-xs text-orange-500">{{ $pagibigOverdue->count() }} month(s)</p>
        </div>
    </div>
</div>

{{-- Overdue Equity Installments --}}
<div class="bg-white rounded-xl shadow-sm overflow-hidden mb-6">
    <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-2">
        <i class="fas fa-calendar-times text-red-500"></i>
        <h3 class="font-semibold text-gray-800">Overdue Equity Installments</h3>
        <span class="ml-auto text-xs bg-red-100 text-red-700 px-2.5 py-1 rounded-full font-medium">{{ $overdue->count() }}</span>
    </div>
    @if($overdue->count())
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-xs text-gray-500 uppercase tracking-wider">
                <tr>
                    <th class="px-6 py-3 text-left">Client</th>
                    <th class="px-6 py-3 text-left">Property</th>
                    <th class="px-6 py-3 text-center">Installment #</th>
                    <th class="px-6 py-3 text-right">Due Date</th>
                    <th class="px-6 py-3 text-right">Balance</th>
                    <th class="px-6 py-3 text-center">Days Overdue</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($overdue as $s)
                <tr class="hover:bg-red-50 transition">
                    <td class="px-6 py-4">
                        <p class="font-medium text-gray-800">{{ $s->reservation->client->full_name ?? '—' }}</p>
                        <p class="text-xs text-gray-400">{{ $s->reservation->client->phone ?? '' }}</p>
                    </td>
                    <td class="px-6 py-4 text-gray-700">{{ $s->reservation->property->title ?? '—' }}</td>
                    <td class="px-6 py-4 text-center text-gray-700">#{{ $s->installment_number }}</td>
                    <td class="px-6 py-4 text-right text-gray-700">{{ $s->due_date->format('M d, Y') }}</td>
                    <td class="px-6 py-4 text-right font-bold text-red-600">₱{{ number_format($s->balance, 2) }}</td>
                    <td class="px-6 py-4 text-center">
                        <span class="text-xs font-bold px-2.5 py-1 rounded-full
                            {{ $s->days_overdue > 30 ? 'bg-red-100 text-red-700' : 'bg-orange-100 text-orange-700' }}">
                            {{ $s->days_overdue }} days
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="px-6 py-10 text-center text-gray-400">
        <i class="fas fa-check-circle text-3xl mb-2 block text-green-300"></i>
        No overdue equity installments.
    </div>
    @endif
</div>

{{-- Overdue Pag-IBIG Amortizations --}}
<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-2">
        <i class="fas fa-home text-orange-500"></i>
        <h3 class="font-semibold text-gray-800">Overdue Pag-IBIG Amortizations</h3>
        <span class="ml-auto text-xs bg-orange-100 text-orange-700 px-2.5 py-1 rounded-full font-medium">{{ $pagibigOverdue->count() }}</span>
    </div>
    @if($pagibigOverdue->count())
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-xs text-gray-500 uppercase tracking-wider">
                <tr>
                    <th class="px-6 py-3 text-left">Client</th>
                    <th class="px-6 py-3 text-left">Property</th>
                    <th class="px-6 py-3 text-center">Month #</th>
                    <th class="px-6 py-3 text-right">Due Date</th>
                    <th class="px-6 py-3 text-right">Balance</th>
                    <th class="px-6 py-3 text-center">Days Overdue</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($pagibigOverdue as $s)
                <tr class="hover:bg-orange-50 transition">
                    <td class="px-6 py-4">
                        <p class="font-medium text-gray-800">{{ $s->reservation->client->full_name ?? '—' }}</p>
                        <p class="text-xs text-gray-400">{{ $s->reservation->client->phone ?? '' }}</p>
                    </td>
                    <td class="px-6 py-4 text-gray-700">{{ $s->reservation->property->title ?? '—' }}</td>
                    <td class="px-6 py-4 text-center text-gray-700">Month {{ $s->month_number }}</td>
                    <td class="px-6 py-4 text-right text-gray-700">{{ $s->due_date->format('M d, Y') }}</td>
                    <td class="px-6 py-4 text-right font-bold text-orange-600">₱{{ number_format($s->balance, 2) }}</td>
                    <td class="px-6 py-4 text-center">
                        <span class="text-xs font-bold px-2.5 py-1 rounded-full
                            {{ $s->days_overdue > 30 ? 'bg-red-100 text-red-700' : 'bg-orange-100 text-orange-700' }}">
                            {{ $s->days_overdue }} days
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="px-6 py-10 text-center text-gray-400">
        <i class="fas fa-check-circle text-3xl mb-2 block text-green-300"></i>
        No overdue Pag-IBIG amortizations.
    </div>
    @endif
</div>

@endsection
