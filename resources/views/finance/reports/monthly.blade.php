@extends('layouts.app')
@section('title', 'Monthly Collection Report — EstateFlow')
@section('page-title', 'Monthly Collection Report')
@section('page-subtitle', 'Collections breakdown by month and payment method')

@section('content')

{{-- Year Filter --}}
<div class="bg-white rounded-xl shadow-sm p-4 mb-6">
    <form method="GET" class="flex items-end gap-3">
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Year</label>
            <select name="year" class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                @foreach($years as $y)
                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-indigo-700 transition">View</button>
    </form>
</div>

{{-- Year Total --}}
<div class="bg-gradient-to-r from-indigo-600 to-purple-600 rounded-xl p-6 mb-6 text-white">
    <p class="text-sm opacity-80">Total Collections — {{ $year }}</p>
    <p class="text-3xl font-bold mt-1">₱{{ number_format($yearTotal, 2) }}</p>
</div>

{{-- Monthly Table --}}
<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-xs text-gray-500 uppercase tracking-wider">
            <tr>
                <th class="px-6 py-3 text-left">Month</th>
                <th class="px-6 py-3 text-right">Transactions</th>
                <th class="px-6 py-3 text-right">Cash</th>
                <th class="px-6 py-3 text-right">Bank Transfer</th>
                <th class="px-6 py-3 text-right">Pag-IBIG</th>
                <th class="px-6 py-3 text-right font-semibold text-gray-700">Total</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @foreach($months as $m)
            <tr class="hover:bg-gray-50 transition {{ $m['total'] > 0 ? '' : 'opacity-40' }}">
                <td class="px-6 py-4 font-medium text-gray-800">{{ $m['month'] }}</td>
                <td class="px-6 py-4 text-right text-gray-600">{{ $m['count'] }}</td>
                <td class="px-6 py-4 text-right text-gray-600">{{ $m['cash'] > 0 ? '₱'.number_format($m['cash'], 2) : '—' }}</td>
                <td class="px-6 py-4 text-right text-gray-600">{{ $m['bank'] > 0 ? '₱'.number_format($m['bank'], 2) : '—' }}</td>
                <td class="px-6 py-4 text-right text-gray-600">{{ $m['pagibig'] > 0 ? '₱'.number_format($m['pagibig'], 2) : '—' }}</td>
                <td class="px-6 py-4 text-right font-bold {{ $m['total'] > 0 ? 'text-indigo-600' : 'text-gray-300' }}">
                    {{ $m['total'] > 0 ? '₱'.number_format($m['total'], 2) : '—' }}
                </td>
            </tr>
            @endforeach
        </tbody>
        <tfoot class="bg-gray-50 border-t-2 border-gray-200">
            <tr>
                <td class="px-6 py-4 font-bold text-gray-800">TOTAL</td>
                <td class="px-6 py-4 text-right font-bold text-gray-800">{{ collect($months)->sum('count') }}</td>
                <td class="px-6 py-4 text-right font-bold text-gray-700">₱{{ number_format(collect($months)->sum('cash'), 2) }}</td>
                <td class="px-6 py-4 text-right font-bold text-gray-700">₱{{ number_format(collect($months)->sum('bank'), 2) }}</td>
                <td class="px-6 py-4 text-right font-bold text-gray-700">₱{{ number_format(collect($months)->sum('pagibig'), 2) }}</td>
                <td class="px-6 py-4 text-right font-bold text-indigo-700 text-base">₱{{ number_format($yearTotal, 2) }}</td>
            </tr>
        </tfoot>
    </table>
</div>

@endsection
