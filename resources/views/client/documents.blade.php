@extends('layouts.client')
@section('title', 'My Documents — EstateFlow')
@section('page-title', 'My Documents')
@section('page-subtitle', 'Submit your required documents for your reservation')

@section('content')

@if(!$clientRecord)
<div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 flex items-start gap-3">
    <i class="fas fa-exclamation-triangle text-yellow-500 mt-0.5"></i>
    <p class="text-sm text-yellow-800">Your account is not linked to a client profile. Please contact an administrator.</p>
</div>
@elseif(!$activeReservation)
<div class="bg-white rounded-xl shadow-sm p-16 text-center text-gray-400">
    <i class="fas fa-folder-open text-5xl mb-4 block text-gray-200"></i>
    <p class="text-base font-medium text-gray-600 mb-2">Document submission is not yet available.</p>
    <p class="text-sm text-gray-400">Please complete your reservation first. Document submission opens once your reservation fee has been verified.</p>
    <a href="{{ route('client.dashboard') }}" class="mt-6 inline-block bg-indigo-600 text-white px-6 py-2.5 rounded-xl text-sm hover:bg-indigo-700 transition font-medium">
        <i class="fas fa-arrow-left mr-2"></i>Back to Dashboard
    </a>
</div>
@else

@php
    $checklist   = $activeReservation->document_checklist ?? [];
    $total       = count($checklist);
    $approved    = collect($checklist)->filter(function($item) use ($checklistDocs) {
        $doc = $checklistDocs->get($item['key']);
        return $doc && $doc->checklist_status === 'approved';
    })->count();
    $naCount     = collect($checklist)->filter(function($item) use ($checklistDocs) {
        $doc = $checklistDocs->get($item['key']);
        return $doc && $doc->checklist_status === 'not_applicable';
    })->count();
    $effective   = $total - $naCount;
    $progress    = $effective > 0 ? round(($approved / $effective) * 100) : 0;
    $allApproved = $approved >= $effective && $effective > 0;
    $progressColor = $progress < 40 ? 'bg-red-500' : ($progress < 80 ? 'bg-yellow-500' : 'bg-green-500');
    $deadline    = $activeReservation->document_deadline ?? null;
    $daysLeft    = $deadline ? now()->diffInDays(\Carbon\Carbon::parse($deadline), false) : null;
@endphp

{{-- All Approved Banner --}}
@if($allApproved)
<div class="bg-green-50 border border-green-200 rounded-xl p-4 mb-6 flex items-center gap-3">
    <i class="fas fa-check-circle text-green-500 text-xl"></i>
    <div>
        <p class="text-sm font-semibold text-green-800">All documents verified!</p>
        <p class="text-xs text-green-600 mt-0.5">Your payment schedule will be issued soon. Our team will contact you.</p>
    </div>
</div>
@endif

{{-- Reservation Info --}}
<div class="bg-white rounded-xl shadow-sm p-4 mb-6 flex items-center gap-4">
    <div class="w-14 h-14 bg-indigo-50 rounded-xl flex items-center justify-center flex-shrink-0 overflow-hidden">
        @if($activeReservation->property?->image_main)
            <img src="{{ asset($activeReservation->property->image_main) }}" class="w-14 h-14 object-cover">
        @else
            <i class="fas fa-building text-indigo-300 text-xl"></i>
        @endif
    </div>
    <div class="flex-1 min-w-0">
        <p class="font-semibold text-gray-800">{{ $activeReservation->property->title ?? '—' }}</p>
        <p class="text-xs text-gray-400 mt-0.5">
            {{ \App\Models\Reservation::PAYMENT_SCHEMES[$activeReservation->payment_scheme] ?? '—' }}
            @if($activeReservation->employment_type)
                · {{ \App\Models\Reservation::EMPLOYMENT_TYPES[$activeReservation->employment_type] ?? '' }}
            @endif
        </p>
    </div>
    <span class="text-xs px-2.5 py-1 rounded-full font-medium bg-green-100 text-green-700 flex-shrink-0">
        {{ \App\Models\Reservation::STATUSES[$activeReservation->status] ?? ucfirst($activeReservation->status) }}
    </span>
</div>

{{-- Progress Tracker --}}
<div class="bg-white rounded-xl shadow-sm p-5 mb-6">
    <div class="flex items-center justify-between mb-3">
        <div>
            <p class="text-sm font-semibold text-gray-800">Document Progress</p>
            <p class="text-xs text-gray-400 mt-0.5">{{ $approved }} of {{ $effective }} documents approved</p>
        </div>
        <div class="text-right">
            <p class="text-2xl font-bold {{ $progress < 40 ? 'text-red-600' : ($progress < 80 ? 'text-yellow-600' : 'text-green-600') }}">{{ $progress }}%</p>
            @if($deadline)
                @if($daysLeft < 0)
                    <p class="text-xs text-red-600 font-medium"><i class="fas fa-exclamation-triangle mr-1"></i>Overdue by {{ abs($daysLeft) }} day(s)</p>
                @elseif($daysLeft <= 3)
                    <p class="text-xs text-orange-600 font-medium"><i class="fas fa-clock mr-1"></i>{{ $daysLeft }} day(s) left</p>
                @else
                    <p class="text-xs text-gray-400">Due: {{ \Carbon\Carbon::parse($deadline)->format('M d, Y') }} ({{ $daysLeft }} days)</p>
                @endif
            @endif
        </div>
    </div>
    <div class="w-full bg-gray-100 rounded-full h-3">
        <div class="{{ $progressColor }} h-3 rounded-full transition-all duration-500" style="width: {{ $progress }}%"></div>
    </div>
    <div class="flex justify-between text-xs text-gray-400 mt-1">
        <span>0%</span>
        <span>100%</span>
    </div>
</div>

{{-- Document Checklist --}}
@if(empty($checklist))
<div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 text-sm text-yellow-700">
    <i class="fas fa-info-circle mr-2"></i>
    Your document checklist has not been generated yet. Please contact your agent.
</div>
@else

@php
    $pendingKeys = collect($checklist)->filter(fn($item) =>
        in_array($checklistDocs->get($item['key'])?->checklist_status ?? null, [null, 'rejected'])
    )->pluck('key')->toArray();
@endphp

<div class="space-y-3" id="checklist-cards">
    @foreach($checklist as $index => $item)
    @php
        $key         = $item['key'];
        $label       = $item['label'];
        $conditional = $item['conditional'] ?? false;
        $doc         = $checklistDocs->get($key);
        $status      = $doc ? $doc->checklist_status : null;
        $canUpload   = in_array($status, [null, 'rejected']);

        $badgeConfig = [
            null             => ['bg' => 'bg-gray-100',   'text' => 'text-gray-500',   'icon' => 'fa-circle',       'label' => 'Not yet selected'],
            'submitted'      => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-700', 'icon' => 'fa-clock',        'label' => 'Under Review'],
            'approved'       => ['bg' => 'bg-green-100',  'text' => 'text-green-700',  'icon' => 'fa-check-circle', 'label' => 'Approved'],
            'rejected'       => ['bg' => 'bg-red-100',    'text' => 'text-red-700',    'icon' => 'fa-times-circle', 'label' => 'Rejected — Resubmit'],
            'resubmitted'    => ['bg' => 'bg-blue-100',   'text' => 'text-blue-700',   'icon' => 'fa-sync',         'label' => 'Resubmitted — Under Review'],
            'not_applicable' => ['bg' => 'bg-gray-100',   'text' => 'text-gray-500',   'icon' => 'fa-minus-circle', 'label' => 'Not Applicable'],
        ];
        $badge = $badgeConfig[$status] ?? $badgeConfig[null];
    @endphp

    <div class="bg-white rounded-xl shadow-sm overflow-hidden border-l-4 {{ $status === 'approved' ? 'border-green-400' : ($status === 'rejected' ? 'border-red-400' : 'border-gray-200') }}"
         id="card-{{ $key }}">
        <div class="p-4">
            <div class="flex items-start justify-between gap-3">
                <div class="flex items-start gap-3 flex-1 min-w-0">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0 text-sm font-bold
                        {{ $status === 'approved' ? 'bg-green-100 text-green-700' : 'bg-indigo-100 text-indigo-600' }}">
                        {{ $index + 1 }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-semibold text-gray-800">{{ $label }}</p>
                        @if($conditional)
                            <span class="text-xs text-gray-400 italic">if applicable</span>
                        @endif
                        @if($doc && $doc->file_name && $status !== 'not_applicable')
                            <p class="text-xs text-gray-400 mt-1">
                                <i class="fas fa-paperclip mr-1"></i>{{ $doc->file_name }}
                            </p>
                        @endif
                        @if($status === 'not_applicable' && $doc?->not_applicable_reason)
                            <p class="text-xs text-gray-400 mt-1"><i class="fas fa-info-circle mr-1"></i>{{ $doc->not_applicable_reason }}</p>
                        @endif

                        {{-- Rejection Reason --}}
                        @if($status === 'rejected' && $doc?->rejection_reason)
                        <div class="mt-2 bg-red-50 border border-red-100 rounded-lg p-2 flex items-start gap-2">
                            <i class="fas fa-exclamation-circle text-red-400 mt-0.5 flex-shrink-0 text-xs"></i>
                            <p class="text-xs text-red-600">{{ $doc->rejection_reason }}</p>
                        </div>
                        @endif

                        {{-- File picker (no individual upload button) --}}
                        @if($canUpload)
                        <div class="mt-3">
                            <input type="file"
                                data-key="{{ $key }}"
                                data-url="{{ route('documents.checklist.upload', [$activeReservation, $key]) }}"
                                accept=".jpg,.jpeg,.png,.pdf"
                                class="doc-file-input w-full text-xs text-gray-600 border border-gray-200 rounded-lg px-3 py-2 bg-gray-50
                                file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:text-xs file:bg-indigo-50 file:text-indigo-600">
                            <p class="text-xs text-gray-400 mt-1">JPG, PNG, PDF — Max 5MB</p>
                            <p class="text-xs text-indigo-500 mt-0.5 file-selected-label hidden"><i class="fas fa-check mr-1"></i>File selected</p>
                        </div>
                        @endif

                        {{-- Not Applicable (conditional only) --}}
                        @if($conditional && $canUpload)
                        <div class="mt-2">
                            <button type="button" onclick="toggleNaForm('na-{{ $key }}')"
                                class="text-xs text-gray-400 hover:text-gray-600 transition underline">
                                Mark as not applicable
                            </button>
                            <div id="na-{{ $key }}" class="hidden mt-2 flex items-center gap-2">
                                <input type="text" id="na-reason-{{ $key }}" placeholder="Reason (e.g. Single, not married)"
                                    class="flex-1 text-xs border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-gray-300">
                                <button type="button"
                                    onclick="submitNa('{{ $key }}', '{{ route('documents.checklist.not-applicable', [$activeReservation, $key]) }}')"
                                    class="bg-gray-500 text-white px-3 py-2 rounded-lg text-xs hover:bg-gray-600 transition whitespace-nowrap">
                                    Confirm N/A
                                </button>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
                <span class="badge-{{ $key }} text-xs px-2.5 py-1 rounded-full font-medium flex-shrink-0 {{ $badge['bg'] }} {{ $badge['text'] }}">
                    <i class="fas {{ $badge['icon'] }} mr-1"></i>{{ $badge['label'] }}
                </span>
            </div>
        </div>
    </div>
    @endforeach
</div>

{{-- Submit All Button --}}
@if(count($pendingKeys) > 0)
<div class="mt-6 bg-white rounded-xl shadow-sm p-5">
    <p class="text-sm text-gray-600 mb-1">Select a file for each required document above, then click <strong>Submit All Documents</strong>.</p>
    <p class="text-xs text-gray-400 mb-4" id="submit-status">0 of {{ count($pendingKeys) }} files selected</p>
    <button id="submit-all-btn" onclick="submitAll()" disabled
        class="w-full bg-indigo-600 text-white py-3 rounded-xl text-sm font-semibold hover:bg-indigo-700 transition disabled:opacity-40 disabled:cursor-not-allowed">
        <i class="fas fa-upload mr-2"></i>Submit All Documents
    </button>
</div>
@endif

@endif

@endif

@endsection

@push('scripts')
<script>
const CSRF = document.querySelector('meta[name=csrf-token]')?.content ?? '{{ csrf_token() }}';

function toggleNaForm(id) {
    document.getElementById(id).classList.toggle('hidden');
}

// Track selected files per key
const selectedFiles = {};

document.addEventListener('change', function(e) {
    const input = e.target.closest('.doc-file-input');
    if (!input) return;
    const key = input.dataset.key;
    if (input.files[0]) {
        selectedFiles[key] = input;
        input.nextElementSibling.nextElementSibling.classList.remove('hidden'); // show "File selected"
    } else {
        delete selectedFiles[key];
        input.nextElementSibling.nextElementSibling.classList.add('hidden');
    }
    updateSubmitBtn();
});

function updateSubmitBtn() {
    const total   = document.querySelectorAll('.doc-file-input').length;
    const filled  = Object.keys(selectedFiles).length;
    const btn     = document.getElementById('submit-all-btn');
    const status  = document.getElementById('submit-status');
    if (status) status.textContent = filled + ' of ' + total + ' files selected';
    if (btn) btn.disabled = filled === 0;
}

async function submitAll() {
    const btn = document.getElementById('submit-all-btn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Uploading...';

    const keys  = Object.keys(selectedFiles);
    let success = 0, failed = 0;

    for (const key of keys) {
        const input = selectedFiles[key];
        const url   = input.dataset.url;
        const fd    = new FormData();
        fd.append('_token', CSRF);
        fd.append('file', input.files[0]);

        try {
            const res  = await fetch(url, { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            const json = await res.json();
            if (json.success) {
                success++;
                updateCard(key, json.status, json.file_name);
                delete selectedFiles[key];
            } else {
                failed++;
            }
        } catch {
            failed++;
        }
    }

    if (failed === 0) {
        showToast(success + ' document(s) submitted successfully!', 'green');
        // Hide submit panel if no more pending
        if (document.querySelectorAll('.doc-file-input').length === 0) {
            document.getElementById('submit-all-btn')?.closest('.mt-6')?.remove();
        }
    } else {
        showToast(failed + ' upload(s) failed. Please try again.', 'red');
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-upload mr-2"></i>Submit All Documents';
    }
    updateSubmitBtn();
}

async function submitNa(key, url) {
    const reason = document.getElementById('na-reason-' + key)?.value?.trim();
    if (!reason) { showToast('Please enter a reason.', 'red'); return; }

    const fd = new FormData();
    fd.append('_token', CSRF);
    fd.append('reason', reason);

    try {
        const res  = await fetch(url, { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        const json = await res.json();
        if (json.success) {
            showToast(json.message, 'green');
            updateCard(key, 'not_applicable', null);
            delete selectedFiles[key];
            updateSubmitBtn();
        } else {
            showToast(json.message ?? 'Failed.', 'red');
        }
    } catch {
        showToast('Something went wrong.', 'red');
    }
}

function updateCard(key, status, fileName) {
    const badgeMap = {
        submitted:      { bg: 'bg-yellow-100', text: 'text-yellow-700', icon: 'fa-clock',        label: 'Under Review' },
        resubmitted:    { bg: 'bg-blue-100',   text: 'text-blue-700',   icon: 'fa-sync',         label: 'Resubmitted — Under Review' },
        not_applicable: { bg: 'bg-gray-100',   text: 'text-gray-500',   icon: 'fa-minus-circle', label: 'Not Applicable' },
    };
    const badge = badgeMap[status] ?? badgeMap['submitted'];
    const card  = document.getElementById('card-' + key);
    if (!card) return;

    // Update badge
    const badgeEl = card.querySelector('.badge-' + key);
    if (badgeEl) {
        badgeEl.className = `badge-${key} text-xs px-2.5 py-1 rounded-full font-medium flex-shrink-0 ${badge.bg} ${badge.text}`;
        badgeEl.innerHTML = `<i class="fas ${badge.icon} mr-1"></i>${badge.label}`;
    }

    // Update border
    card.classList.remove('border-green-400', 'border-red-400', 'border-gray-200');
    card.classList.add('border-gray-200');

    // Show file name
    if (fileName) {
        const nameEl = document.createElement('p');
        nameEl.className = 'text-xs text-gray-400 mt-1';
        nameEl.innerHTML = `<i class="fas fa-paperclip mr-1"></i>${fileName}`;
        card.querySelector('.min-w-0.flex-1 p')?.after(nameEl);
    }

    // Remove file input, hint, N/A section
    card.querySelector('.doc-file-input')?.closest('div.mt-3')?.remove();
    card.querySelector('[id^="na-"]')?.closest('div.mt-2')?.remove();
}

function showToast(msg, color) {
    const t = document.createElement('div');
    t.className = `fixed bottom-6 left-1/2 -translate-x-1/2 z-50 px-5 py-3 rounded-xl shadow-lg text-sm font-medium text-white transition ${
        color === 'green' ? 'bg-green-600' : 'bg-red-600'
    }`;
    t.textContent = msg;
    document.body.appendChild(t);
    setTimeout(() => t.remove(), 3500);
}

// Poll for admin verification changes every 8 seconds
async function pollDocumentStatuses() {
    try {
        const res  = await fetch('{{ route("client.documents.poll") }}', { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        const data = await res.json();

        Object.entries(data).forEach(([key, info]) => {
            const card = document.getElementById('card-' + key);
            if (!card) return;

            const badgeEl = card.querySelector('.badge-' + key);
            if (!badgeEl) return;

            const currentLabel = badgeEl.textContent.trim();
            const badgeMap = {
                submitted:      { bg: 'bg-yellow-100', text: 'text-yellow-700', icon: 'fa-clock',        label: 'Under Review' },
                resubmitted:    { bg: 'bg-blue-100',   text: 'text-blue-700',   icon: 'fa-sync',         label: 'Resubmitted — Under Review' },
                approved:       { bg: 'bg-green-100',  text: 'text-green-700',  icon: 'fa-check-circle', label: 'Approved' },
                rejected:       { bg: 'bg-red-100',    text: 'text-red-700',    icon: 'fa-times-circle', label: 'Rejected — Resubmit' },
                not_applicable: { bg: 'bg-gray-100',   text: 'text-gray-500',   icon: 'fa-minus-circle', label: 'Not Applicable' },
            };
            const badge = badgeMap[info.status];
            if (!badge) return;

            // Only update if status actually changed
            if (currentLabel.includes(badge.label)) return;

            badgeEl.className = `badge-${key} text-xs px-2.5 py-1 rounded-full font-medium flex-shrink-0 ${badge.bg} ${badge.text}`;
            badgeEl.innerHTML = `<i class="fas ${badge.icon} mr-1"></i>${badge.label}`;

            // Update border
            card.classList.remove('border-green-400', 'border-red-400', 'border-gray-200');
            card.classList.add(info.status === 'approved' ? 'border-green-400' : info.status === 'rejected' ? 'border-red-400' : 'border-gray-200');

            // If approved, remove file input
            if (info.status === 'approved') {
                card.querySelector('.doc-file-input')?.closest('div.mt-3')?.remove();
                card.querySelector('[id^="na-"]')?.closest('div.mt-2')?.remove();
            }

            // If rejected, show rejection reason
            if (info.status === 'rejected' && info.rejection_reason) {
                let reasonEl = card.querySelector('.rejection-reason-block');
                if (!reasonEl) {
                    reasonEl = document.createElement('div');
                    reasonEl.className = 'rejection-reason-block mt-2 bg-red-50 border border-red-100 rounded-lg p-2 flex items-start gap-2';
                    card.querySelector('.min-w-0.flex-1')?.appendChild(reasonEl);
                }
                reasonEl.innerHTML = `<i class="fas fa-exclamation-circle text-red-400 mt-0.5 flex-shrink-0 text-xs"></i><p class="text-xs text-red-600">${info.rejection_reason}</p>`;
            }

            showToast('"' + badge.label + '" — document status updated.', info.status === 'approved' ? 'green' : 'red');
        });
    } catch (e) {}
}

setInterval(pollDocumentStatuses, 8000);
</script>
@endpush
