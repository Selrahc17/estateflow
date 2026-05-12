@extends('layouts.app')

@section('title', 'Smart Document Checker - EstateFlow')
@section('page-title', 'Smart Document Checker')
@section('page-subtitle', 'Document compliance status across all reservations')

@section('content')

{{-- Filters --}}
<div class="bg-white rounded-xl shadow-sm p-5 mb-6">
    <form method="GET" action="{{ route('documents.checker') }}" class="flex flex-wrap gap-3 items-end">
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Search Client</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Client name..."
                class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 w-52">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Reservation Status</label>
            <select name="status" class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">All Statuses</option>
                @foreach(['pending' => 'Pending', 'confirmed' => 'Confirmed', 'completed' => 'Completed', 'cancelled' => 'Cancelled'] as $val => $label)
                    <option value="{{ $val }}" {{ request('status') === $val ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-indigo-700 transition">
            <i class="fas fa-search mr-1"></i> Filter
        </button>
        <a href="{{ route('documents.checker') }}" class="bg-gray-100 text-gray-600 px-4 py-2 rounded-lg text-sm hover:bg-gray-200 transition">Reset</a>
    </form>
</div>

{{-- Reservation Cards --}}
<div class="space-y-4">
    @forelse($reservations as $reservation)
    @php $check = $checklist[$reservation->id]; @endphp
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">

        {{-- Card Header --}}
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-50">
            <div class="flex items-center gap-4">
                <div class="w-10 h-10 bg-indigo-50 rounded-xl flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-building text-indigo-400"></i>
                </div>
                <div>
                    <p class="font-semibold text-gray-800">{{ $reservation->property->title ?? '—' }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">
                        Client: <span class="font-medium text-gray-600">{{ $reservation->client->full_name ?? '—' }}</span>
                        · Agent: <span class="font-medium text-gray-600">{{ $reservation->agent->full_name ?? 'Unassigned' }}</span>
                        · <span class="capitalize">{{ $reservation->status }}</span>
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                {{-- Score Badge --}}
                @php
                    $score = $check['score'];
                    $scoreColor = $score === 100 ? 'bg-green-100 text-green-700' : ($score >= 50 ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700');
                @endphp
                <div class="text-center">
                    <span class="text-xs px-3 py-1 rounded-full font-semibold {{ $scoreColor }}">
                        {{ $score }}% Complete
                    </span>
                    <p class="text-xs text-gray-400 mt-1">{{ $check['complete'] }}/{{ $check['required'] }} docs</p>
                </div>
                <a href="{{ route('reservations.show', $reservation) }}"
                    class="text-xs bg-indigo-50 text-indigo-600 px-3 py-1.5 rounded-lg hover:bg-indigo-100 transition">
                    View Reservation
                </a>
            </div>
        </div>

        {{-- Progress Bar --}}
        <div class="px-6 pt-3">
            <div class="w-full bg-gray-100 rounded-full h-1.5">
                <div class="h-1.5 rounded-full {{ $score === 100 ? 'bg-green-500' : ($score >= 50 ? 'bg-yellow-400' : 'bg-red-400') }}"
                    style="width: {{ $score }}%"></div>
            </div>
        </div>

        {{-- Document Items --}}
        <div class="px-6 py-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2">
            @foreach($check['results'] as $item)
            @php
                $statusConfig = match($item['status']) {
                    'verified'             => ['icon' => 'fa-check-circle',       'color' => 'text-green-500',  'bg' => 'bg-green-50',  'badge' => 'bg-green-100 text-green-700',  'label' => 'Verified'],
                    'pending_verification' => ['icon' => 'fa-clock',              'color' => 'text-yellow-500', 'bg' => 'bg-yellow-50', 'badge' => 'bg-yellow-100 text-yellow-700', 'label' => 'Pending'],
                    'expiring_soon'        => ['icon' => 'fa-exclamation-circle', 'color' => 'text-orange-500', 'bg' => 'bg-orange-50', 'badge' => 'bg-orange-100 text-orange-700', 'label' => 'Expiring Soon'],
                    'expired'              => ['icon' => 'fa-times-circle',       'color' => 'text-red-500',    'bg' => 'bg-red-50',    'badge' => 'bg-red-100 text-red-700',       'label' => 'Expired'],
                    default                => ['icon' => 'fa-minus-circle',       'color' => 'text-gray-400',   'bg' => 'bg-gray-50',   'badge' => 'bg-gray-100 text-gray-500',     'label' => 'Missing'],
                };
            @endphp
            <div class="flex items-center gap-2 px-3 py-2 rounded-lg {{ $statusConfig['bg'] }}">
                <i class="fas {{ $statusConfig['icon'] }} {{ $statusConfig['color'] }} text-sm flex-shrink-0"></i>
                <div class="flex-1 min-w-0">
                    <p class="text-xs font-medium text-gray-700 truncate">{{ $item['label'] }}</p>
                </div>
                <span class="text-xs px-1.5 py-0.5 rounded-full font-medium {{ $statusConfig['badge'] }} flex-shrink-0">
                    {{ $statusConfig['label'] }}
                </span>
                @if($item['document'])
                    <a href="{{ route('documents.download', $item['document']) }}" class="text-indigo-400 hover:text-indigo-600 flex-shrink-0" title="Download">
                        <i class="fas fa-download text-xs"></i>
                    </a>
                @endif
            </div>
            @endforeach
        </div>

        @if($check['missing'] > 0)
        <div class="px-6 pb-4">
            <a href="{{ route('documents.create') }}?documentable_type=reservation&documentable_id={{ $reservation->id }}"
                class="inline-flex items-center gap-1.5 text-xs bg-indigo-600 text-white px-3 py-1.5 rounded-lg hover:bg-indigo-700 transition">
                <i class="fas fa-upload"></i> Upload Missing Document
            </a>
        </div>
        @endif

    </div>
    @empty
    <div class="bg-white rounded-xl shadow-sm p-16 text-center text-gray-400">
        <i class="fas fa-clipboard-check text-4xl mb-3 block text-gray-200"></i>
        <p>No reservations found.</p>
    </div>
    @endforelse
</div>

@if($reservations->hasPages())
    <div class="mt-6">{{ $reservations->links() }}</div>
@endif

@endsection
