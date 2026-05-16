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
<div class="space-y-3">
    @foreach($checklist as $index => $item)
    @php
        $key        = $item['key'];
        $label      = $item['label'];
        $conditional = $item['conditional'] ?? false;
        $doc        = $checklistDocs->get($key);
        $status     = $doc ? $doc->checklist_status : null;

        $badgeConfig = [
            null             => ['bg' => 'bg-gray-100',   'text' => 'text-gray-500',   'icon' => 'fa-circle',       'label' => 'Not yet uploaded'],
            'submitted'      => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-700', 'icon' => 'fa-clock',        'label' => 'Under Review'],
            'approved'       => ['bg' => 'bg-green-100',  'text' => 'text-green-700',  'icon' => 'fa-check-circle', 'label' => 'Approved'],
            'rejected'       => ['bg' => 'bg-red-100',    'text' => 'text-red-700',    'icon' => 'fa-times-circle', 'label' => 'Rejected — Resubmit'],
            'resubmitted'    => ['bg' => 'bg-blue-100',   'text' => 'text-blue-700',   'icon' => 'fa-sync',         'label' => 'Resubmitted — Under Review'],
            'not_applicable' => ['bg' => 'bg-gray-100',   'text' => 'text-gray-500',   'icon' => 'fa-minus-circle', 'label' => 'Not Applicable'],
        ];
        $badge = $badgeConfig[$status] ?? $badgeConfig[null];
        $canUpload = in_array($status, [null, 'rejected']);
    @endphp

    <div class="bg-white rounded-xl shadow-sm overflow-hidden {{ $status === 'approved' ? 'border-l-4 border-green-400' : ($status === 'rejected' ? 'border-l-4 border-red-400' : 'border-l-4 border-gray-200') }}">
        <div class="p-4">
            <div class="flex items-start justify-between gap-3">
                <div class="flex items-start gap-3 flex-1 min-w-0">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0 text-sm font-bold
                        {{ $status === 'approved' ? 'bg-green-100 text-green-700' : 'bg-indigo-100 text-indigo-600' }}">
                        {{ $index + 1 }}
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-gray-800">{{ $label }}</p>
                        @if($conditional)
                            <span class="text-xs text-gray-400 italic">if applicable</span>
                        @endif
                        @if($doc && $doc->file_name && $status !== 'not_applicable')
                            <p class="text-xs text-gray-400 mt-1">
                                <i class="fas fa-paperclip mr-1"></i>{{ $doc->file_name }}
                                <span class="ml-2 text-gray-300">{{ $doc->file_size_formatted }}</span>
                            </p>
                        @endif
                        @if($status === 'not_applicable' && $doc->not_applicable_reason)
                            <p class="text-xs text-gray-400 mt-1"><i class="fas fa-info-circle mr-1"></i>{{ $doc->not_applicable_reason }}</p>
                        @endif
                    </div>
                </div>
                <span class="text-xs px-2.5 py-1 rounded-full font-medium flex-shrink-0 {{ $badge['bg'] }} {{ $badge['text'] }}">
                    <i class="fas {{ $badge['icon'] }} mr-1"></i>{{ $badge['label'] }}
                </span>
            </div>

            {{-- Rejection Reason --}}
            @if($status === 'rejected' && $doc->rejection_reason)
            <div class="mt-3 bg-red-50 border border-red-100 rounded-lg p-3 flex items-start gap-2">
                <i class="fas fa-exclamation-circle text-red-400 mt-0.5 flex-shrink-0"></i>
                <div>
                    <p class="text-xs font-semibold text-red-700">Reason for rejection:</p>
                    <p class="text-xs text-red-600 mt-0.5">{{ $doc->rejection_reason }}</p>
                </div>
            </div>
            @endif

            {{-- Upload Form --}}
            @if($canUpload && $status !== 'not_applicable')
            <form method="POST" action="{{ route('documents.checklist.upload', [$activeReservation, $key]) }}"
                enctype="multipart/form-data" class="mt-3 flex items-center gap-2">
                @csrf
                <input type="file" name="file" accept=".jpg,.jpeg,.png,.pdf" required
                    class="flex-1 text-xs text-gray-600 border border-gray-200 rounded-lg px-3 py-2 bg-gray-50
                    file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:text-xs file:bg-indigo-50 file:text-indigo-600">
                <button type="submit"
                    class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-xs hover:bg-indigo-700 transition font-medium whitespace-nowrap">
                    <i class="fas fa-upload mr-1"></i>{{ $status === 'rejected' ? 'Resubmit' : 'Upload' }}
                </button>
            </form>
            <p class="text-xs text-gray-400 mt-1 ml-11">JPG, PNG, PDF — Max 5MB</p>
            @endif

            {{-- Not Applicable Toggle (conditional docs only) --}}
            @if($conditional && in_array($status, [null, 'rejected']))
            <div class="mt-2 ml-11">
                <button type="button" onclick="toggleNaForm('na-{{ $key }}')"
                    class="text-xs text-gray-400 hover:text-gray-600 transition underline">
                    Mark as not applicable
                </button>
                <div id="na-{{ $key }}" class="hidden mt-2">
                    <form method="POST" action="{{ route('documents.checklist.not-applicable', [$activeReservation, $key]) }}"
                        class="flex items-center gap-2">
                        @csrf
                        <input type="text" name="reason" required placeholder="Reason (e.g. Single, not married)"
                            class="flex-1 text-xs border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-gray-300">
                        <button type="submit"
                            class="bg-gray-500 text-white px-3 py-2 rounded-lg text-xs hover:bg-gray-600 transition whitespace-nowrap">
                            Confirm
                        </button>
                    </form>
                </div>
            </div>
            @endif

        </div>
    </div>
    @endforeach
</div>
@endif

@endif

@endsection

@push('scripts')
<script>
function toggleNaForm(id) {
    const el = document.getElementById(id);
    el.classList.toggle('hidden');
}
</script>
@endpush
