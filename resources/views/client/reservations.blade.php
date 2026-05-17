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
                    <div class="bg-gray-50 px-4 py-3 border-b border-gray-100">
                        <p class="text-sm font-semibold text-gray-700"><i class="fas fa-clipboard-list mr-1 text-indigo-400"></i> Required Documents</p>
                    </div>
                    <div class="divide-y divide-gray-50">
                        @foreach($res->document_checklist as $index => $item)
                        <div class="p-4">
                            <div class="flex items-center justify-between gap-3 mb-2">
                                <div class="flex items-center gap-2 flex-1 min-w-0">
                                    @if($item['verified'] ?? false)
                                        <i class="fas fa-check-circle text-green-500 flex-shrink-0"></i>
                                    @elseif($item['rejected'] ?? false)
                                        <i class="fas fa-times-circle text-red-500 flex-shrink-0"></i>
                                    @elseif($item['not_applicable'] ?? false)
                                        <i class="fas fa-minus-circle text-gray-400 flex-shrink-0"></i>
                                    @elseif($item['uploaded'] ?? false)
                                        <i class="fas fa-clock text-yellow-500 flex-shrink-0"></i>
                                    @else
                                        <i class="fas fa-circle text-gray-300 flex-shrink-0"></i>
                                    @endif
                                    <span class="text-sm text-gray-700 truncate">{{ $item['label'] }}</span>
                                </div>
                                <span class="text-xs px-2.5 py-1 rounded-full font-medium flex-shrink-0
                                    {{ ($item['verified'] ?? false) ? 'bg-green-100 text-green-700' : '' }}
                                    {{ ($item['rejected'] ?? false) ? 'bg-red-100 text-red-700' : '' }}
                                    {{ ($item['not_applicable'] ?? false) ? 'bg-gray-100 text-gray-600' : '' }}
                                    {{ (($item['uploaded'] ?? false) && !($item['verified'] ?? false) && !($item['rejected'] ?? false)) ? 'bg-yellow-100 text-yellow-700' : '' }}
                                    {{ (!($item['uploaded'] ?? false) && !($item['verified'] ?? false) && !($item['rejected'] ?? false) && !($item['not_applicable'] ?? false)) ? 'bg-gray-100 text-gray-500' : '' }}">
                                    @if($item['verified'] ?? false) Verified
                                    @elseif($item['rejected'] ?? false) Rejected
                                    @elseif($item['not_applicable'] ?? false) N/A
                                    @elseif($item['uploaded'] ?? false) Under Review
                                    @else Not Uploaded
                                    @endif
                                </span>
                            </div>

                            {{-- Rejection reason + resubmit --}}
                            @if($item['rejected'] ?? false)
                            <div class="bg-red-50 border border-red-200 rounded-lg p-3 mt-2">
                                <p class="text-xs text-red-700 mb-2">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    <strong>Reason:</strong> {{ $item['rejection_reason'] ?? 'No reason provided.' }}
                                </p>
                                <form method="POST"
                                    action="{{ route('reservations.checklist.upload', [$res, $index]) }}"
                                    enctype="multipart/form-data"
                                    class="flex items-center gap-2">
                                    @csrf
                                    <input type="file" name="document" accept=".jpg,.jpeg,.png,.pdf" required
                                        class="flex-1 text-xs text-gray-600 border border-red-200 rounded-lg px-2 py-1.5 bg-white">
                                    <button type="submit"
                                        class="bg-red-600 text-white px-3 py-1.5 rounded-lg text-xs hover:bg-red-700 transition font-medium whitespace-nowrap">
                                        <i class="fas fa-redo mr-1"></i> Resubmit
                                    </button>
                                </form>
                                <p class="text-xs text-gray-400 mt-1">JPG, PNG, PDF — Max 10MB</p>
                            </div>
                            @endif

                            {{-- Upload (not yet uploaded) --}}
                            @if(!($item['uploaded'] ?? false) && !($item['verified'] ?? false) && !($item['rejected'] ?? false) && !($item['not_applicable'] ?? false))
                            <form method="POST"
                                action="{{ route('reservations.checklist.upload', [$res, $index]) }}"
                                enctype="multipart/form-data"
                                class="flex items-center gap-2 mt-2">
                                @csrf
                                <input type="file" name="document" accept=".jpg,.jpeg,.png,.pdf" required
                                    class="flex-1 text-xs text-gray-600 border border-gray-200 rounded-lg px-2 py-1.5 bg-white">
                                <button type="submit"
                                    class="bg-indigo-600 text-white px-3 py-1.5 rounded-lg text-xs hover:bg-indigo-700 transition font-medium whitespace-nowrap">
                                    <i class="fas fa-upload mr-1"></i> Upload
                                </button>
                            </form>
                            <p class="text-xs text-gray-400 mt-1">JPG, PNG, PDF — Max 10MB</p>
                            @endif
                        </div>
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
                    <a href="{{ route('client.pagibig-schedule', $res) }}"
                        class="mt-2 inline-flex items-center gap-1 bg-indigo-600 text-white px-3 py-1.5 rounded-lg hover:bg-indigo-700 transition">
                        <i class="fas fa-calendar-alt"></i> View Amortization Schedule
                    </a>
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
