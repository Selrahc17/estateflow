@extends('layouts.app')
@section('title', 'Payment Schedule — ' . ($reservation->property->title ?? ''))
@section('page-title', 'Payment Schedule')
@section('page-subtitle', $reservation->client->full_name . ' — ' . ($reservation->property->title ?? ''))
@section('content')

{{-- Summary --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-xl shadow-sm p-5">
        <p class="text-xs text-gray-400 mb-1">Total Due</p>
        <p class="text-xl font-bold text-gray-800">₱{{ number_format($totalDue, 2) }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-5">
        <p class="text-xs text-gray-400 mb-1">Total Paid</p>
        <p class="text-xl font-bold text-green-600">₱{{ number_format($totalPaid, 2) }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-5">
        <p class="text-xs text-gray-400 mb-1">Remaining</p>
        <p class="text-xl font-bold {{ $remaining <= 0 ? 'text-green-600' : 'text-red-600' }}">
            {{ $remaining <= 0 ? 'Fully Paid ✓' : '₱' . number_format($remaining, 2) }}
        </p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-5">
        <p class="text-xs text-gray-400 mb-1">Installments</p>
        <p class="text-xl font-bold text-gray-800">{{ $reservation->paymentSchedules->count() }}</p>
    </div>
</div>

<div class="flex items-center justify-between mb-4">
    <div></div>
    <a href="{{ route('finance.schedule.create', $reservation) }}"
        class="text-xs bg-gray-100 text-gray-600 px-4 py-2 rounded-lg hover:bg-gray-200 transition">
        <i class="fas fa-edit mr-1"></i> Edit Schedule
    </a>
</div>

{{-- Schedule Table --}}
<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-100">
            <tr>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">#</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Due Date</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Amount Due</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Amount Paid</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Balance</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">OR#</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Status</th>
                <th class="px-5 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @foreach($reservation->paymentSchedules->sortBy('installment_number') as $schedule)
            @php
                $balance = max(0, (float)$schedule->amount_due - (float)$schedule->amount_paid);
                $colors  = \App\Models\PaymentSchedule::STATUS_COLORS;
            @endphp
            <tr class="hover:bg-gray-50 transition {{ $schedule->status === 'overdue' ? 'bg-red-50' : '' }}">
                <td class="px-5 py-4 text-gray-500 font-medium">{{ $schedule->installment_number }}</td>
                <td class="px-5 py-4 {{ $schedule->status === 'overdue' ? 'text-red-600 font-medium' : 'text-gray-700' }}">
                    {{ $schedule->due_date->format('M d, Y') }}
                </td>
                <td class="px-5 py-4 font-medium text-gray-800">₱{{ number_format($schedule->amount_due, 2) }}</td>
                <td class="px-5 py-4 font-medium text-green-600">₱{{ number_format($schedule->amount_paid, 2) }}</td>
                <td class="px-5 py-4 font-medium {{ $balance <= 0 ? 'text-green-600' : 'text-red-600' }}">
                    {{ $balance <= 0 ? '—' : '₱' . number_format($balance, 2) }}
                </td>
                <td class="px-5 py-4 text-xs text-gray-500">{{ $schedule->receipt_number ?? '—' }}</td>
                <td class="px-5 py-4">
                    <span class="text-xs px-2.5 py-1 rounded-full font-medium {{ $colors[$schedule->status] ?? 'bg-gray-100 text-gray-600' }}">
                        {{ \App\Models\PaymentSchedule::STATUSES[$schedule->status] ?? ucfirst($schedule->status) }}
                    </span>
                </td>
                <td class="px-5 py-4 text-right">
                    @if($schedule->status !== 'paid')
                    <button onclick="openModal({{ $schedule->id }}, {{ $schedule->installment_number }}, {{ number_format($balance, 2, '.', '') }})"
                        class="text-xs bg-indigo-600 text-white px-3 py-1.5 rounded-lg hover:bg-indigo-700 transition">
                        Record Payment
                    </button>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

{{-- Record Payment Modal --}}
<div id="paymentModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 px-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <h2 class="font-semibold text-gray-800">Record Payment — Installment <span id="modalInstNum"></span></h2>
            <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
        </div>
        <form method="POST" id="paymentForm" enctype="multipart/form-data" class="px-6 py-5 space-y-4">
            @csrf
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Amount (₱) <span class="text-red-500">*</span></label>
                    <input type="number" name="amount" id="modalAmount" min="1" step="0.01" required
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <p class="text-xs text-gray-400 mt-0.5">Balance: <span id="modalBalance"></span></p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Payment Date <span class="text-red-500">*</span></label>
                    <input type="date" name="payment_date" value="{{ now()->format('Y-m-d') }}" required
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">OR Number <span class="text-red-500">*</span></label>
                    <input type="text" name="receipt_number" placeholder="OR-XXXX" required
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Reference No.</label>
                    <input type="text" name="reference_number" placeholder="Optional"
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Payment Method <span class="text-red-500">*</span></label>
                <select name="payment_method" required
                    class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="cash">Cash</option>
                    <option value="bank_transfer">Bank Transfer</option>
                    <option value="check">Check</option>
                    <option value="pagibig">Pag-IBIG</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Proof of Payment</label>
                <input type="file" name="proof_image" accept=".jpg,.jpeg,.png,.pdf"
                    class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm file:mr-2 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:bg-indigo-50 file:text-indigo-600">
            </div>
            <div class="flex gap-3 pt-2">
                <button type="button" onclick="closeModal()"
                    class="flex-1 px-4 py-2 bg-gray-100 text-gray-600 rounded-lg text-sm hover:bg-gray-200 transition">Cancel</button>
                <button type="submit"
                    class="flex-1 px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm hover:bg-indigo-700 transition font-medium">
                    <i class="fas fa-save mr-1"></i> Record
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
function openModal(scheduleId, instNum, balance) {
    document.getElementById('modalInstNum').textContent = instNum;
    document.getElementById('modalAmount').value = balance.toFixed(2);
    document.getElementById('modalBalance').textContent = '₱' + balance.toLocaleString('en-PH', {minimumFractionDigits: 2});
    document.getElementById('paymentForm').action = '/finance/schedule-payment/' + scheduleId;
    document.getElementById('paymentModal').classList.remove('hidden');
}
function closeModal() {
    document.getElementById('paymentModal').classList.add('hidden');
}
</script>
@endpush
