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
    @php $progressWidth = $progress . '%'; @endphp
    <div class="w-full bg-gray-100 rounded-full h-3">
        <div class="{{ $progressColor }} h-3 rounded-full transition-all duration-500" style="width: {{ $progressWidth }};"></div>
    </div>
    <div class="flex justify-between text-xs text-gray-400 mt-1">
        <span>0%</span>
        <span>100%</span>
    </div>
</div>

@if($docCheck)
@php
    $missingItems = collect($docCheck['results'])->where('status', 'missing')->values();
    $duplicateItems = collect($docCheck['results'])->filter(function ($item) {
        return !empty($item['is_duplicate']);
    })->values();
@endphp
<div class="bg-white rounded-xl shadow-sm p-5 mb-6 border border-indigo-50">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div class="min-w-0">
            <p class="text-sm font-semibold text-gray-800">Auto Document Check</p>
            <p class="text-xs text-gray-500 mt-1">Your uploaded documents are reviewed automatically. Missing documents, duplicates, and unsupported uploads are highlighted below.</p>
        </div>
        <div class="flex flex-wrap gap-2 text-xs" id="auto-check-summary">
            <span class="inline-flex items-center gap-1 rounded-full bg-indigo-50 text-indigo-700 px-3 py-1" id="auto-check-score">Score {{ $docCheck['score'] }}%</span>
            @if($docCheck['missing'] > 0)
                <span class="inline-flex items-center gap-1 rounded-full bg-red-50 text-red-700 px-3 py-1" id="auto-check-missing">{{ $docCheck['missing'] }} missing</span>
            @else
                <span class="inline-flex items-center gap-1 rounded-full bg-green-50 text-green-700 px-3 py-1" id="auto-check-missing">No missing documents</span>
            @endif
            @if($docCheck['is_completely_uploaded'])
                <span class="inline-flex items-center gap-1 rounded-full bg-green-100 text-green-700 px-3 py-1" id="auto-check-complete">Upload complete</span>
            @else
                <span class="hidden" id="auto-check-complete"></span>
            @endif
            @if($duplicateItems->count() > 0)
                <span class="inline-flex items-center gap-1 rounded-full bg-yellow-50 text-yellow-700 px-3 py-1">{{ $duplicateItems->count() }} duplicate type(s)</span>
            @endif
        </div>
    </div>
    <div class="mt-4 grid gap-3 md:grid-cols-2 xl:grid-cols-3">
        @foreach($docCheck['results'] as $item)
        @php
            $statusConfig = match($item['status']) {
                'verified'             => ['icon' => 'fa-check-circle',       'color' => 'text-green-500',  'bg' => 'bg-green-50',  'badge' => 'bg-green-100 text-green-700',  'label' => 'Verified'],
                'pending_verification' => ['icon' => 'fa-clock',              'color' => 'text-yellow-500', 'bg' => 'bg-yellow-50', 'badge' => 'bg-yellow-100 text-yellow-700', 'label' => 'Pending Verification'],
                'expiring_soon'        => ['icon' => 'fa-exclamation-circle', 'color' => 'text-orange-500', 'bg' => 'bg-orange-50', 'badge' => 'bg-orange-100 text-orange-700', 'label' => 'Expiring Soon'],
                'expired'              => ['icon' => 'fa-times-circle',       'color' => 'text-red-500',    'bg' => 'bg-red-50',    'badge' => 'bg-red-100 text-red-700',       'label' => 'Expired'],
                default                => ['icon' => 'fa-minus-circle',       'color' => 'text-gray-400',   'bg' => 'bg-gray-50',   'badge' => 'bg-gray-100 text-gray-500',     'label' => ''],
            };
            // indicator: green when a document exists (not missing), red when missing
            $indicatorClass = ($item['status'] ?? 'missing') !== 'missing' ? 'border-l-4 border-green-400' : 'border-l-4 border-red-400';
        @endphp
        <div class="rounded-xl border p-3 {{ $statusConfig['bg'] }} {{ $indicatorClass }}" id="auto-check-{{ $item['type'] }}">
            <div class="flex items-start gap-3">
                <i class="fas {{ $statusConfig['icon'] }} {{ $statusConfig['color'] }} text-lg"></i>
                <div class="min-w-0">
                    <p class="text-sm font-medium text-gray-800">{{ $item['label'] }}</p>
                    @if($item['document'] && $item['document']->file_name)
                        <p class="text-xs text-gray-500 mt-1 truncate">{{ $item['document']->file_name }}</p>
                    @endif
                    @if($item['is_duplicate'])
                        <p class="text-xs text-yellow-700 mt-1"><i class="fas fa-copy mr-1"></i>{{ $item['count'] }} copies uploaded</p>
                    @endif
                </div>
            </div>
            <div class="mt-3 flex items-center justify-between gap-3">
                @if($statusConfig['label'])
                <span class="text-[10px] px-2.5 py-1 rounded-full font-semibold {{ $statusConfig['badge'] }}">{{ $statusConfig['label'] }}</span>
                @else
                <span></span>
                @endif
                @if($item['document'])
                    <a href="{{ route('documents.download', $item['document']) }}" class="text-indigo-500 hover:text-indigo-700 text-xs">Download</a>
                @endif
            </div>
        </div>
        @endforeach
    </div>
    @if($missingItems->count())
    <div class="mt-4 text-sm text-red-700">
        <strong>Missing document(s):</strong> {{ $missingItems->pluck('label')->join(', ') }}
    </div>
    @endif
    @if($duplicateItems->count())
    <div class="mt-2 text-sm text-yellow-700">
        <strong>Duplicate upload detected:</strong> {{ $duplicateItems->pluck('label')->join(', ') }}
    </div>
    @endif
    <div class="mt-4 text-xs text-gray-500 space-y-1">
        <div class="inline-flex items-center gap-2"><i class="fas fa-info-circle"></i> Supported formats: JPG, PNG, PDF.</div>
        <div class="inline-flex items-center gap-2"><i class="fas fa-check"></i> Upload all missing documents for the best result.</div>
    </div>
</div>
@endif

{{-- Document Checklist --}}
@if(empty($checklist))
<div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 text-sm text-yellow-700">
    <i class="fas fa-info-circle mr-2"></i>
    Your document checklist has not been generated yet. Please contact your agent.
</div>
@else

@php
    $pendingKeys = collect($checklist)->filter(function ($item) use ($checklistDocs) {
        $status = null;
        if ($checklistDocs->get($item['key'])) {
            $status = $checklistDocs->get($item['key'])->checklist_status;
        }
        return in_array($status, [null, 'rejected']);
    })->pluck('key')->toArray();
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

        $statusMap = [
            null             => ['bg' => 'bg-gray-50',    'border' => 'border-gray-200',  'badge' => 'bg-gray-100 text-gray-500',    'icon' => 'fa-circle',        'iconColor' => 'text-gray-300',   'label' => 'Missing'],
            'submitted'      => ['bg' => 'bg-blue-50',    'border' => 'border-blue-200',  'badge' => 'bg-blue-100 text-blue-700',   'icon' => 'fa-cloud-upload-alt','iconColor' => 'text-blue-400',  'label' => 'Uploaded'],
            'resubmitted'    => ['bg' => 'bg-blue-50',    'border' => 'border-blue-200',  'badge' => 'bg-blue-100 text-blue-700',   'icon' => 'fa-sync',           'iconColor' => 'text-blue-400',  'label' => 'Resubmitted'],
            'pending_verification' => ['bg' => 'bg-yellow-50','border' => 'border-yellow-200','badge' => 'bg-yellow-100 text-yellow-700','icon' => 'fa-clock',       'iconColor' => 'text-yellow-400','label' => 'Pending Verification'],
            'approved'       => ['bg' => 'bg-green-50',   'border' => 'border-green-300', 'badge' => 'bg-green-100 text-green-700', 'icon' => 'fa-check-circle',   'iconColor' => 'text-green-500', 'label' => 'Approved'],
            'rejected'       => ['bg' => 'bg-red-50',     'border' => 'border-red-300',   'badge' => 'bg-red-100 text-red-700',     'icon' => 'fa-times-circle',   'iconColor' => 'text-red-500',   'label' => 'Rejected'],
            'not_applicable' => ['bg' => 'bg-gray-50',    'border' => 'border-gray-200',  'badge' => 'bg-gray-100 text-gray-500',   'icon' => 'fa-minus-circle',   'iconColor' => 'text-gray-400',  'label' => 'Not Applicable'],
        ];
        $s = $statusMap[$status] ?? $statusMap[null];
    @endphp

    <div class="rounded-xl border-2 {{ $s['border'] }} {{ $s['bg'] }} overflow-hidden transition-all" id="card-{{ $key }}">
        <div class="p-4">
            <div class="flex items-start justify-between gap-3">
                {{-- Left: number + info --}}
                <div class="flex items-start gap-3 flex-1 min-w-0">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0 text-sm font-bold
                        {{ $status === 'approved' ? 'bg-green-100 text-green-700' : ($status === 'rejected' ? 'bg-red-100 text-red-700' : 'bg-white text-gray-500 border border-gray-200') }}">
                        @if($status === 'approved')
                            <i class="fas fa-check text-xs"></i>
                        @elseif($status === 'rejected')
                            <i class="fas fa-times text-xs"></i>
                        @else
                            {{ $index + 1 }}
                        @endif
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2 flex-wrap">
                            <p class="text-sm font-semibold text-gray-800">{{ $label }}</p>
                            @if($conditional)
                                <span class="text-xs text-gray-400 italic">(if applicable)</span>
                            @endif
                        </div>

                        {{-- File name --}}
                        @if($doc && $doc->file_name && $status !== 'not_applicable')
                            <p class="text-xs text-gray-500 mt-1 truncate">
                                <i class="fas fa-paperclip mr-1"></i>{{ $doc->file_name }}
                            </p>
                        @endif

                        {{-- N/A reason --}}
                        @if($status === 'not_applicable' && $doc?->not_applicable_reason)
                            <p class="text-xs text-gray-400 mt-1"><i class="fas fa-info-circle mr-1"></i>{{ $doc->not_applicable_reason }}</p>
                        @endif

                        {{-- Rejection reason --}}
                        @if($status === 'rejected')
                        <div class="mt-2 bg-red-100 border border-red-200 rounded-lg px-3 py-2 flex items-start gap-2">
                            <i class="fas fa-exclamation-circle text-red-500 mt-0.5 flex-shrink-0 text-xs"></i>
                            <div>
                                <p class="text-xs font-semibold text-red-700">Reason for rejection:</p>
                                <p class="text-xs text-red-600 mt-0.5">{{ $doc?->rejection_reason ?? 'No reason provided.' }}</p>
                            </div>
                        </div>
                        @endif

                        {{-- Reupload (rejected only) --}}
                        @if($status === 'rejected')
                        <div class="mt-3">
                            <label class="block text-xs font-medium text-red-700 mb-1"><i class="fas fa-redo mr-1"></i>Reupload Document</label>
                            <input type="file"
                                data-key="{{ $key }}"
                                data-required="{{ $conditional ? '0' : '1' }}"
                                data-url="{{ route('documents.checklist.upload', [$activeReservation, $key]) }}"
                                accept=".jpg,.jpeg,.png,.pdf"
                                class="doc-file-input w-full text-xs text-gray-600 border border-red-200 rounded-lg px-3 py-2 bg-white
                                file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:text-xs file:bg-red-50 file:text-red-600">
                            <p class="text-xs text-gray-400 mt-1">JPG, PNG, PDF — Max 5MB</p>
                            <p class="text-xs text-red-500 mt-0.5 file-selected-label hidden"><i class="fas fa-check mr-1"></i>File selected — click Submit to upload</p>
                            <p class="text-xs text-red-600 mt-1 hidden file-error-label"></p>
                        </div>
                        @endif

                        {{-- First upload (missing only) --}}
                        @if($status === null)
                        <div class="mt-3">
                            <input type="file"
                                data-key="{{ $key }}"
                                data-required="{{ $conditional ? '0' : '1' }}"
                                data-url="{{ route('documents.checklist.upload', [$activeReservation, $key]) }}"
                                accept=".jpg,.jpeg,.png,.pdf"
                                class="doc-file-input w-full text-xs text-gray-600 border border-gray-200 rounded-lg px-3 py-2 bg-white
                                file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:text-xs file:bg-indigo-50 file:text-indigo-600">
                            <p class="text-xs text-gray-400 mt-1">JPG, PNG, PDF — Max 5MB</p>
                            <p class="text-xs text-indigo-500 mt-0.5 file-selected-label hidden"><i class="fas fa-check mr-1"></i>File selected</p>
                            <p class="text-xs text-red-600 mt-1 hidden file-error-label"></p>
                        </div>
                        @endif

                        {{-- Not Applicable (conditional only, not yet uploaded) --}}
                        @if($conditional && $canUpload)
                        @php $notApplicableUrl = route('documents.checklist.not-applicable', [$activeReservation, $key]); @endphp
                        <div class="mt-2">
                            <button type="button" onclick="toggleNaForm('na-{{ $key }}')"
                                class="text-xs text-gray-400 hover:text-gray-600 transition underline">
                                Mark as not applicable
                            </button>
                            <div id="na-{{ $key }}" class="hidden mt-2 flex items-center gap-2">
                                <input type="text" id="na-reason-{{ $key }}" placeholder="Reason (e.g. Single, not married)"
                                    class="flex-1 text-xs border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-gray-300">
                                <button type="button"
                                    onclick="submitNa('{{ $key }}', '{{ $notApplicableUrl }}')"
                                    class="bg-gray-500 text-white px-3 py-2 rounded-lg text-xs hover:bg-gray-600 transition whitespace-nowrap">
                                    Confirm N/A
                                </button>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Badge --}}
                <span class="badge-{{ $key }} text-xs px-2.5 py-1 rounded-full font-semibold flex-shrink-0 {{ $s['badge'] }}">
                    <i class="fas {{ $s['icon'] }} mr-1"></i>{{ $s['label'] }}
                </span>
            </div>
        </div>
    </div>
    @endforeach
</div>

{{-- Submit All Button --}}
@if(count($pendingKeys) > 0)
<div class="mt-6 bg-white rounded-xl shadow-sm p-5">
    <p class="text-sm text-gray-600 mb-1">Select a file for each <strong>required</strong> document above, then click <strong>Submit All Documents</strong>. Optional documents (if applicable) can be skipped.</p>
    <p class="text-xs text-gray-400 mb-4" id="submit-status">0 of {{ count($pendingKeys) }} files selected</p>
    <button id="submit-all-btn" onclick="submitAll()" disabled
        class="w-full bg-indigo-600 text-white py-3 rounded-xl text-sm font-semibold hover:bg-indigo-700 transition disabled:opacity-40 disabled:cursor-not-allowed cursor-not-allowed">
        <i class="fas fa-upload mr-2"></i>Submit All Documents
    </button>
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
const SUPPORTED_MIMES = ['application/pdf', 'image/jpeg', 'image/png'];
const SUPPORTED_EXTS = ['pdf', 'jpg', 'jpeg', 'png'];

function toggleNaForm(id) {
    document.getElementById(id).classList.toggle('hidden');
}

// Track selected files per key
const selectedFiles = {};

function showFileError(input, message) {
    const wrapper = input.closest('div');
    const errorLabel = wrapper?.querySelector('.file-error-label');
    const selectedLabel = wrapper?.querySelector('.file-selected-label');
    if (errorLabel) {
        errorLabel.textContent = message;
        errorLabel.classList.remove('hidden');
    }
    if (selectedLabel) {
        selectedLabel.classList.add('hidden');
    }
    delete selectedFiles[input.dataset.key];
}

function clearFileError(input) {
    const wrapper = input.closest('div');
    const errorLabel = wrapper?.querySelector('.file-error-label');
    if (errorLabel) {
        errorLabel.textContent = '';
        errorLabel.classList.add('hidden');
    }
}

function validateSelectedFile(input) {
    const file = input.files[0];
    if (!file) return false;

    const extension = file.name.split('.').pop()?.toLowerCase();
    if (!SUPPORTED_MIMES.includes(file.type) || !SUPPORTED_EXTS.includes(extension)) {
        showFileError(input, 'Unsupported file type. Please upload JPG, PNG or PDF.');
        input.value = '';
        return false;
    }

    const duplicate = Object.entries(selectedFiles).some(([otherKey, otherInput]) =>
        otherKey !== input.dataset.key &&
        otherInput.files[0] &&
        otherInput.files[0].name.toLowerCase() === file.name.toLowerCase()
    );

    if (duplicate) {
        showFileError(input, 'A file with the same name is already selected for another document.');
        input.value = '';
        return false;
    }

    clearFileError(input);
    return true;
}

document.addEventListener('change', function(e) {
    const input = e.target.closest('.doc-file-input');
    if (!input) return;
    const key = input.dataset.key;

    if (input.files[0] && validateSelectedFile(input)) {
        selectedFiles[key] = input;
        const wrapper = input.closest('div');
        wrapper?.querySelector('.file-selected-label')?.classList.remove('hidden');
    } else {
        delete selectedFiles[key];
    }
    updateSubmitBtn();
});

function updateSubmitBtn() {
    const requiredInputs = document.querySelectorAll('.doc-file-input[data-required="1"]');
    const allInputs      = document.querySelectorAll('.doc-file-input');
    const requiredFilled = [...requiredInputs].every(input => selectedFiles[input.dataset.key]);
    const totalSelected  = Object.keys(selectedFiles).length;
    const btn    = document.getElementById('submit-all-btn');
    const status = document.getElementById('submit-status');
    if (status) status.textContent = totalSelected + ' of ' + allInputs.length + ' files selected';
    if (btn) {
        btn.disabled = !requiredFilled || totalSelected === 0;
        btn.classList.toggle('cursor-not-allowed', !requiredFilled || totalSelected === 0);
        btn.classList.toggle('cursor-pointer', requiredFilled && totalSelected > 0);
    }
}

async function submitAll() {
    const btn = document.getElementById('submit-all-btn');
    const requiredInputs = document.querySelectorAll('.doc-file-input[data-required="1"]');
    const allUnfilled = [...requiredInputs].filter(input => !selectedFiles[input.dataset.key]);
    if (allUnfilled.length > 0) {
        showToast('Please select a file for all required documents before submitting.', 'red');
        return;
    }

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
        submitted:      { badge: 'bg-blue-100 text-blue-700',    icon: 'fa-cloud-upload-alt', label: 'Uploaded' },
        resubmitted:    { badge: 'bg-blue-100 text-blue-700',    icon: 'fa-sync',             label: 'Resubmitted' },
        not_applicable: { badge: 'bg-gray-100 text-gray-500',   icon: 'fa-minus-circle',     label: 'Not Applicable' },
        approved:       { badge: 'bg-green-100 text-green-700', icon: 'fa-check-circle',     label: 'Approved' },
        rejected:       { badge: 'bg-red-100 text-red-700',     icon: 'fa-times-circle',     label: 'Rejected' },
    };
    const b    = badgeMap[status] ?? badgeMap['submitted'];
    const card = document.getElementById('card-' + key);
    if (!card) return;

    // Update badge
    const badgeEl = card.querySelector('.badge-' + key);
    if (badgeEl) {
        badgeEl.className = `badge-${key} text-xs px-2.5 py-1 rounded-full font-semibold flex-shrink-0 ${b.badge}`;
        badgeEl.innerHTML = `<i class="fas ${b.icon} mr-1"></i>${b.label}`;
    }

    // Update border + bg
    const borderMap = {
        submitted:      ['border-blue-200',  'bg-blue-50'],
        resubmitted:    ['border-blue-200',  'bg-blue-50'],
        not_applicable: ['border-gray-200',  'bg-gray-50'],
        approved:       ['border-green-300', 'bg-green-50'],
        rejected:       ['border-red-300',   'bg-red-50'],
    };
    card.className = card.className.replace(/border-\S+/g, '').replace(/bg-\S+-50/g, '').trim();
    const [border, bg] = borderMap[status] ?? ['border-blue-200', 'bg-blue-50'];
    card.classList.add('rounded-xl', 'border-2', border, bg, 'overflow-hidden', 'transition-all');

    // Show file name
    if (fileName) {
        let nameEl = card.querySelector('.doc-filename');
        if (!nameEl) {
            nameEl = document.createElement('p');
            nameEl.className = 'doc-filename text-xs text-gray-500 mt-1 truncate';
            card.querySelector('.min-w-0.flex-1 p')?.after(nameEl);
        }
        nameEl.innerHTML = `<i class="fas fa-paperclip mr-1"></i>${fileName}`;
    }

    // Remove file input & N/A section after upload
    if (status !== 'rejected') {
        card.querySelector('.doc-file-input')?.closest('div.mt-3')?.remove();
        card.querySelector('[id^="na-"]')?.closest('div.mt-2')?.remove();
    }
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

        let approvedCount = 0;
        let missingCount  = 0;
        const totalItems  = document.querySelectorAll('[id^="auto-check-"]').length - 3; // exclude score/missing/complete spans

        Object.entries(data).forEach(([key, info]) => {

            // ── Update checklist card ──
            const card = document.getElementById('card-' + key);
            if (card) {
                const badgeEl = card.querySelector('.badge-' + key);
                if (badgeEl) {
                    const currentLabel = badgeEl.textContent.trim();
                    const badgeMap = {
                        submitted:      { badge: 'bg-blue-100 text-blue-700',    icon: 'fa-cloud-upload-alt', label: 'Uploaded' },
                        resubmitted:    { badge: 'bg-blue-100 text-blue-700',    icon: 'fa-sync',             label: 'Resubmitted' },
                        approved:       { badge: 'bg-green-100 text-green-700', icon: 'fa-check-circle',     label: 'Approved' },
                        rejected:       { badge: 'bg-red-100 text-red-700',     icon: 'fa-times-circle',     label: 'Rejected' },
                        not_applicable: { badge: 'bg-gray-100 text-gray-500',   icon: 'fa-minus-circle',     label: 'Not Applicable' },
                    };
                    const badge = badgeMap[info.status];
                    if (badge && !currentLabel.includes(badge.label)) {
                        badgeEl.className = `badge-${key} text-xs px-2.5 py-1 rounded-full font-semibold flex-shrink-0 ${badge.badge}`;
                        badgeEl.innerHTML = `<i class="fas ${badge.icon} mr-1"></i>${badge.label}`;

                        const borderMap = {
                            submitted:      ['border-blue-200',  'bg-blue-50'],
                            resubmitted:    ['border-blue-200',  'bg-blue-50'],
                            not_applicable: ['border-gray-200',  'bg-gray-50'],
                            approved:       ['border-green-300', 'bg-green-50'],
                            rejected:       ['border-red-300',   'bg-red-50'],
                        };
                        card.className = card.className.replace(/border-\S+/g, '').replace(/bg-\S+-50/g, '').trim();
                        const [border, bg] = borderMap[info.status] ?? ['border-blue-200', 'bg-blue-50'];
                        card.classList.add('rounded-xl', 'border-2', border, bg, 'overflow-hidden', 'transition-all');

                        if (info.status === 'rejected' && info.rejection_reason) {
                            let reasonEl = card.querySelector('.rejection-reason-block');
                            if (!reasonEl) {
                                reasonEl = document.createElement('div');
                                reasonEl.className = 'rejection-reason-block mt-2 bg-red-100 border border-red-200 rounded-lg px-3 py-2 flex items-start gap-2';
                                card.querySelector('.min-w-0.flex-1')?.appendChild(reasonEl);
                            }
                            reasonEl.innerHTML = `<i class="fas fa-exclamation-circle text-red-500 mt-0.5 flex-shrink-0 text-xs"></i><div><p class="text-xs font-semibold text-red-700">Reason for rejection:</p><p class="text-xs text-red-600 mt-0.5">${info.rejection_reason}</p></div>`;
                            if (!card.querySelector('.doc-file-input')) {
                                const uploadDiv = document.createElement('div');
                                uploadDiv.className = 'mt-3';
                                uploadDiv.innerHTML = `
                                    <label class="block text-xs font-medium text-red-700 mb-1"><i class="fas fa-redo mr-1"></i>Reupload Document</label>
                                    <input type="file" data-key="${key}" data-required="1" data-url="${card.dataset.uploadUrl ?? ''}"
                                        accept=".jpg,.jpeg,.png,.pdf"
                                        class="doc-file-input w-full text-xs text-gray-600 border border-red-200 rounded-lg px-3 py-2 bg-white file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:text-xs file:bg-red-50 file:text-red-600">
                                    <p class="text-xs text-gray-400 mt-1">JPG, PNG, PDF — Max 5MB</p>
                                    <p class="text-xs text-red-500 mt-0.5 file-selected-label hidden"><i class="fas fa-check mr-1"></i>File selected</p>
                                    <p class="text-xs text-red-600 mt-1 hidden file-error-label"></p>`;
                                card.querySelector('.min-w-0.flex-1')?.appendChild(uploadDiv);
                            }
                        }
                        if (info.status === 'approved') {
                            card.querySelector('.doc-file-input')?.closest('div.mt-3')?.remove();
                            card.querySelector('[id^="na-"]')?.closest('div.mt-2')?.remove();
                            card.querySelector('.rejection-reason-block')?.remove();
                        }
                        showToast('"' + (info.file_name ?? key) + '" — ' + badge.label, info.status === 'approved' ? 'green' : 'red');
                    }
                }
            }

            // ── Update Auto Document Check grid item ──
            const autoItem = document.getElementById('auto-check-' + key);
            if (autoItem) {
                const autoMap = {
                    submitted:      { icon: 'fa-cloud-upload-alt', iconColor: 'text-blue-400',   bg: 'bg-blue-50',   border: 'border-l-4 border-blue-400',  badge: 'bg-blue-100 text-blue-700',   label: 'Uploaded' },
                    resubmitted:    { icon: 'fa-sync',             iconColor: 'text-blue-400',   bg: 'bg-blue-50',   border: 'border-l-4 border-blue-400',  badge: 'bg-blue-100 text-blue-700',   label: 'Resubmitted' },
                    approved:       { icon: 'fa-check-circle',     iconColor: 'text-green-500',  bg: 'bg-green-50',  border: 'border-l-4 border-green-400', badge: 'bg-green-100 text-green-700', label: 'Approved' },
                    rejected:       { icon: 'fa-times-circle',     iconColor: 'text-red-500',    bg: 'bg-red-50',    border: 'border-l-4 border-red-400',   badge: 'bg-red-100 text-red-700',     label: 'Rejected' },
                    not_applicable: { icon: 'fa-minus-circle',     iconColor: 'text-gray-400',   bg: 'bg-gray-50',   border: 'border-l-4 border-gray-300',  badge: 'bg-gray-100 text-gray-500',   label: 'N/A' },
                };
                const am = autoMap[info.status];
                if (am) {
                    // Update bg + border
                    autoItem.className = `rounded-xl border p-3 ${am.bg} ${am.border}`;
                    // Update icon
                    const iconEl = autoItem.querySelector('i.fas');
                    if (iconEl) iconEl.className = `fas ${am.icon} ${am.iconColor} text-lg`;
                    // Update badge
                    const badgeSpan = autoItem.querySelector('span.rounded-full');
                    if (badgeSpan) {
                        badgeSpan.className = `text-[10px] px-2.5 py-1 rounded-full font-semibold ${am.badge}`;
                        badgeSpan.textContent = am.label;
                    }
                    // Update file name
                    if (info.file_name) {
                        let fnEl = autoItem.querySelector('.auto-filename');
                        if (!fnEl) {
                            fnEl = document.createElement('p');
                            fnEl.className = 'auto-filename text-xs text-gray-500 mt-1 truncate';
                            autoItem.querySelector('.min-w-0 p')?.after(fnEl);
                        }
                        fnEl.innerHTML = `<i class="fas fa-paperclip mr-1"></i>${info.file_name}`;
                    }
                    if (info.status === 'approved') approvedCount++;
                }
            }
        });

        // ── Update score + summary badges ──
        const allAutoItems = document.querySelectorAll('[id^="auto-check-"]:not(#auto-check-score):not(#auto-check-missing):not(#auto-check-complete)');
        const totalAuto    = allAutoItems.length;
        const approvedAuto = [...allAutoItems].filter(el => el.querySelector('.bg-green-50') !== null || el.classList.contains('bg-green-50')).length;
        const missingAuto  = [...allAutoItems].filter(el => el.classList.contains('bg-gray-50') && !el.classList.contains('bg-green-50')).length;

        const scoreEl   = document.getElementById('auto-check-score');
        const missingEl = document.getElementById('auto-check-missing');
        const completeEl= document.getElementById('auto-check-complete');
        const pct = totalAuto > 0 ? Math.round((approvedAuto / totalAuto) * 100) : 0;

        if (scoreEl) scoreEl.textContent = 'Score ' + pct + '%';
        if (missingEl) {
            if (missingAuto > 0) {
                missingEl.className = 'inline-flex items-center gap-1 rounded-full bg-red-50 text-red-700 px-3 py-1';
                missingEl.textContent = missingAuto + ' missing';
            } else {
                missingEl.className = 'inline-flex items-center gap-1 rounded-full bg-green-50 text-green-700 px-3 py-1';
                missingEl.textContent = 'No missing documents';
            }
        }
        if (completeEl && missingAuto === 0) {
            completeEl.className = 'inline-flex items-center gap-1 rounded-full bg-green-100 text-green-700 px-3 py-1';
            completeEl.textContent = 'Upload complete';
        }

    } catch (e) {}
}

setInterval(pollDocumentStatuses, 5000);
</script>
@endpush
