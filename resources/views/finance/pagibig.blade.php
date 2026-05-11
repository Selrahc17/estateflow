@extends('layouts.app')

@section('title', 'Pag-IBIG Ongoing Payments - EstateFlow')
@section('page-title', 'Pag-IBIG Ongoing Payments')
@section('page-subtitle', 'Track and record Pag-IBIG loan payments from HDMF')

@section('content')

@if(session('success'))
    <div class="mb-6 bg-green-50 border border-green-200 text-green-700 text-sm px-5 py-4 rounded-xl flex items-center gap-3">
        <i class="fas fa-check-circle text-green-500"></i> {{ session('success') }}
    </div>
@endif

{{-- Summary Stats --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
    <div class="bg-white rounded-xl shadow-sm p-6 flex items-center gap-4">
        <div class="w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center">
            <i class="fas fa-home text-red-600 text-xl"></i>
        </div>
        <div>
            <p class="text-sm text-gray-500">Active Pag-IBIG Loans</p>
            <p class="text-2xl font-bold text-gray-800">{{ $reservations->count() }}</p>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-6 flex items-center gap-4">
        <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
            <i class="fas fa-check-circle text-green-600 text-xl"></i>
        </div>
        <div>
            <p class="text-sm text-gray-500">Total Collected</p>
            <p class="text-2xl font-bold text-gray-800">₱{{ number_format($reservations->sum('total_paid'), 2) }}</p>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-6 flex items-center gap-4">
        <div class="w-12 h-12 bg-yellow-100 rounded-xl flex items-center justify-center">
            <i class="fas fa-clock text-yellow-600 text-xl"></i>
        </div>
        <div>
            <p class="text-sm text-gray-500">Total Remaining</p>
            <p class="text-2xl font-bold text-gray-800">₱{{ number_format($reservations->sum('remaining'), 2) }}</p>
        </div>
    </div>
</div>

{{-- Reservations Table --}}
@if($reservations->count())
<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-100">
            <tr>
                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Client</th>
                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Property</th>
                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Property Price</th>
                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Paid</th>
                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Remaining</th>
                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Payments</th>
                <th class="text-right px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Action</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @foreach($reservations as $res)
            <tr class="hover:bg-gray-50 transition">
                <td class="px-6 py-4">
                    <p class="font-medium text-gray-800">{{ $res->client->full_name ?? '—' }}</p>
                    <p class="text-xs text-gray-400">{{ $res->client->phone ?? '' }}</p>
                    @if($res->pagibig_reference)
                        <p class="text-xs text-indigo-500 mt-0.5"><i class="fas fa-hashtag mr-1"></i>{{ $res->pagibig_reference }}</p>
                    @endif
                </td>
                <td class="px-6 py-4">
                    <p class="font-medium text-gray-800">{{ $res->property->title ?? '—' }}</p>
                    <p class="text-xs text-gray-400">{{ $res->property->location ?? '' }}</p>
                </td>
                <td class="px-6 py-4 font-medium text-gray-800">
                    ₱{{ number_format($res->property->price ?? 0, 2) }}
                </td>
                <td class="px-6 py-4 font-bold text-green-600">
                    ₱{{ number_format($res->total_paid, 2) }}
                </td>
                <td class="px-6 py-4">
                    @if($res->remaining <= 0)
                        <span class="text-xs font-bold text-green-600 bg-green-50 px-2 py-1 rounded-full">
                            <i class="fas fa-check mr-1"></i>Fully Paid
                        </span>
                    @else
                        <span class="font-bold text-red-600">₱{{ number_format($res->remaining, 2) }}</span>
                    @endif
                </td>
                <td class="px-6 py-4 text-gray-600">
                    {{ $res->payments->where('payment_method', 'pagibig')->where('status', 'completed')->count() }} payment(s)
                </td>
                <td class="px-6 py-4 text-right">
                    @if($res->remaining > 0)
                        <a href="{{ route('finance.pagibig.record', $res) }}"
                            class="inline-flex items-center gap-1.5 bg-red-600 text-white text-xs px-3 py-1.5 rounded-lg hover:bg-red-700 transition font-medium">
                            <i class="fas fa-plus"></i> Record Payment
                        </a>
                    @else
                        <span class="text-xs text-gray-400">Awaiting completion</span>
                    @endif
                </td>
            </tr>

            {{-- Payment History Row --}}
            @if($res->payments->where('payment_method', 'pagibig')->count())
            <tr class="bg-gray-50">
                <td colspan="7" class="px-6 py-3">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Pag-IBIG Payment History</p>
                    <div class="flex flex-wrap gap-2">
                        @foreach($res->payments->where('payment_method', 'pagibig') as $payment)
                        <div class="bg-white border border-gray-100 rounded-lg px-3 py-2 text-xs flex items-center gap-3 shadow-sm">
                            <div>
                                <p class="font-medium text-gray-800">₱{{ number_format($payment->amount, 2) }}</p>
                                <p class="text-gray-400">{{ $payment->payment_date->format('M d, Y') }}</p>
                            </div>
                            @if($payment->reference_number)
                                <p class="text-indigo-500">Ref: {{ $payment->reference_number }}</p>
                            @endif
                            <span class="px-2 py-0.5 rounded-full {{ $payment->status === 'completed' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                                {{ ucfirst($payment->status) }}
                            </span>
                        </div>
                        @endforeach
                    </div>
                </td>
            </tr>
            @endif

            @endforeach
        </tbody>
    </table>
</div>
@else
<div class="bg-white rounded-xl shadow-sm p-16 text-center text-gray-400">
    <i class="fas fa-home text-4xl mb-3 block text-gray-200"></i>
    <p>No active Pag-IBIG loan payments at the moment.</p>
    <p class="text-xs mt-2">Reservations with approved Pag-IBIG loans will appear here.</p>
</div>
@endif

@endsection
