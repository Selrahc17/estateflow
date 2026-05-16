@extends('layouts.client')
@section('title', 'My Reservations — EstateFlow')
@section('page-title', 'My Reservations')
@section('page-subtitle', 'All your property reservations')
@section('content')

{{-- Stats --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-xl shadow-sm p-4 text-center">
        <p class="text-2xl font-bold text-gray-800">{{ $totalCount }}</p>
        <p class="text-xs text-gray-500 mt-1">Total</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-4 text-center">
        <p class="text-2xl font-bold text-yellow-600">{{ $pendingCount }}</p>
        <p class="text-xs text-gray-500 mt-1">Pending</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-4 text-center">
        <p class="text-2xl font-bold text-green-600">{{ $confirmedCount }}</p>
        <p class="text-xs text-gray-500 mt-1">Confirmed</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-4 text-center">
        <p class="text-2xl font-bold text-indigo-600">{{ $reservationPaidCount }}</p>
        <p class="text-xs text-gray-500 mt-1">RF Paid</p>
    </div>
</div>

{{-- Filter Tabs --}}
<div class="flex items-center gap-2 mb-6 flex-wrap">
    @foreach([
        ''                     => 'All',
        'pending'              => 'Pending',
        'confirmed'            => 'Confirmed',
        'reservation_paid'     => 'RF Paid',
        'pagibig_applied'      => 'Pag-IBIG Applied',
        'pagibig_approved'     => 'Pag-IBIG Approved',
        'pagibig_takeout'      => 'Pag-IBIG Takeout',
        'pagibig_amortization' => 'Pag-IBIG Amortization',
        'completed'            => 'Completed',
        'cancelled'            => 'Cancelled',
    ] as $val => $label)
        <a href="{{ route('client.reservations', $val ? ['status' => $val] : []) }}"
            class="px-4 py-2 rounded-xl text-sm font-medium transition {{ request('status', '') === $val ? 'bg-indigo-600 text-white' : 'bg-white text-gray-600 hover:bg-gray-50 shadow-sm' }}">
            {{ $label }}
        </a>
    @endforeach
</div>

@forelse($reservations as $res)
@php
    $statusColors = [
        'pending'              => 'bg-yellow-100 text-yellow-700',
        'confirmed'            => 'bg-green-100 text-green-700',
        'reservation_paid'     => 'bg-indigo-100 text-indigo-700',
        'pagibig_applied'      => 'bg-blue-100 text-blue-700',
        'pagibig_approved'     => 'bg-teal-100 text-teal-700',
        'pagibig_takeout'      => 'bg-purple-100 text-purple-700',
        'pagibig_amortization' => 'bg-pink-100 text-pink-700',
        'completed'            => 'bg-blue-100 text-blue-700',
        'cancelled'            => 'bg-red-100 text-red-700',
        'expired'              => 'bg-gray-100 text-gray-600',
    ];
    $statusLabels = [
        'pending'              => 'Pending',
        'confirmed'            => 'Confirmed',
        'reservation_paid'     => 'RF Paid',
        'pagibig_applied'      => 'Pag-IBIG Applied',
        'pagibig_approved'     => 'Pag-IBIG Approved',
        'pagibig_takeout'      => 'Pag-IBIG Takeout',
        'pagibig_amortization' => 'Pag-IBIG Amortization',
        'completed'            => 'Completed',
        'cancelled'            => 'Cancelled',
        'expired'              => 'Expired',
    ];
@endphp
<div class="bg-white rounded-xl shadow-sm mb-4 overflow-hidden">
    <div class="p-6">

        {{-- Header --}}
        <div class="flex items-start justify-between gap-4 mb-4">
            <div class="flex items-start gap-4 flex-1 min-w-0">
                <div class="w-14 h-14 bg-indigo-50 rounded-xl flex items-center justify-center flex-shrink-0 overflow-hidden">
                    @if($res->property?->image_main)
                        <img src="{{ asset($res->property->image_main) }}" class="w-14 h-14 object-cover">
                    @else
                        <i class="fas fa-building text-indigo-300 text-xl"></i>
                    @endif
                </div>
                <div class="min-w-0">
                    <p class="font-semibold text-gray-800">{{ $res->property->title ?? '—' }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">
                        @if($res->property?->block) Block {{ $res->property->block }}, @endif
                        @if($res->property?->lot) Lot {{ $res->property->lot }} @endif
                        @if($res->property?->location) · {{ $res->property->location }} @endif
                    </p>
                    <div class="flex items-center gap-3 mt-1">
                        <p class="text-sm font-bold text-indigo-600">₱{{ number_format($res->property->price ?? 0, 0) }}</p>
                        <span class="text-xs text-gray-400">{{ $res->payment_scheme === 'pagibig' ? 'Pag-IBIG' : 'Cash / Bank Transfer' }}</span>
                    </div>
                </div>
            </div>
            <span class="text-xs px-3 py-1.5 rounded-full font-medium flex-shrink-0 {{ $statusColors[$res->status] ?? 'bg-gray-100 text-gray-600' }}">
                {{ $statusLabels[$res->status] ?? ucfirst($res->status) }}
            </span>
        </div>

        {{-- Details --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-xs mb-4">
            <div class="bg-gray-50 rounded-lg p-2.5">
                <p class="text-gray-400 mb-0.5">Appointment Date</p>
                <p class="font-medium text-gray-700">{{ $res->reservation_date->format('M d, Y') }}</p>
            </div>
            <div class="bg-gray-50 rounded-lg p-2.5">
                <p class="text-gray-400 mb-0.5">Agent</p>
                <p class="font-medium text-gray-700">{{ $res->agent->agent_code ?? 'Not assigned' }}</p>
            </div>
            <div class="bg-gray-50 rounded-lg p-2.5">
                <p class="text-gray-400 mb-0.5">Reservation Fee</p>
                <p class="font-medium text-gray-700">₱{{ number_format($res->reservation_fee, 0) }}</p>
            </div>
            <div class="bg-gray-50 rounded-lg p-2.5">
                <p class="text-gray-400 mb-0.5">RF Deadline</p>
                @if($res->rf_deadline)
                    <p class="font-medium {{ $res->isRfOverdue() ? 'text-red-600' : 'text-gray-700' }}">
                        {{ $res->rf_deadline->format('M d, Y') }}
                        @if($res->isRfOverdue()) <span class="text-red-500">(Overdue)</span> @endif
                    </p>
                @else
                    <p class="text-gray-400">Not set yet</p>
                @endif
            </div>
        </div>

        {{-- ── FLOW STEPS ── --}}
        @if(!in_array($res->status, ['cancelled', 'expired']))
        <div class="mt-2">

            {{-- STEP 1: Confirmed → RF Deadline set → Pay RF --}}
            @if($res->status === 'confirmed')
                @if(!$res->rf_deadline)
                    <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 flex items-start gap-3">
                        <i class="fas fa-clock text-yellow-500 mt-0.5 flex-shrink-0"></i>
                        <div>
                            <p class="text-sm font-semibold text-yellow-800">Waiting for RF Deadline</p>
                            <p class="text-xs text-yellow-700 mt-1">Your reservation is confirmed. Your agent will set the deadline for your Reservation Fee payment.</p>
                        </div>
                    </div>
                @elseif($res->viewing_status === 'pending')
                    <div class="bg-indigo-50 border border-indigo-100 rounded-xl p-4 flex items-start gap-3">
                        <i class="fas fa-calendar-check text-indigo-400 mt-0.5 flex-shrink-0"></i>
                        <div>
                            <p class="text-sm font-semibold text-indigo-800">Site Viewing Pending</p>
                            <p class="text-xs text-indigo-700 mt-1">Your RF deadline is <strong>{{ $res->rf_deadline->format('M d, Y') }}</strong>. Please attend your site viewing first.</p>
                        </div>
                    </div>
                @elseif($res->viewing_status === 'viewed')
                    <div class="bg-green-50 border border-green-200 rounded-xl p-4">
                        <p class="text-sm font-semibold text-green-800 mb-1"><i class="fas fa-upload mr-1"></i> Upload Proof of RF Payment</p>
                        <p class="text-xs text-green-700 mb-3">
                            Viewing done! Pay your Reservation Fee and upload proof on or before
                            <strong>{{ $res->rf_deadline->format('M d, Y') }}</strong>.
                        </p>
                        <form method="POST" action="{{ route('reservations.upload-proof', $res) }}" enctype="multipart/form-data">
                            @csrf
                            <div class="flex items-center gap-3">
                                <input type="file" name="proof_of_payment" accept=".jpg,.jpeg,.png,.pdf" required
                                    class="flex-1 text-sm text-gray-600 border border-green-200 rounded-lg px-3 py-2 bg-white">
                                <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-green-700 transition font-medium whitespace-nowrap">
                                    <i class="fas fa-upload mr-1"></i> Upload
                                </button>
                            </div>
                            <p class="text-xs text-gray-400 mt-1">JPG, PNG, PDF — Max 5MB</p>
                        </form>
                    </div>
                @elseif($res->viewing_status === 'payment_uploaded')
                    <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-3 flex items-center gap-2">
                        <i class="fas fa-clock text-yellow-500"></i>
                        <p class="text-sm text-yellow-700">Proof of RF payment uploaded. Waiting for Finance to verify and issue OR.</p>
                    </div>
                @endif
            @endif

            {{-- STEP 2: RF Paid → Document Checklist --}}
            @if($res->status === 'reservation_paid')
                {{-- RF Verified Banner --}}
                <div class="bg-green-50 border border-green-200 rounded-xl p-3 flex items-center gap-3 mb-4">
                    <i class="fas fa-check-circle text-green-500 flex-shrink-0"></i>
                    <div>
                        <p class="text-sm font-semibold text-green-800">Reservation Fee Verified</p>
                        <p class="text-xs text-green-700">OR# {{ $res->rf_or_number }} · Paid {{ $res->rf_paid_at?->format('M d, Y') }}</p>
                    </div>
                    <a href="{{ route('client.schedule', $res) }}"
                        class="ml-auto text-xs bg-indigo-600 text-white px-3 py-1.5 rounded-lg hover:bg-indigo-700 transition flex-shrink-0">
                        <i class="fas fa-calendar-alt mr-1"></i> View Schedule
                    </a>
                </div>

                {{-- Document Checklist --}}
                @if($res->document_checklist)
                <div class="border border-gray-200 rounded-xl overflow-hidden">
                    <div class="bg-gray-50 px-4 py-3 flex items-center justify-between">
                        <p class="text-sm font-semibold text-gray-700"><i class="fas fa-clipboard-list mr-2 text-indigo-400"></i>Required Documents</p>
                        @php
                            $uploaded = collect($res->document_checklist)->where('uploaded', true)->count();
                            $total    = count($res->document_checklist);
                        @endphp
                        <span class="text-xs font-medium {{ $uploaded === $total ? 'text-green-600' : 'text-indigo-600' }}">
                            {{ $uploaded }}/{{ $total }} uploaded
                        </span>
                    </div>
                    <div class="divide-y divide-gray-100">
                        @foreach($res->document_checklist as $index => $item)
                        <div class="px-4 py-3 flex items-center justify-between gap-3">
                            <div class="flex items-center gap-3 flex-1 min-w-0">
                                <div class="w-7 h-7 rounded-full flex items-center justify-center flex-shrink-0
                                    {{ $item['verified'] ? 'bg-green-100' : (($item['rejected'] ?? false) ? 'bg-red-100' : (($item['not_applicable'] ?? false) ? 'bg-gray-100' : ($item['uploaded'] ? 'bg-yellow-100' : 'bg-gray-100'))) }}">
                                    @if($item['verified'])
                                        <i class="fas fa-check text-green-600 text-xs"></i>
                                    @elseif($item['rejected'] ?? false)
                                        <i class="fas fa-times text-red-600 text-xs"></i>
                                    @elseif($item['not_applicable'] ?? false)
                                        <i class="fas fa-minus text-gray-500 text-xs"></i>
                                    @elseif($item['uploaded'])
                                        <i class="fas fa-clock text-yellow-600 text-xs"></i>
                                    @else
                                        <i class="fas fa-file text-gray-400 text-xs"></i>
                                    @endif
                                </div>
                                <div class="min-w-0">
                                    <p class="text-sm text-gray-800">{{ $item['label'] }}
                                        @if($item['conditional'] ?? false)
                                            <span class="text-xs text-gray-400 ml-1">(if applicable)</span>
                                        @endif
                                    </p>
                                    @if($item['verified'])
                                        <p class="text-xs text-green-600 font-medium">Verified ✓</p>
                                    @elseif($item['rejected'] ?? false)
                                        <p class="text-xs text-red-600 font-medium">Rejected — {{ $item['rejection_reason'] }}</p>
                                    @elseif($item['not_applicable'] ?? false)
                                        <p class="text-xs text-gray-500">Marked as Not Applicable</p>
                                    @elseif($item['uploaded'])
                                        <p class="text-xs text-yellow-600">Uploaded — pending review</p>
                                    @endif
                                </div>
                            </div>
                            <div class="flex-shrink-0">
                                @if($item['verified'])
                                    <span class="text-xs bg-green-100 text-green-700 px-2.5 py-1 rounded-full">Verified</span>
                                @elseif($item['not_applicable'] ?? false)
                                    <span class="text-xs bg-gray-100 text-gray-600 px-2.5 py-1 rounded-full">N/A</span>
                                @elseif(!$item['uploaded'] || ($item['rejected'] ?? false))
                                    <div class="flex flex-col gap-1 items-end">
                                        <form method="POST"
                                            action="{{ route('client.checklist.upload', [$res, $index]) }}"
                                            enctype="multipart/form-data"
                                            class="flex items-center gap-2">
                                            @csrf
                                            <input type="file" name="document" accept=".jpg,.jpeg,.png,.pdf" required
                                                class="text-xs text-gray-500 border border-gray-200 rounded-lg px-2 py-1.5 bg-white w-32">
                                            <button type="submit"
                                                class="bg-indigo-600 text-white px-3 py-1.5 rounded-lg text-xs hover:bg-indigo-700 transition whitespace-nowrap">
                                                {{ ($item['rejected'] ?? false) ? 'Resubmit' : 'Upload' }}
                                            </button>
                                        </form>
                                        @if($item['conditional'] ?? false)
                                        <button type="button"
                                            onclick="document.getElementById('na-modal-{{ $res->id }}-{{ $index }}').classList.remove('hidden')"
                                            class="text-xs text-gray-400 hover:text-gray-600 underline">Mark as Not Applicable</button>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-xs bg-yellow-100 text-yellow-700 px-2.5 py-1 rounded-full">Under Review</span>
                                @endif
                            </div>
                        </div>

                        {{-- N/A Modal --}}
                        @if($item['conditional'] ?? false)
                        <div id="na-modal-{{ $res->id }}-{{ $index }}" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
                            <div class="bg-white rounded-xl shadow-xl p-6 w-full max-w-md mx-4">
                                <h3 class="font-semibold text-gray-800 mb-1">Mark as Not Applicable</h3>
                                <p class="text-xs text-gray-500 mb-4">"{{ $item['label'] }}" — please provide a reason.</p>
                                <form method="POST" action="{{ route('client.checklist.not-applicable', [$res, $index]) }}">
                                    @csrf
                                    <div class="mb-4">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Reason <span class="text-red-500">*</span></label>
                                        <textarea name="na_reason" rows="2" required
                                            placeholder="e.g. I am single / not married"
                                            class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 resize-none"></textarea>
                                    </div>
                                    <div class="flex gap-3">
                                        <button type="button"
                                            onclick="document.getElementById('na-modal-{{ $res->id }}-{{ $index }}').classList.add('hidden')"
                                            class="flex-1 bg-gray-100 text-gray-700 py-2 rounded-lg text-sm hover:bg-gray-200 transition">Cancel</button>
                                        <button type="submit"
                                            class="flex-1 bg-indigo-600 text-white py-2 rounded-lg text-sm hover:bg-indigo-700 transition font-medium">Confirm</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        @endif
                        @endforeach
                    </div>
                </div>
                @endif
            @endif

            {{-- STEP 3: Pag-IBIG Loan Tracking (pagibig scheme, after equity paid) --}}
            @if($res->payment_scheme === 'pagibig' && in_array($res->status, ['pagibig_applied','pagibig_approved','pagibig_takeout','pagibig_amortization']))
            @php
                $loanSteps = [
                    'applied'      => 'Application Submitted',
                    'approved'     => 'Letter of Approval Received',
                    'takeout'      => 'Takeout Processed',
                    'amortization' => 'Monthly Amortization Active',
                ];
                $loanStepKeys = array_keys($loanSteps);
                $currentLoanIdx = array_search($res->pagibig_loan_status, $loanStepKeys);
            @endphp
            <div class="bg-indigo-50 border border-indigo-100 rounded-xl p-4">
                <p class="text-sm font-semibold text-indigo-800 mb-3"><i class="fas fa-home mr-1"></i> Pag-IBIG Loan Processing</p>
                <div class="space-y-2">
                    @foreach($loanSteps as $key => $label)
                    @php
                        $stepIdx = array_search($key, $loanStepKeys);
                        $isDone  = $currentLoanIdx !== false && $stepIdx <= $currentLoanIdx;
                        $isCurrent = $currentLoanIdx !== false && $stepIdx === $currentLoanIdx;
                    @endphp
                    <div class="flex items-center gap-3">
                        <div class="w-6 h-6 rounded-full flex items-center justify-center flex-shrink-0
                            {{ $isDone ? 'bg-indigo-600' : 'bg-gray-200' }}">
                            @if($isDone)
                                <i class="fas fa-check text-white text-xs"></i>
                            @else
                                <span class="text-gray-400 text-xs">{{ $stepIdx + 1 }}</span>
                            @endif
                        </div>
                        <span class="text-xs {{ $isCurrent ? 'font-semibold text-indigo-700' : ($isDone ? 'text-indigo-600' : 'text-gray-400') }}">
                            {{ $label }}
                            @if($isCurrent) <span class="ml-1 text-indigo-400">← current</span> @endif
                        </span>
                    </div>
                    @endforeach
                </div>
                @if($res->pagibig_monthly_amortization)
                <div class="mt-3 pt-3 border-t border-indigo-100 text-xs text-indigo-700">
                    <p>Monthly amortization: <strong>₱{{ number_format($res->pagibig_monthly_amortization, 2) }}</strong></p>
                    <p class="text-indigo-500 mt-0.5">Pay directly to Pag-IBIG (HDMF) — not to Villa Rosalina.</p>
                </div>
                @endif
            </div>
            @endif

            {{-- STEP 4: Completed --}}
            @if($res->status === 'completed')
            <div class="bg-blue-50 border border-blue-200 rounded-xl p-3 flex items-center justify-between gap-2">
                <div class="flex items-center gap-2">
                    <i class="fas fa-flag-checkered text-blue-500"></i>
                    <p class="text-sm text-blue-700 font-medium">Reservation completed. Property is yours!</p>
                </div>
                <a href="{{ route('client.schedule', $res) }}"
                    class="text-xs bg-indigo-600 text-white px-3 py-1.5 rounded-lg hover:bg-indigo-700 transition flex-shrink-0">
                    <i class="fas fa-calendar-alt mr-1"></i> View Schedule
                </a>
            </div>
            @endif

        </div>
        @endif

        {{-- Site Viewing --}}
        @if(!in_array($res->status, ['cancelled', 'expired', 'reservation_paid', 'completed']))
        <div class="mt-4 pt-4 border-t border-gray-50">
            @php $viewing = $res->siteViewingSchedules->whereIn('status', ['pending','confirmed'])->first(); @endphp
            @if($viewing)
                <div class="flex items-center gap-2 text-sm">
                    <i class="fas fa-calendar-check text-indigo-400"></i>
                    <span class="text-gray-600">Site viewing <span class="font-medium">{{ ucfirst($viewing->status) }}</span>:</span>
                    <span class="text-indigo-600 font-medium">{{ $viewing->preferred_date->format('M d, Y') }}</span>
                </div>
            @elseif($res->status === 'confirmed')
                <a href="{{ route('site-viewing.create', $res) }}"
                    class="inline-flex items-center gap-2 text-sm bg-indigo-50 text-indigo-600 px-4 py-2 rounded-lg hover:bg-indigo-100 transition font-medium">
                    <i class="fas fa-map-marker-alt"></i> Schedule Site Viewing
                </a>
            @endif
        </div>
        @endif

        {{-- Payments toggle --}}
        @if($res->payments->count())
        <div class="mt-4 pt-4 border-t border-gray-50">
            <button onclick="togglePayments({{ $res->id }})"
                class="flex items-center gap-2 text-sm text-indigo-600 hover:text-indigo-800 transition font-medium">
                <i class="fas fa-receipt"></i>
                {{ $res->payments->count() }} Payment(s)
                <i class="fas fa-chevron-down text-xs transition-transform" id="chevron-{{ $res->id }}"></i>
            </button>
        </div>
        @endif

        {{-- Client Cancel (pending/confirmed only) --}}
        @if(in_array($res->status, ['pending', 'confirmed']))
        <div class="mt-4 pt-4 border-t border-gray-50">
            <button type="button"
                onclick="document.getElementById('client-cancel-{{ $res->id }}').classList.remove('hidden')"
                class="text-xs text-red-500 hover:text-red-700 transition">
                <i class="fas fa-times-circle mr-1"></i> Cancel this reservation
            </button>
        </div>
        <div id="client-cancel-{{ $res->id }}" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
            <div class="bg-white rounded-xl shadow-xl p-6 w-full max-w-md mx-4">
                <h3 class="font-semibold text-gray-800 mb-1">Cancel Reservation</h3>
                <p class="text-sm text-gray-500 mb-1">{{ $res->property->title ?? '' }}</p>
                <p class="text-xs text-red-500 mb-4">This action cannot be undone. Your data will be deleted after a 7-day grace period.</p>
                <form method="POST" action="{{ route('client.reservation.cancel', $res) }}">
                    @csrf
                    <div class="flex gap-3">
                        <button type="button"
                            onclick="document.getElementById('client-cancel-{{ $res->id }}').classList.add('hidden')"
                            class="flex-1 bg-gray-100 text-gray-700 py-2 rounded-lg text-sm hover:bg-gray-200 transition">Go Back</button>
                        <button type="submit"
                            class="flex-1 bg-red-600 text-white py-2 rounded-lg text-sm hover:bg-red-700 transition font-medium">Yes, Cancel</button>
                    </div>
                </form>
            </div>
        </div>
        @endif

        {{-- Grace period notice --}}
        @if($res->status === 'cancelled' && $res->cancelled_at && !$res->data_wiped_at)
        @php $daysLeft = max(0, now()->diffInDays($res->gracePeriodEndsAt(), false)); @endphp
        <div class="mt-4 bg-yellow-50 border border-yellow-200 rounded-xl p-3 flex items-start gap-2">
            <i class="fas fa-clock text-yellow-500 mt-0.5"></i>
            <p class="text-xs text-yellow-700">
                Data will be deleted in <strong>{{ $daysLeft }} day(s)</strong> ({{ $res->gracePeriodEndsAt()->format('M d, Y') }}).
            </p>
        </div>
        @endif

    </div>

    {{-- Payments Expandable --}}
    @if($res->payments->count())
    <div id="payments-{{ $res->id }}" class="hidden border-t border-gray-100 bg-gray-50 px-6 py-4">
        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Payment History</p>
        <div class="space-y-2">
            @foreach($res->payments as $payment)
            <div class="bg-white rounded-xl p-4 flex items-center justify-between shadow-sm">
                <div>
                    <p class="text-sm font-medium text-gray-800">{{ ucfirst(str_replace('_', ' ', $payment->payment_type)) }}</p>
                    <p class="text-xs text-gray-400">
                        {{ $payment->payment_date->format('M d, Y') }}
                        · {{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}
                        @if($payment->receipt_number) · OR# {{ $payment->receipt_number }} @endif
                    </p>
                </div>
                <div class="text-right">
                    <p class="text-sm font-bold text-gray-800">₱{{ number_format($payment->amount, 2) }}</p>
                    <span class="text-xs px-2 py-0.5 rounded-full {{ $payment->status === 'completed' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                        {{ ucfirst($payment->status) }}
                    </span>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

</div>
@empty
<div class="bg-white rounded-xl shadow-sm p-16 text-center text-gray-400">
    <i class="fas fa-calendar-check text-4xl mb-3 block text-gray-200"></i>
    @if(request('status'))
        <p>No {{ request('status') }} reservations found.</p>
        <a href="{{ route('client.reservations') }}" class="mt-4 inline-block text-indigo-600 hover:underline text-sm">View all</a>
    @else
        <p>You have no reservations yet.</p>
        <a href="{{ route('home.browse') }}" class="mt-4 inline-block bg-indigo-600 text-white px-6 py-2 rounded-lg text-sm hover:bg-indigo-700 transition">
            Browse Properties
        </a>
    @endif
</div>
@endforelse

@if(method_exists($reservations, 'hasPages') && $reservations->hasPages())
    <div class="mt-6">{{ $reservations->links() }}</div>
@endif

@endsection

@push('scripts')
<script>
function togglePayments(id) {
    const panel   = document.getElementById('payments-' + id);
    const chevron = document.getElementById('chevron-' + id);
    panel.classList.toggle('hidden');
    chevron.style.transform = panel.classList.contains('hidden') ? '' : 'rotate(180deg)';
}
</script>
@endpush
