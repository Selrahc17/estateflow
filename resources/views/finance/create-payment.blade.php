@extends('layouts.app')
@section('title', 'Record Payment — EstateFlow')
@section('page-title', 'Record Payment')
@section('page-subtitle', 'Search a unit to auto-fill client and reservation details')
@section('content')

<div class="max-w-3xl">
    <div class="bg-white rounded-xl shadow-sm p-6">
        <form method="POST" action="{{ route('finance.payments.store') }}" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="currency" value="PHP">
            <input type="hidden" name="status" value="completed">

            {{-- ── SECTION 1: Unit Search ── --}}
            <div class="mb-6">
                <p class="text-sm font-semibold text-gray-700 mb-3">
                    <span class="inline-flex items-center justify-center w-5 h-5 bg-indigo-600 text-white rounded-full text-xs mr-2">1</span>
                    Search Unit
                </p>

                <div class="relative">
                    <div class="relative">
                        <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                        <input type="text" id="unitSearch"
                            placeholder="Search by Block/Lot or Client name..."
                            autocomplete="off"
                            class="w-full pl-9 pr-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    {{-- Dropdown results --}}
                    <div id="searchResults"
                        class="hidden absolute z-50 w-full mt-1 bg-white border border-gray-200 rounded-xl shadow-lg max-h-64 overflow-y-auto">
                    </div>
                </div>
            </div>

            {{-- ── SECTION 2: Auto-filled Info ── --}}
            <div id="autoFillSection" class="hidden mb-6">
                <p class="text-sm font-semibold text-gray-700 mb-3">
                    <span class="inline-flex items-center justify-center w-5 h-5 bg-indigo-600 text-white rounded-full text-xs mr-2">2</span>
                    Reservation Details
                    <button type="button" onclick="clearSelection()"
                        class="ml-3 text-xs text-gray-400 hover:text-red-500 transition font-normal">
                        <i class="fas fa-times mr-1"></i>Clear
                    </button>
                </p>

                {{-- Hidden fields --}}
                <input type="hidden" name="reservation_id" id="f_reservation_id">
                <input type="hidden" name="client_id" id="f_client_id">
                <input type="hidden" name="agent_id" id="f_agent_id">
                <input type="hidden" name="payment_schedule_id" id="f_schedule_id">

                <div class="bg-gray-50 rounded-xl p-4 grid grid-cols-2 md:grid-cols-3 gap-4 text-sm mb-4">
                    <div>
                        <p class="text-xs text-gray-400 mb-0.5">Client</p>
                        <p class="font-semibold text-gray-800" id="d_client_name">—</p>
                        <p class="text-xs text-gray-400" id="d_client_phone"></p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 mb-0.5">Agent</p>
                        <p class="font-medium text-gray-700" id="d_agent_name">—</p>
                        <p class="text-xs text-gray-400" id="d_agent_code"></p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 mb-0.5">Property</p>
                        <p class="font-medium text-gray-700" id="d_property_title">—</p>
                        <p class="text-xs text-gray-400" id="d_block_lot"></p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 mb-0.5">Property Type</p>
                        <p class="font-medium text-gray-700" id="d_property_type">—</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 mb-0.5">Payment Scheme</p>
                        <p class="font-medium text-gray-700" id="d_scheme">—</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 mb-0.5">Reservation Status</p>
                        <p class="font-medium text-gray-700" id="d_status">—</p>
                    </div>
                </div>

                {{-- Payment Summary --}}
                <div class="grid grid-cols-3 gap-3 mb-4">
                    <div class="bg-white border border-gray-200 rounded-xl p-3 text-center">
                        <p class="text-xs text-gray-400 mb-1">Outstanding Balance</p>
                        <p class="text-lg font-bold text-red-600" id="d_balance">—</p>
                    </div>
                    <div class="bg-white border border-gray-200 rounded-xl p-3 text-center">
                        <p class="text-xs text-gray-400 mb-1">Total Paid</p>
                        <p class="text-lg font-bold text-green-600" id="d_total_paid">—</p>
                    </div>
                    <div class="bg-white border border-gray-200 rounded-xl p-3 text-center">
                        <p class="text-xs text-gray-400 mb-1">Installments</p>
                        <p class="text-lg font-bold text-indigo-600" id="d_installments">—</p>
                    </div>
                </div>

                {{-- Current Installment Due --}}
                <div id="nextInstallmentBox" class="hidden bg-yellow-50 border border-yellow-200 rounded-xl p-4 mb-4">
                    <p class="text-xs font-semibold text-yellow-800 mb-2">
                        <i class="fas fa-calendar-alt mr-1"></i> Current Installment Due
                    </p>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-sm">
                        <div>
                            <p class="text-xs text-yellow-600">Installment #</p>
                            <p class="font-semibold text-yellow-900" id="d_inst_num">—</p>
                        </div>
                        <div>
                            <p class="text-xs text-yellow-600">Due Date</p>
                            <p class="font-semibold text-yellow-900" id="d_inst_due_date">—</p>
                        </div>
                        <div>
                            <p class="text-xs text-yellow-600">Amount Due</p>
                            <p class="font-semibold text-yellow-900" id="d_inst_amount_due">—</p>
                        </div>
                        <div>
                            <p class="text-xs text-yellow-600">Balance</p>
                            <p class="font-semibold text-red-600" id="d_inst_balance">—</p>
                        </div>
                    </div>
                </div>

                <div id="noScheduleBox" class="hidden bg-blue-50 border border-blue-100 rounded-xl p-3 mb-4">
                    <p class="text-xs text-blue-700"><i class="fas fa-info-circle mr-1"></i>No payment schedule issued yet for this reservation.</p>
                </div>
            </div>

            {{-- ── SECTION 3: Payment Details ── --}}
            <div id="paymentSection" class="hidden">
                <p class="text-sm font-semibold text-gray-700 mb-3">
                    <span class="inline-flex items-center justify-center w-5 h-5 bg-indigo-600 text-white rounded-full text-xs mr-2">3</span>
                    Payment Details
                </p>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Amount Paid (₱) <span class="text-red-500">*</span></label>
                        <input type="number" name="amount" id="f_amount" min="1" step="0.01" required
                            class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Payment Date <span class="text-red-500">*</span></label>
                        <input type="date" name="payment_date" value="{{ now()->format('Y-m-d') }}" required
                            class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Payment Method <span class="text-red-500">*</span></label>
                        <select name="payment_method" required
                            class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="cash">Cash</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="gcash">GCash</option>
                            <option value="check">Check</option>
                            <option value="pagibig">Pag-IBIG</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">OR Number <span class="text-red-500">*</span></label>
                        <input type="text" name="receipt_number" placeholder="OR-XXXX" required
                            class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Reference Number <span class="text-gray-400">(bank transfer)</span></label>
                        <input type="text" name="reference_number" placeholder="Optional"
                            class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Payment Type <span class="text-red-500">*</span></label>
                        <select name="payment_type" required
                            class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="equity_installment">Equity Installment</option>
                            <option value="reservation">Reservation Fee</option>
                            <option value="downpayment">Downpayment</option>
                            <option value="full_payment">Full Payment</option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-xs font-medium text-gray-600 mb-1">Remarks / Notes</label>
                        <textarea name="description" rows="2" placeholder="Optional notes..."
                            class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 resize-none"></textarea>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-xs font-medium text-gray-600 mb-1">Proof of Payment <span class="text-gray-400">(optional)</span></label>
                        <input type="file" name="proof_image" accept="image/*,.pdf"
                            class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:bg-indigo-50 file:text-indigo-600">
                        <p class="text-xs text-gray-400 mt-1">Max 5MB — JPG, PNG, PDF</p>
                    </div>

                </div>

                <div class="mt-6 flex gap-3">
                    <button type="submit"
                        class="bg-indigo-600 text-white px-6 py-2.5 rounded-xl text-sm hover:bg-indigo-700 transition font-medium">
                        <i class="fas fa-save mr-1"></i> Save Payment
                    </button>
                    <a href="{{ route('finance.payments') }}"
                        class="bg-gray-100 text-gray-600 px-6 py-2.5 rounded-xl text-sm hover:bg-gray-200 transition">
                        Cancel
                    </a>
                </div>
            </div>

        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
const searchInput  = document.getElementById('unitSearch');
const resultsBox   = document.getElementById('searchResults');
const autoSection  = document.getElementById('autoFillSection');
const paySection   = document.getElementById('paymentSection');
let searchTimer    = null;

searchInput.addEventListener('input', function () {
    clearTimeout(searchTimer);
    const q = this.value.trim();
    if (q.length < 2) { resultsBox.classList.add('hidden'); return; }
    searchTimer = setTimeout(() => fetchResults(q), 300);
});

searchInput.addEventListener('blur', function () {
    setTimeout(() => resultsBox.classList.add('hidden'), 200);
});

searchInput.addEventListener('focus', function () {
    if (this.value.trim().length >= 2) fetchResults(this.value.trim());
});

function fetchResults(q) {
    fetch(`{{ route('finance.units.search') }}?q=${encodeURIComponent(q)}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => renderResults(data));
}

function renderResults(items) {
    if (!items.length) {
        resultsBox.innerHTML = '<div class="px-4 py-3 text-sm text-gray-400">No results found.</div>';
        resultsBox.classList.remove('hidden');
        return;
    }
    resultsBox.innerHTML = items.map(item => `
        <div class="px-4 py-3 hover:bg-indigo-50 cursor-pointer border-b border-gray-50 last:border-0 transition"
            onclick='selectUnit(${JSON.stringify(item)})'>
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-semibold text-gray-800">
                        Blk ${item.block || '?'}, Lot ${item.lot || '?'}
                        <span class="text-gray-400 font-normal">— ${item.property_title}</span>
                    </p>
                    <p class="text-xs text-gray-500 mt-0.5">
                        <i class="fas fa-user mr-1"></i>${item.client_name}
                        ${item.agent_code ? ' · <i class="fas fa-user-tie mr-1"></i>' + item.agent_code : ''}
                    </p>
                </div>
                <span class="text-xs px-2 py-0.5 rounded-full ml-3 flex-shrink-0
                    ${item.status === 'reservation_paid' ? 'bg-indigo-100 text-indigo-700' :
                      item.status === 'confirmed'        ? 'bg-green-100 text-green-700'   :
                      item.status === 'completed'        ? 'bg-blue-100 text-blue-700'     : 'bg-gray-100 text-gray-600'}">
                    ${item.status === 'reservation_paid' ? 'RF Paid' : item.status.charAt(0).toUpperCase() + item.status.slice(1)}
                </span>
            </div>
        </div>
    `).join('');
    resultsBox.classList.remove('hidden');
}

function selectUnit(item) {
    // Hide results, update search input
    resultsBox.classList.add('hidden');
    searchInput.value = item.label;

    // Hidden fields
    document.getElementById('f_reservation_id').value = item.id;
    document.getElementById('f_client_id').value       = item.client_id;
    document.getElementById('f_agent_id').value        = item.agent_id || '';

    // Display fields
    document.getElementById('d_client_name').textContent  = item.client_name;
    document.getElementById('d_client_phone').textContent = item.client_phone;
    document.getElementById('d_agent_name').textContent   = item.agent_name;
    document.getElementById('d_agent_code').textContent   = item.agent_code ? 'Code: ' + item.agent_code : '';
    document.getElementById('d_property_title').textContent = item.property_title;
    document.getElementById('d_block_lot').textContent    = item.block ? 'Blk ' + item.block + ', Lot ' + item.lot : '';
    document.getElementById('d_property_type').textContent = item.property_type;
    document.getElementById('d_scheme').textContent       = item.payment_scheme;
    document.getElementById('d_status').textContent       = item.status === 'reservation_paid' ? 'RF Paid' : item.status.charAt(0).toUpperCase() + item.status.slice(1);

    // Summary
    document.getElementById('d_balance').textContent    = '₱' + parseFloat(item.balance).toLocaleString('en-PH', {minimumFractionDigits: 2});
    document.getElementById('d_total_paid').textContent = '₱' + parseFloat(item.total_paid).toLocaleString('en-PH', {minimumFractionDigits: 2});
    document.getElementById('d_installments').textContent = item.paid_count + ' / ' + item.total_count;

    // Next installment
    if (item.next_installment) {
        const ni = item.next_installment;
        document.getElementById('f_schedule_id').value        = ni.id;
        document.getElementById('d_inst_num').textContent      = '#' + ni.number;
        document.getElementById('d_inst_due_date').textContent = ni.due_date;
        document.getElementById('d_inst_amount_due').textContent = '₱' + parseFloat(ni.amount_due).toLocaleString('en-PH', {minimumFractionDigits: 2});
        document.getElementById('d_inst_balance').textContent = '₱' + parseFloat(ni.balance).toLocaleString('en-PH', {minimumFractionDigits: 2});
        document.getElementById('nextInstallmentBox').classList.remove('hidden');
        document.getElementById('noScheduleBox').classList.add('hidden');
        // Pre-fill amount with installment balance
        document.getElementById('f_amount').value = ni.balance.toFixed(2);
    } else {
        document.getElementById('f_schedule_id').value = '';
        document.getElementById('nextInstallmentBox').classList.add('hidden');
        document.getElementById('noScheduleBox').classList.remove('hidden');
    }

    // Show sections
    autoSection.classList.remove('hidden');
    paySection.classList.remove('hidden');
}

function clearSelection() {
    searchInput.value = '';
    autoSection.classList.add('hidden');
    paySection.classList.add('hidden');
    document.getElementById('f_reservation_id').value = '';
    document.getElementById('f_client_id').value = '';
    document.getElementById('f_agent_id').value = '';
    document.getElementById('f_schedule_id').value = '';
    document.getElementById('f_amount').value = '';
}
</script>
@endpush
