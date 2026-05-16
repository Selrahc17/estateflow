@extends('layouts.app')

@section('title', 'Reservation Details - EstateFlow')
@section('page-title', 'Reservation Details')
@section('page-subtitle', $reservation->property->title ?? '')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Left: Reservation Info --}}
    <div class="lg:col-span-1 space-y-6">

        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-gray-800">Reservation Info</h3>
@php
                    $statusBadgeColors = [
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
                    $statusBadgeLabels = [
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
                <span class="text-xs px-2.5 py-1 rounded-full font-medium {{ $statusBadgeColors[$reservation->status] ?? 'bg-gray-100 text-gray-600' }}">
                    {{ $statusBadgeLabels[$reservation->status] ?? ucfirst(str_replace('_', ' ', $reservation->status)) }}
                </span>
            </div>

            <div class="space-y-3 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-500">Reservation Date</span>
                    <span class="font-medium text-gray-800">{{ $reservation->reservation_date->format('M d, Y') }}</span>
                </div>
                @if($reservation->expiry_date)
                <div class="flex justify-between">
                    <span class="text-gray-500">Expiry Date</span>
                    <span class="font-medium text-gray-800">{{ $reservation->expiry_date->format('M d, Y') }}</span>
                </div>
                @endif
                <div class="flex justify-between">
                    <span class="text-gray-500">Reservation Fee</span>
                    <span class="font-bold text-indigo-600">₱{{ number_format($reservation->reservation_fee, 2) }}</span>
                </div>
            </div>

            @if(auth()->user()->isAdmin())
            <div class="mt-6 space-y-2">
                @php
                    $totalPaid   = $reservation->payments->where('status', 'completed')->sum('amount');
                    $propertyPrice = $reservation->property->price ?? 0;
                    $remaining   = $propertyPrice - $totalPaid;
                @endphp

                {{-- Mark as Viewed --}}
                @if(in_array($reservation->viewing_status ?? 'pending', ['pending']) && !in_array($reservation->status, ['cancelled','expired','completed']))
                <form method="POST" action="{{ route('reservations.mark-viewed', $reservation) }}">
                    @csrf @method('PATCH')
                    <button type="submit" class="w-full bg-purple-600 text-white py-2 rounded-lg text-sm hover:bg-purple-700 transition font-medium">
                        <i class="fas fa-eye mr-1"></i> Mark Appointment as Viewed
                    </button>
                </form>
                @endif

                <a href="{{ route('reservations.edit', $reservation) }}" class="block text-center bg-indigo-600 text-white py-2 rounded-lg text-sm hover:bg-indigo-700 transition">
                    <i class="fas fa-edit mr-1"></i> Edit Reservation
                </a>

                @if(in_array($reservation->status, ['confirmed', 'reservation_paid', 'pagibig_applied', 'pagibig_approved', 'pagibig_takeout', 'pagibig_amortization']))
                    @php
                        $canComplete = $reservation->payment_scheme === 'pagibig'
                            ? $reservation->pagibig_loan_status === 'amortization'
                            : $remaining <= 0;
                    @endphp
                    @if($canComplete)
                    <form method="POST" action="{{ route('reservations.update-status', $reservation) }}"
                        onsubmit="return confirm('Mark this reservation as completed? This will mark the property as sold.')">
                        @csrf @method('PATCH')
                        <input type="hidden" name="status" value="completed">
                        <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded-lg text-sm hover:bg-blue-700 transition font-medium">
                            <i class="fas fa-flag-checkered mr-1"></i> Mark as Completed
                        </button>
                    </form>
                    @else
                    <div class="w-full bg-gray-100 text-gray-400 py-2 rounded-lg text-sm text-center cursor-not-allowed"
                        title="{{ $reservation->payment_scheme === 'pagibig' ? 'Complete all Pag-IBIG stages first' : 'Balance must be ₱0.00 first' }}">
                        <i class="fas fa-flag-checkered mr-1"></i> Mark as Completed
                        <p class="text-xs mt-0.5">{{ $reservation->payment_scheme === 'pagibig' ? 'Complete Pag-IBIG stages first' : 'Balance must be ₱0.00 first' }}</p>
                    </div>
                    @endif

                    <button type="button" onclick="document.getElementById('cancel-modal').classList.remove('hidden')"
                        class="w-full bg-red-50 text-red-600 py-2 rounded-lg text-sm hover:bg-red-100 transition font-medium">
                        <i class="fas fa-times mr-1"></i> Cancel Reservation
                    </button>
                @endif

                @if($reservation->status === 'pending')
                    <form method="POST" action="{{ route('reservations.update-status', $reservation) }}">
                        @csrf @method('PATCH')
                        <input type="hidden" name="status" value="confirmed">
                        <button type="submit" class="w-full bg-green-600 text-white py-2 rounded-lg text-sm hover:bg-green-700 transition font-medium">
                            <i class="fas fa-check mr-1"></i> Confirm Reservation
                        </button>
                    </form>
                    <button type="button" onclick="document.getElementById('cancel-modal').classList.remove('hidden')"
                        class="w-full bg-red-50 text-red-600 py-2 rounded-lg text-sm hover:bg-red-100 transition font-medium">
                        <i class="fas fa-times mr-1"></i> Cancel Reservation
                    </button>
                @endif

                <form method="POST" action="{{ route('reservations.destroy', $reservation) }}"
                    onsubmit="return confirm('Delete this reservation?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="w-full px-4 py-2 bg-red-50 text-red-600 rounded-lg text-sm hover:bg-red-100 transition">
                        <i class="fas fa-trash mr-1"></i> Delete
                    </button>
                </form>
            </div>
            @endif

            @if(auth()->user()->isAgent())
            <div class="mt-6">
                <p class="text-xs text-gray-400 text-center py-2"><i class="fas fa-info-circle mr-1"></i>Status changes are managed by admin.</p>
            </div>
            @endif
        </div>

        {{-- Property --}}
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="font-semibold text-gray-800 mb-3 text-sm">Property</h3>
            @if($reservation->property)
                <p class="font-medium text-gray-800">{{ $reservation->property->title }}</p>
                <p class="text-xs text-gray-400 mt-1"><i class="fas fa-map-marker-alt mr-1"></i>{{ $reservation->property->location }}</p>
                <p class="text-sm font-bold text-indigo-600 mt-2">₱{{ number_format($reservation->property->price, 2) }}</p>
                <a href="{{ route('properties.show', $reservation->property) }}" class="mt-3 block text-center text-xs bg-gray-50 text-gray-600 py-2 rounded-lg hover:bg-gray-100 transition">
                    View Property
                </a>
            @endif
        </div>

        {{-- Client --}}
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="font-semibold text-gray-800 mb-3 text-sm">Client</h3>
            @if($reservation->client)
                <p class="font-medium text-gray-800">{{ $reservation->client->full_name }}</p>
                <p class="text-xs text-gray-400 mt-1">{{ $reservation->client->phone }}</p>
                <p class="text-xs text-gray-400">{{ $reservation->client->email }}</p>
                <a href="{{ route('clients.show', $reservation->client) }}" class="mt-3 block text-center text-xs bg-gray-50 text-gray-600 py-2 rounded-lg hover:bg-gray-100 transition">
                    View Client
                </a>
            @endif
        </div>

        {{-- Pag-IBIG Loan Tracking --}}
        @if($reservation->payment_scheme === 'pagibig')
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-gray-800 text-sm">Pag-IBIG Loan Tracking</h3>
                @php
                    $loanStatusColors = [
                        null           => 'bg-gray-100 text-gray-500',
                        'applied'      => 'bg-blue-100 text-blue-700',
                        'approved'     => 'bg-green-100 text-green-700',
                        'takeout'      => 'bg-indigo-100 text-indigo-700',
                        'amortization' => 'bg-purple-100 text-purple-700',
                    ];
                    $loanStatusLabels = [
                        null           => 'Pending Application',
                        'applied'      => 'Application Submitted',
                        'approved'     => 'Letter of Approval Received',
                        'takeout'      => 'Takeout Processed',
                        'amortization' => 'Monthly Amortization Active',
                    ];
                    $currentLoanStatus = $reservation->pagibig_loan_status;
                @endphp
                <span class="text-xs px-2.5 py-1 rounded-full font-medium {{ $loanStatusColors[$currentLoanStatus] ?? 'bg-gray-100 text-gray-500' }}">
                    {{ $loanStatusLabels[$currentLoanStatus] ?? 'Pending Application' }}
                </span>
            </div>

            {{-- Progress Steps --}}
            @php
                $loanSteps = ['applied', 'approved', 'takeout', 'amortization'];
                $loanStepLabels = ['Applied', 'LOA', 'Takeout', 'Amortization'];
                $currentLoanIndex = array_search($currentLoanStatus, $loanSteps);
            @endphp
            <div class="flex items-center mb-5">
                @foreach($loanSteps as $i => $step)
                    @php $done = $currentLoanIndex !== false && $i <= $currentLoanIndex; @endphp
                    <div class="flex flex-col items-center flex-1">
                        <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold {{ $done ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-400' }}">
                            @if($done)<i class="fas fa-check text-xs"></i>@else{{ $i + 1 }}@endif
                        </div>
                        <p class="text-xs mt-1 text-center {{ $done ? 'text-indigo-600 font-medium' : 'text-gray-400' }}">{{ $loanStepLabels[$i] }}</p>
                    </div>
                    @if($i < count($loanSteps) - 1)
                        <div class="flex-1 h-0.5 {{ ($currentLoanIndex !== false && $i < $currentLoanIndex) ? 'bg-indigo-600' : 'bg-gray-200' }} mb-4"></div>
                    @endif
                @endforeach
            </div>

            {{-- Loan Details --}}
            @if($reservation->pagibig_applied_at)
            <div class="text-xs text-gray-500 space-y-1 mb-4">
                <p><span class="font-medium text-gray-700">Applied:</span> {{ $reservation->pagibig_applied_at->format('M d, Y') }}</p>
                @if($reservation->pagibig_loa_number)
                <p><span class="font-medium text-gray-700">LOA#:</span> {{ $reservation->pagibig_loa_number }} · {{ $reservation->pagibig_approved_at?->format('M d, Y') }}</p>
                @endif
                @if($reservation->pagibig_takeout_amount)
                <p><span class="font-medium text-gray-700">Takeout:</span> ₱{{ number_format($reservation->pagibig_takeout_amount, 2) }} · {{ $reservation->pagibig_takeout_at?->format('M d, Y') }}</p>
                @endif
                @if($reservation->pagibig_monthly_amortization)
                <p><span class="font-medium text-gray-700">Monthly:</span> ₱{{ number_format($reservation->pagibig_monthly_amortization, 2) }} starting {{ $reservation->pagibig_amortization_start?->format('M d, Y') }}</p>
                @endif
            </div>
            @endif

            {{-- Action Buttons (Admin/Finance) --}}
            @if(auth()->user()->isAdmin() || auth()->user()->isFinance())

                {{-- Step 1: Submit Application --}}
                @if(!$currentLoanStatus && $reservation->isEquityFullyPaid())
                <form method="POST" action="{{ route('finance.pagibig.apply', $reservation) }}">
                    @csrf
                    <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded-lg text-sm hover:bg-blue-700 transition font-medium">
                        <i class="fas fa-paper-plane mr-1"></i> Submit Pag-IBIG Application
                    </button>
                </form>
                @elseif(!$currentLoanStatus)
                <p class="text-xs text-gray-400 text-center"><i class="fas fa-lock mr-1"></i>Available after all equity installments are paid.</p>
                @endif

                {{-- Step 2: Record LOA --}}
                @if($currentLoanStatus === 'applied')
                <form method="POST" action="{{ route('finance.pagibig.loa', $reservation) }}" class="space-y-2">
                    @csrf
                    <label class="block text-xs font-medium text-gray-600">LOA Number from HDMF</label>
                    <div class="flex gap-2">
                        <input type="text" name="pagibig_loa_number" placeholder="e.g. HDMF-LOA-2024-XXXXX" required
                            class="flex-1 px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-green-700 transition font-medium whitespace-nowrap">
                            Record LOA
                        </button>
                    </div>
                </form>
                @endif

                {{-- Step 3: Record Takeout --}}
                @if($currentLoanStatus === 'approved')
                <form method="POST" action="{{ route('finance.pagibig.takeout', $reservation) }}" class="space-y-2">
                    @csrf
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Takeout Amount (₱)</label>
                        <input type="number" name="pagibig_takeout_amount" step="0.01" min="1" required
                            class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Takeout Date</label>
                        <input type="date" name="pagibig_takeout_at" required
                            class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <button type="submit" class="w-full bg-indigo-600 text-white py-2 rounded-lg text-sm hover:bg-indigo-700 transition font-medium">
                        <i class="fas fa-check mr-1"></i> Record Takeout
                    </button>
                </form>
                @endif

                {{-- Step 4: Start Amortization --}}
                @if($currentLoanStatus === 'takeout')
                <form method="POST" action="{{ route('finance.pagibig.amortization', $reservation) }}" class="space-y-2">
                    @csrf
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Monthly Amortization (₱)</label>
                        <input type="number" name="pagibig_monthly_amortization" step="0.01" min="1" required
                            class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Amortization Start Date</label>
                        <input type="date" name="pagibig_amortization_start" required
                            class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <button type="submit" class="w-full bg-purple-600 text-white py-2 rounded-lg text-sm hover:bg-purple-700 transition font-medium">
                        <i class="fas fa-calendar-alt mr-1"></i> Start Amortization
                    </button>
                </form>
                @endif

            @endif
        </div>
        @endif

        {{-- Document Checklist --}}
        @php
            $checklistDocuments = $reservation->documents()->whereNotNull('checklist_key')->latest()->get()->keyBy('checklist_key');
        @endphp
        @if($reservation->document_checklist)
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="font-semibold text-gray-800 text-sm mb-4"><i class="fas fa-clipboard-list mr-1 text-indigo-400"></i> Required Documents</h3>
            <div class="space-y-3">
                @foreach($reservation->document_checklist as $item)
                @php
                    $key = $item['key'];
                    $doc = $checklistDocuments->get($key);
                    $docStatus = $doc?->checklist_status;
                @endphp
                <div class="border border-gray-100 rounded-xl p-3">
                    <div class="flex items-center justify-between gap-3">
                        <div class="flex items-center gap-2 flex-1 min-w-0">
                            <i class="fas {{ $docStatus === 'approved' ? 'fa-check-circle text-green-500' : ($docStatus === 'rejected' ? 'fa-times-circle text-red-500' : ($docStatus ? 'fa-clock text-yellow-500' : 'fa-circle text-gray-300')) }} text-sm flex-shrink-0"></i>
                            <span class="text-sm text-gray-700 truncate">{{ $item['label'] }}</span>
                        </div>
                        <div class="flex items-center gap-2 flex-shrink-0">
                            @if($doc && $doc->file_path)
                                <button type="button"
                                    onclick="openPreview({!! Js::from(asset('storage/' . $doc->file_path)) !!}, {!! Js::from($doc->file_type) !!}, {!! Js::from($doc->title) !!})"
                                    class="text-xs text-indigo-600 hover:underline">
                                    <i class="fas fa-eye mr-1"></i>View
                                </button>
                            @endif
                            @if(auth()->user()->isAdmin())
                                @if($docStatus === 'submitted' || $docStatus === 'resubmitted')
                                <form method="POST" action="{{ route('documents.checklist.verify', [$reservation, $key]) }}">
                                    @csrf @method('PATCH')
                                    <button class="text-xs bg-green-600 text-white px-3 py-1 rounded-lg hover:bg-green-700 transition">Approve</button>
                                </form>
                                <button type="button"
                                    onclick="document.getElementById('reject-doc-{{ $key }}').classList.remove('hidden')"
                                    class="text-xs bg-red-50 text-red-600 px-3 py-1 rounded-lg hover:bg-red-100 transition">Reject</button>
                                @elseif($docStatus === 'approved')
                                    <span class="text-xs bg-green-100 text-green-700 px-2.5 py-1 rounded-full">Approved</span>
                                @elseif($docStatus === 'rejected')
                                    <span class="text-xs bg-red-100 text-red-700 px-2.5 py-1 rounded-full">Rejected</span>
                                @elseif($docStatus === 'not_applicable')
                                    <span class="text-xs bg-gray-100 text-gray-600 px-2.5 py-1 rounded-full">N/A</span>
                                @else
                                    <span class="text-xs text-gray-400">Not uploaded</span>
                                @endif
                            @elseif(auth()->user()->isAgent())
                                @if($docStatus)
                                    <span class="text-xs px-2.5 py-1 rounded-full
                                        {{ $docStatus === 'approved' ? 'bg-green-100 text-green-700' : ($docStatus === 'rejected' ? 'bg-red-100 text-red-700' : ($docStatus === 'not_applicable' ? 'bg-gray-100 text-gray-600' : 'bg-yellow-100 text-yellow-700')) }}">
                                        {{ ['submitted'=>'Under Review','approved'=>'Approved','rejected'=>'Rejected','resubmitted'=>'Resubmitted','not_applicable'=>'N/A'][$docStatus] ?? ucfirst($docStatus) }}
                                    </span>
                                @else
                                    <span class="text-xs text-gray-400">Not uploaded</span>
                                @endif
                            @endif
                        </div>
                    </div>
                    {{-- Rejection reason --}}
                    @if($docStatus === 'rejected' && $doc?->rejection_reason)
                    <p class="text-xs text-red-500 mt-1 ml-6"><i class="fas fa-info-circle mr-1"></i>{{ $doc->rejection_reason }}</p>
                    @endif
                </div>

                {{-- Reject Modal (admin only) --}}
                @if(auth()->user()->isAdmin())
                <div id="reject-doc-{{ $key }}" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
                    <div class="bg-white rounded-xl shadow-xl p-6 w-full max-w-md mx-4">
                        <h3 class="font-semibold text-gray-800 mb-1">Reject Document</h3>
                        <p class="text-xs text-gray-500 mb-4">"{{ $item['label'] }}" — client will be notified to resubmit.</p>
                        <form method="POST" action="{{ route('documents.checklist.reject', [$reservation, $key]) }}">
                            @csrf @method('PATCH')
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Rejection Reason <span class="text-red-500">*</span></label>
                                <textarea name="rejection_reason" rows="3" required
                                    placeholder="e.g. Document is blurry, wrong document type..."
                                    class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-red-400 resize-none"></textarea>
                            </div>
                            <div class="flex gap-3">
                                <button type="button"
                                    onclick="document.getElementById('reject-doc-{{ $key }}').classList.add('hidden')"
                                    class="flex-1 bg-gray-100 text-gray-700 py-2 rounded-lg text-sm hover:bg-gray-200 transition">Cancel</button>
                                <button type="submit"
                                    class="flex-1 bg-red-600 text-white py-2 rounded-lg text-sm hover:bg-red-700 transition font-medium">Reject</button>
                            </div>
                        </form>
                    </div>
                </div>
                @endif
                @endforeach
            </div>
        </div>
        @endif

        {{-- RF Deadline & Verification --}}
        @if(in_array($reservation->status, ['confirmed', 'reservation_paid']))
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="font-semibold text-gray-800 text-sm mb-4"><i class="fas fa-calendar-alt mr-1 text-indigo-400"></i> Reservation Fee</h3>

            @if($reservation->rf_paid_at)
                <div class="bg-green-50 border border-green-200 rounded-xl p-3 mb-4">
                    <p class="text-xs font-semibold text-green-800">RF Verified ✓</p>
                    <p class="text-xs text-green-700 mt-0.5">OR# {{ $reservation->rf_or_number }} · {{ $reservation->rf_paid_at->format('M d, Y') }}</p>
                </div>
            @else
                {{-- Set RF Deadline (agent/admin) --}}
                @if(auth()->user()->isAdmin() || auth()->user()->isAgent())
                <form method="POST" action="{{ route('reservations.set-rf-deadline', $reservation) }}" class="mb-4">
                    @csrf @method('PATCH')
                    <label class="block text-xs font-medium text-gray-600 mb-1">RF Deadline</label>
                    <div class="flex gap-2">
                        <input type="date" name="rf_deadline"
                            value="{{ $reservation->rf_deadline?->format('Y-m-d') }}"
                            min="{{ now()->addDay()->format('Y-m-d') }}"
                            class="flex-1 px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-indigo-700 transition">
                            Set
                        </button>
                    </div>
                </form>
                @endif

                {{-- Verify RF (finance/admin) --}}
                @if(auth()->user()->isAdmin() || auth()->user()->isFinance())
                @if($reservation->viewing_status === 'payment_uploaded')
                <form method="POST" action="{{ route('reservations.verify-rf', $reservation) }}">
                    @csrf @method('PATCH')
                    <label class="block text-xs font-medium text-gray-600 mb-1">Issue Official Receipt</label>
                    <div class="flex gap-2">
                        <input type="text" name="rf_or_number" placeholder="OR Number"
                            class="flex-1 px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-green-700 transition font-medium">
                            Verify RF
                        </button>
                    </div>
                    <p class="text-xs text-gray-400 mt-1">This will mark the reservation as RF Paid and generate the document checklist.</p>
                </form>
                @else
                    <p class="text-xs text-gray-400"><i class="fas fa-info-circle mr-1"></i>Waiting for client to upload proof of RF payment.</p>
                @endif
                @endif
            @endif
        </div>
        @endif

        {{-- Checklist Verification (admin only) --}}
        @if($reservation->status === 'reservation_paid' && $reservation->document_checklist)
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-gray-800 text-sm"><i class="fas fa-clipboard-check mr-1 text-indigo-400"></i> Document Checklist</h3>
                @if(auth()->user()->isAdmin() || auth()->user()->isFinance())
                <a href="{{ route('finance.schedule.create', $reservation) }}"
                    class="text-xs bg-indigo-600 text-white px-3 py-1.5 rounded-lg hover:bg-indigo-700 transition">
                    <i class="fas fa-calendar-alt mr-1"></i>
                    {{ $reservation->paymentSchedules->count() ? 'Edit Schedule' : 'Issue Schedule' }}
                </a>
                @endif
            </div>
            <div class="space-y-2">
                @foreach($reservation->document_checklist as $index => $item)
                <div class="flex items-center justify-between gap-3 py-2 border-b border-gray-50 last:border-0">
                    <div class="flex items-center gap-2 flex-1 min-w-0">
                        <i class="fas {{ $item['verified'] ? 'fa-check-circle text-green-500' : ($item['uploaded'] ? 'fa-clock text-yellow-500' : 'fa-circle text-gray-300') }} text-sm flex-shrink-0"></i>
                        <span class="text-sm text-gray-700 truncate">{{ $item['label'] }}</span>
                    </div>
                    <div class="flex items-center gap-2 flex-shrink-0">
                        @if($item['uploaded'] && !$item['verified'] && !($item['rejected'] ?? false))
                            @if($item['file_path'])
                            <a href="{{ asset('storage/' . $item['file_path']) }}" target="_blank"
                                class="text-xs text-indigo-600 hover:underline">View</a>
                            @endif
                            @if(auth()->user()->isAdmin())
                            <form method="POST" action="{{ route('reservations.checklist.verify', [$reservation, $index]) }}">
                                @csrf @method('PATCH')
                                <button class="text-xs bg-green-600 text-white px-3 py-1 rounded-lg hover:bg-green-700 transition">Verify</button>
                            </form>
                            <button type="button"
                                onclick="document.getElementById('reject-modal-{{ $index }}').classList.remove('hidden')"
                                class="text-xs bg-red-50 text-red-600 px-3 py-1 rounded-lg hover:bg-red-100 transition">Reject</button>
                            @endif
                        @elseif($item['rejected'] ?? false)
                            <span class="text-xs bg-red-100 text-red-700 px-2.5 py-1 rounded-full">Rejected</span>
                        @elseif($item['not_applicable'] ?? false)
                            <span class="text-xs bg-gray-100 text-gray-600 px-2.5 py-1 rounded-full">N/A</span>
                        @elseif($item['verified'])
                            <span class="text-xs bg-green-100 text-green-700 px-2.5 py-1 rounded-full">Verified</span>
                        @else
                            <span class="text-xs text-gray-400">Not uploaded</span>
                        @endif
                    </div>
                </div>

                {{-- Reject Modal --}}
                @if(auth()->user()->isAdmin())
                <div id="reject-modal-{{ $index }}" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
                    <div class="bg-white rounded-xl shadow-xl p-6 w-full max-w-md mx-4">
                        <h3 class="font-semibold text-gray-800 mb-1">Reject Document</h3>
                        <p class="text-xs text-gray-500 mb-4">"{{ $item['label'] }}" — client will be notified to resubmit.</p>
                        <form method="POST" action="{{ route('reservations.checklist.reject', [$reservation, $index]) }}">
                            @csrf @method('PATCH')
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Rejection Reason <span class="text-red-500">*</span></label>
                                <textarea name="rejection_reason" rows="3" required
                                    placeholder="e.g. Document is blurry, wrong document type..."
                                    class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-red-400 resize-none"></textarea>
                            </div>
                            <div class="flex gap-3">
                                <button type="button"
                                    onclick="document.getElementById('reject-modal-{{ $index }}').classList.add('hidden')"
                                    class="flex-1 bg-gray-100 text-gray-700 py-2 rounded-lg text-sm hover:bg-gray-200 transition">Cancel</button>
                                <button type="submit"
                                    class="flex-1 bg-red-600 text-white py-2 rounded-lg text-sm hover:bg-red-700 transition font-medium">Reject</button>
                            </div>
                        </form>
                    </div>
                </div>
                @endif
                @endforeach
            </div>
        </div>
        @endif

        {{-- Agent --}}
        @if($reservation->agent)
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="font-semibold text-gray-800 mb-3 text-sm">Agent</h3>
            <p class="font-medium text-gray-800">{{ $reservation->agent->full_name }}</p>
            <p class="text-xs text-gray-400 mt-1">{{ $reservation->agent->phone }}</p>
            <a href="{{ route('agents.show', $reservation->agent) }}" class="mt-3 block text-center text-xs bg-gray-50 text-gray-600 py-2 rounded-lg hover:bg-gray-100 transition">
                View Agent
            </a>
        </div>
        @endif

    </div>

    {{-- Right: Payments --}}
    <div class="lg:col-span-2 space-y-6">

        @if($reservation->notes)
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="font-semibold text-gray-800 mb-2">Notes</h3>
            <p class="text-sm text-gray-600">{{ $reservation->notes }}</p>
        </div>
        @endif

        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-gray-800">Payments ({{ $reservation->payments->count() }})</h3>
                @if(auth()->user()->isAdmin() || auth()->user()->isAgent())
                    <a href="{{ route('payments.create') }}?reservation_id={{ $reservation->id }}" class="text-xs bg-indigo-600 text-white px-3 py-1.5 rounded-lg hover:bg-indigo-700 transition">
                        <i class="fas fa-plus mr-1"></i> Add Payment
                    </a>
                @endif
            </div>

            @forelse($reservation->payments as $payment)
            <div class="flex items-center justify-between py-3 border-b border-gray-50 last:border-0">
                <div>
                    <p class="text-sm font-medium text-gray-800">{{ ucfirst(str_replace('_', ' ', $payment->payment_type)) }}</p>
                    <p class="text-xs text-gray-400">{{ $payment->payment_date }} · {{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}</p>
                    @if($payment->reference_number)
                        <p class="text-xs text-gray-400">Ref: {{ $payment->reference_number }}</p>
                    @endif
                </div>
                <div class="text-right">
                    <p class="text-sm font-bold text-gray-800">₱{{ number_format($payment->amount, 2) }}</p>
                    <span class="text-xs px-2 py-0.5 rounded-full
                        {{ $payment->status === 'completed' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                        {{ ucfirst($payment->status) }}
                    </span>
                </div>
            </div>
            @empty
            <p class="text-sm text-gray-400">No payments recorded yet.</p>
            @endforelse

            @if($reservation->payments->count())
            @php
                $totalPaid     = $reservation->payments->where('status', 'completed')->sum('amount');
                $propertyPrice = $reservation->property->price ?? 0;
                $remaining     = $propertyPrice - $totalPaid;
            @endphp
            <div class="mt-4 pt-4 border-t border-gray-100 space-y-2">
                <div class="flex justify-between">
                    <span class="text-sm text-gray-500">Property Price</span>
                    <span class="text-sm font-medium text-gray-800">₱{{ number_format($propertyPrice, 2) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-sm text-gray-500">Total Paid</span>
                    <span class="text-sm font-bold text-green-600">₱{{ number_format($totalPaid, 2) }}</span>
                </div>
                <div class="flex justify-between pt-2 border-t border-gray-100">
                    <span class="text-sm font-semibold text-gray-700">Remaining Balance</span>
                    @if($remaining <= 0)
                        <span class="text-sm font-bold text-green-600">Fully Paid ✓</span>
                    @else
                        <span class="text-sm font-bold text-red-600">₱{{ number_format($remaining, 2) }}</span>
                    @endif
                </div>
            </div>
            @endif
        </div>

    </div>
</div>

{{-- Cancellation Info (if cancelled) --}}
@if($reservation->status === 'cancelled' && $reservation->cancelled_at)
<div class="mt-6 bg-red-50 border border-red-200 rounded-xl p-5">
    <p class="text-sm font-semibold text-red-800 mb-2"><i class="fas fa-ban mr-1"></i> Cancellation Details</p>
    <div class="text-xs text-red-700 space-y-1">
        <p><span class="font-medium">Cancelled:</span> {{ $reservation->cancelled_at->format('M d, Y h:i A') }}</p>
        <p><span class="font-medium">Type:</span>
            {{ ['manual_admin' => 'Manual (Admin)', 'auto_no_action' => 'Auto — No Action (7 days)', 'auto_rf_expired' => 'Auto — RF Deadline Expired', 'client_backout' => 'Client Backed Out'][$reservation->cancellation_type] ?? ucfirst(str_replace('_', ' ', $reservation->cancellation_type ?? '—')) }}
        </p>
        @if($reservation->cancellation_reason)
        <p><span class="font-medium">Reason:</span> {{ $reservation->cancellation_reason }}</p>
        @endif
        @if($reservation->data_wiped_at)
        <p class="mt-2 text-red-500 font-medium"><i class="fas fa-trash mr-1"></i> Client data wiped on {{ $reservation->data_wiped_at->format('M d, Y') }}</p>
        @else
        @php $daysLeft = max(0, now()->diffInDays($reservation->gracePeriodEndsAt(), false)); @endphp
        <p class="mt-2"><i class="fas fa-clock mr-1"></i> Data will be wiped in <strong>{{ $daysLeft }} day(s)</strong> ({{ $reservation->gracePeriodEndsAt()->format('M d, Y') }})</p>
        @endif
    </div>
</div>
@endif

{{-- Cancel Reservation Modal (Admin only) --}}
@if(auth()->user()->isAdmin() && in_array($reservation->status, ['pending', 'confirmed']))
<div id="cancel-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
    <div class="bg-white rounded-xl shadow-xl p-6 w-full max-w-md mx-4">
        <h3 class="font-semibold text-gray-800 mb-1">Cancel Reservation</h3>
        <p class="text-xs text-gray-500 mb-4">This will release the unit and queue the client data for deletion after the grace period.</p>
        <form method="POST" action="{{ route('reservations.update-status', $reservation) }}">
            @csrf @method('PATCH')
            <input type="hidden" name="status" value="cancelled">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Cancellation Reason <span class="text-red-500">*</span></label>
                <textarea name="cancellation_reason" rows="3" required
                    placeholder="e.g. Client backed out, no response after 7 days..."
                    class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-red-400 resize-none"></textarea>
            </div>
            <div class="flex gap-3">
                <button type="button" onclick="document.getElementById('cancel-modal').classList.add('hidden')"
                    class="flex-1 bg-gray-100 text-gray-700 py-2 rounded-lg text-sm hover:bg-gray-200 transition">
                    Go Back
                </button>
                <button type="submit"
                    class="flex-1 bg-red-600 text-white py-2 rounded-lg text-sm hover:bg-red-700 transition font-medium">
                    Confirm Cancellation
                </button>
            </div>
        </form>
    </div>
</div>
@endif

{{-- Document Preview Modal --}}
<div id="doc-preview-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-75">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-3xl mx-4 overflow-hidden">
        <div class="flex items-center justify-between px-5 py-3 border-b border-gray-100">
            <p class="font-semibold text-gray-800 text-sm" id="preview-title"></p>
            <button onclick="closePreview()" class="text-gray-400 hover:text-gray-600 transition">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>
        <div class="p-4 flex items-center justify-center bg-gray-50" style="min-height:400px">
            <img id="preview-img" src="" alt="" class="hidden max-w-full max-h-96 rounded-lg object-contain">
            <iframe id="preview-pdf" src="" class="hidden w-full" style="height:480px"></iframe>
            <p id="preview-unsupported" class="hidden text-sm text-gray-400">Preview not available. <a id="preview-download" href="#" target="_blank" class="text-indigo-600 hover:underline">Open file</a></p>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function openPreview(url, type, title) {
    document.getElementById('preview-title').textContent = title;
    document.getElementById('preview-img').classList.add('hidden');
    document.getElementById('preview-pdf').classList.add('hidden');
    document.getElementById('preview-unsupported').classList.add('hidden');
    if (type && type.includes('image')) {
        const img = document.getElementById('preview-img');
        img.src = url;
        img.classList.remove('hidden');
    } else if (type && type.includes('pdf')) {
        const pdf = document.getElementById('preview-pdf');
        pdf.src = url;
        pdf.classList.remove('hidden');
    } else {
        document.getElementById('preview-download').href = url;
        document.getElementById('preview-unsupported').classList.remove('hidden');
    }
    document.getElementById('doc-preview-modal').classList.remove('hidden');
}
function closePreview() {
    document.getElementById('doc-preview-modal').classList.add('hidden');
    document.getElementById('preview-pdf').src = '';
}
</script>
@endpush
