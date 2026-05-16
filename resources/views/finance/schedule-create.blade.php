@extends('layouts.app')
@section('title', 'Create Payment Schedule — EstateFlow')
@section('page-title', 'Create Payment Schedule')
@section('page-subtitle', $reservation->client->full_name . ' — ' . ($reservation->property->title ?? ''))
@section('content')

<div class="max-w-4xl">

    {{-- Reservation Summary --}}
    <div class="bg-white rounded-xl shadow-sm p-5 mb-6 flex flex-wrap gap-6 text-sm">
        <div>
            <p class="text-xs text-gray-400">Property</p>
            <p class="font-semibold text-gray-800">{{ $reservation->property->title ?? '—' }}</p>
            @if($reservation->property?->block)
                <p class="text-xs text-gray-400">Blk {{ $reservation->property->block }}, Lot {{ $reservation->property->lot }}</p>
            @endif
        </div>
        <div>
            <p class="text-xs text-gray-400">Total Price</p>
            <p class="font-bold text-indigo-600">₱{{ number_format($reservation->property->price ?? 0, 2) }}</p>
        </div>
        <div>
            <p class="text-xs text-gray-400">Payment Scheme</p>
            <p class="font-medium text-gray-800">{{ $reservation->payment_scheme === 'pagibig' ? 'Pag-IBIG' : 'Cash / Bank Transfer' }}</p>
        </div>
        <div>
            <p class="text-xs text-gray-400">RF Paid</p>
            <p class="font-medium text-green-600">₱{{ number_format($reservation->reservation_fee, 2) }} — OR# {{ $reservation->rf_or_number ?? 'N/A' }}</p>
        </div>
    </div>

    @if($reservation->paymentSchedules->count())
    <div class="mb-4 bg-yellow-50 border border-yellow-200 rounded-xl p-4 text-sm text-yellow-800">
        <i class="fas fa-exclamation-triangle mr-2"></i>
        This reservation already has a schedule. Saving will <strong>replace</strong> the existing one.
    </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm p-6">
        <form method="POST" action="{{ route('finance.schedule.store', $reservation) }}">
            @csrf

            <div class="flex items-center justify-between mb-4">
                <p class="text-sm font-semibold text-gray-700">Installment Rows</p>
                <div class="flex items-center gap-3">
                    <span class="text-xs text-gray-400">Total: <span id="grandTotal" class="font-bold text-indigo-600">₱0.00</span></span>
                    <button type="button" onclick="addRow()"
                        class="text-xs bg-indigo-600 text-white px-3 py-1.5 rounded-lg hover:bg-indigo-700 transition">
                        <i class="fas fa-plus mr-1"></i> Add Row
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 rounded-lg">
                        <tr>
                            <th class="text-left px-3 py-2 text-xs font-semibold text-gray-500 w-10">#</th>
                            <th class="text-left px-3 py-2 text-xs font-semibold text-gray-500">Due Date</th>
                            <th class="text-left px-3 py-2 text-xs font-semibold text-gray-500">Amount Due (₱)</th>
                            <th class="text-left px-3 py-2 text-xs font-semibold text-gray-500">Notes</th>
                            <th class="px-3 py-2 w-10"></th>
                        </tr>
                    </thead>
                    <tbody id="scheduleBody"></tbody>
                </table>
            </div>

            <div class="mt-6 flex gap-3">
                <button type="submit"
                    class="bg-indigo-600 text-white px-6 py-2.5 rounded-lg text-sm hover:bg-indigo-700 transition font-medium">
                    <i class="fas fa-save mr-1"></i> Save Schedule
                </button>
                <a href="{{ route('finance.schedules') }}"
                    class="bg-gray-100 text-gray-600 px-6 py-2.5 rounded-lg text-sm hover:bg-gray-200 transition">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<script>
var rowCount = 0;

function addRow(dueDate, amountDue, notes) {
    dueDate    = dueDate    || '';
    amountDue  = amountDue  || '';
    notes      = notes      || '';

    var idx   = rowCount;
    var tbody = document.getElementById('scheduleBody');
    var tr    = document.createElement('tr');
    tr.className = 'border-b border-gray-100';

    // # cell
    var tdNum = document.createElement('td');
    tdNum.className = 'px-3 py-2 text-gray-400 text-xs';
    tdNum.textContent = rowCount + 1;
    tr.appendChild(tdNum);

    // Due date cell
    var tdDate = document.createElement('td');
    tdDate.className = 'px-3 py-2';
    var inputDate = document.createElement('input');
    inputDate.type = 'date';
    inputDate.name = 'installments[' + idx + '][due_date]';
    inputDate.value = dueDate;
    inputDate.required = true;
    inputDate.className = 'w-full px-2 py-1.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500';
    tdDate.appendChild(inputDate);
    tr.appendChild(tdDate);

    // Amount due cell
    var tdAmt = document.createElement('td');
    tdAmt.className = 'px-3 py-2';
    var inputAmt = document.createElement('input');
    inputAmt.type = 'number';
    inputAmt.name = 'installments[' + idx + '][amount_due]';
    inputAmt.value = amountDue;
    inputAmt.min = '1';
    inputAmt.step = '0.01';
    inputAmt.required = true;
    inputAmt.className = 'w-full px-2 py-1.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500';
    inputAmt.addEventListener('input', updateTotal);
    tdAmt.appendChild(inputAmt);
    tr.appendChild(tdAmt);

    // Notes cell
    var tdNotes = document.createElement('td');
    tdNotes.className = 'px-3 py-2';
    var inputNotes = document.createElement('input');
    inputNotes.type = 'text';
    inputNotes.name = 'installments[' + idx + '][notes]';
    inputNotes.value = notes;
    inputNotes.placeholder = 'Optional';
    inputNotes.className = 'w-full px-2 py-1.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500';
    tdNotes.appendChild(inputNotes);
    tr.appendChild(tdNotes);

    // Remove button cell
    var tdBtn = document.createElement('td');
    tdBtn.className = 'px-3 py-2';
    var btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'text-red-400 hover:text-red-600 transition';
    btn.innerHTML = '<i class="fas fa-times"></i>';
    btn.addEventListener('click', function() {
        tr.remove();
        updateTotal();
    });
    tdBtn.appendChild(btn);
    tr.appendChild(tdBtn);

    tbody.appendChild(tr);
    rowCount++;
    updateTotal();
}

function updateTotal() {
    var inputs = document.querySelectorAll('[name*="[amount_due]"]');
    var total  = 0;
    inputs.forEach(function(i) { total += parseFloat(i.value) || 0; });
    document.getElementById('grandTotal').textContent = '₱' + total.toLocaleString('en-PH', {minimumFractionDigits: 2});
}

// Pre-populate
@if($reservation->paymentSchedules->count())
    @foreach($reservation->paymentSchedules->sortBy('installment_number') as $s)
        addRow('{{ $s->due_date->format("Y-m-d") }}', '{{ $s->amount_due }}', '{{ addslashes($s->notes ?? "") }}');
    @endforeach
@else
    addRow();
@endif
</script>

@endsection
