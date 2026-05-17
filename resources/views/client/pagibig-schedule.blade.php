@extends('layouts.client')
@section('title', 'Pag-IBIG Amortization Schedule — EstateFlow')
@section('page-title', 'Pag-IBIG Amortization Schedule')
@section('page-subtitle', $reservation->property->title ?? '—')

@section('content')

{{-- Summary --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-xl shadow-sm p-4 text-center">
        <p class="text-xl font-bold text-gray-800">{{ $schedules->count() }}</p>
        <p class="text-xs text-gray-500 mt-1">Total Months</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-4 text-center">
        <p class="text-xl font-bold text-indigo-600">₱{{ number_format($reservation->pagibig_monthly_amortization, 2) }}</p>
        <p class="text-xs text-gray-500 mt-1">Monthly Amount</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-4 text-center">
        <p class="text-xl font-bold text-green-600">₱{{ number_format($totalPaid, 2) }}</p>
        <p class="text-xs text-gray-500 mt-1">Total Paid</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-4 text-center">
        <p class="text-xl font-bold text-red-500">₱{{ number_format($totalDue - $totalPaid, 2) }}</p>
        <p class="text-xs text-gray-500 mt-1">Remaining</p>
    </div>
</div>

{{-- Notice --}}
<div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 mb-6 flex items-start gap-3">
    <i class="fas fa-info-circle text-yellow-500 mt-0.5 flex-shrink-0"></i>
    <div class="text-sm text-yellow-800">
        <p class="font-semibold mb-1">Pay directly to Pag-IBIG (HDMF)</p>
        <p>Your monthly amortization must be paid directly to Pag-IBIG — not to EstateFlow. You can pay via Virtual Pag-IBIG, any Pag-IBIG branch, or accredited payment centers (SM, Bayad Center, etc.).</p>
    </div>
</div>

{{-- Schedule Table --}}
<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
        <h3 class="font-semibold text-gray-800">Amortization Schedule ({{ $schedules->count() }} Months / {{ round($schedules->count() / 12) }} Years)</h3>
        <span class="text-xs text-gray-400">Starts {{ \Carbon\Carbon::parse($reservation->pagibig_amortization_start)->format('M d, Y') }}</span>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-xs text-gray-500 uppercase tracking-wider">
                <tr>
                    <th class="px-6 py-3 text-left">Month</th>
                    <th class="px-6 py-3 text-left">Due Date</th>
                    <th class="px-6 py-3 text-right">Amount Due</th>
                    <th class="px-6 py-3 text-right">Amount Paid</th>
                    <th class="px-6 py-3 text-right">Balance</th>
                    <th class="px-6 py-3 text-center">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($schedules as $s)
                @php $balance = max(0, (float)$s->amount_due - (float)$s->amount_paid); @endphp
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-6 py-4 font-medium text-gray-800">Month {{ $s->month_number }}</td>
                    <td class="px-6 py-4 text-gray-600">{{ $s->due_date->format('M d, Y') }}</td>
                    <td class="px-6 py-4 text-right text-gray-800">₱{{ number_format($s->amount_due, 2) }}</td>
                    <td class="px-6 py-4 text-right text-green-600 font-medium">₱{{ number_format($s->amount_paid, 2) }}</td>
                    <td class="px-6 py-4 text-right {{ $balance > 0 ? 'text-red-500' : 'text-gray-400' }} font-medium">
                        ₱{{ number_format($balance, 2) }}
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="text-xs px-2.5 py-1 rounded-full font-medium {{ \App\Models\PagibigAmortizationSchedule::STATUS_COLORS[$s->status] ?? 'bg-gray-100 text-gray-600' }}">
                            {{ ucfirst(str_replace('_', ' ', $s->status)) }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-10 text-center text-gray-400">No schedule generated yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-4">
    <a href="{{ route('client.reservations') }}" class="text-sm text-indigo-600 hover:underline">
        <i class="fas fa-arrow-left mr-1"></i> Back to Reservations
    </a>
</div>

@endsection
