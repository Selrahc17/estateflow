{{--
    Reusable Document Checklist Partial
    Variables:
      $docCheck  — result from DocumentCheckerService::check($reservation)
      $reservation — the Reservation model
      $showUploadLink — bool, whether to show upload button (default true)
--}}
@php $showUploadLink = $showUploadLink ?? true; @endphp

<div class="bg-white rounded-xl shadow-sm p-6">
    {{-- Header --}}
    <div class="flex items-center justify-between mb-4">
        <h3 class="font-semibold text-gray-800 text-sm flex items-center gap-2">
            <i class="fas fa-clipboard-check text-indigo-500"></i>
            Document Checklist
        </h3>
        {{-- Score Badge --}}
        @php
            $score = $docCheck['score'];
            $scoreColor = $score === 100 ? 'bg-green-100 text-green-700' : ($score >= 50 ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700');
        @endphp
        <span class="text-xs px-2.5 py-1 rounded-full font-semibold {{ $scoreColor }}">
            {{ $score }}% Complete
        </span>
    </div>

    {{-- Progress Bar --}}
    <div class="w-full bg-gray-100 rounded-full h-2 mb-4">
        <div class="h-2 rounded-full transition-all duration-500
            {{ $score === 100 ? 'bg-green-500' : ($score >= 50 ? 'bg-yellow-400' : 'bg-red-400') }}"
            style="width: {{ $score }}%">
        </div>
    </div>

    {{-- Summary --}}
    <div class="flex items-center gap-4 text-xs text-gray-500 mb-4">
        <span><span class="font-semibold text-gray-700">{{ $docCheck['complete'] }}</span> of {{ $docCheck['required'] }} required</span>
        @if($docCheck['missing'] > 0)
            <span class="text-red-500 font-medium"><i class="fas fa-exclamation-circle mr-1"></i>{{ $docCheck['missing'] }} missing</span>
        @endif
        @if($docCheck['is_ready'])
            <span class="text-green-600 font-medium"><i class="fas fa-check-circle mr-1"></i>Document-ready</span>
        @endif
    </div>

    {{-- Checklist Items --}}
    <div class="space-y-2">
        @foreach($docCheck['results'] as $item)
        @php
            $statusConfig = match($item['status']) {
                'verified'             => ['icon' => 'fa-check-circle',       'color' => 'text-green-500',  'bg' => 'bg-green-50',  'badge' => 'bg-green-100 text-green-700',  'label' => 'Verified'],
                'submitted',
                'pending_verification' => ['icon' => 'fa-clock',              'color' => 'text-yellow-500', 'bg' => 'bg-yellow-50', 'badge' => 'bg-yellow-100 text-yellow-700', 'label' => 'Uploaded'],
                'expiring_soon'        => ['icon' => 'fa-exclamation-circle', 'color' => 'text-orange-500', 'bg' => 'bg-orange-50', 'badge' => 'bg-orange-100 text-orange-700', 'label' => 'Expiring Soon'],
                'expired'              => ['icon' => 'fa-times-circle',       'color' => 'text-red-500',    'bg' => 'bg-red-50',    'badge' => 'bg-red-100 text-red-700',       'label' => 'Expired'],
                default                => $item['document']
                                          ? ['icon' => 'fa-clock',          'color' => 'text-yellow-500', 'bg' => 'bg-yellow-50', 'badge' => 'bg-yellow-100 text-yellow-700', 'label' => 'Uploaded']
                                          : ['icon' => 'fa-minus-circle',   'color' => 'text-gray-400',   'bg' => 'bg-gray-50',   'badge' => 'bg-gray-100 text-gray-500',     'label' => 'Missing'],
            };
        @endphp
        <div class="flex items-center justify-between px-3 py-2.5 rounded-lg {{ $statusConfig['bg'] }}">
            <div class="flex items-center gap-2.5">
                <i class="fas {{ $statusConfig['icon'] }} {{ $statusConfig['color'] }} text-sm flex-shrink-0"></i>
                <div>
                    <p class="text-sm font-medium text-gray-800">{{ $item['label'] }}</p>
                    @if($item['document'] && $item['document']->expiry_date)
                        <p class="text-xs text-gray-400">Expires: {{ $item['document']->expiry_date->format('M d, Y') }}</p>
                    @endif
                    @if($item['is_duplicate'])
                        <p class="text-xs text-orange-500"><i class="fas fa-copy mr-1"></i>{{ $item['count'] }} copies uploaded</p>
                    @endif
                </div>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-xs px-2 py-0.5 rounded-full font-medium {{ $statusConfig['badge'] }}">
                    {{ $statusConfig['label'] }}
                </span>
                @if($item['document'])
                    <a href="{{ route('documents.download', $item['document']) }}"
                        class="text-xs text-indigo-500 hover:text-indigo-700 transition"
                        title="Download">
                        <i class="fas fa-download"></i>
                    </a>
                @endif
            </div>
        </div>
        @endforeach
    </div>

    {{-- Upload Link --}}
    @if($showUploadLink && $docCheck['missing'] > 0)
    <div class="mt-4 pt-4 border-t border-gray-100">
        <a href="{{ route('documents.create') }}?documentable_type=reservation&documentable_id={{ $reservation->id }}"
            class="flex items-center justify-center gap-2 w-full bg-indigo-600 text-white py-2 rounded-lg text-sm hover:bg-indigo-700 transition font-medium">
            <i class="fas fa-upload"></i> Upload Missing Document
        </a>
    </div>
    @endif
</div>
