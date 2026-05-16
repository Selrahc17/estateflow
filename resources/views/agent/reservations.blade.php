@extends('layouts.app')

@section('title', 'My Reservations - EstateFlow')
@section('page-title', 'My Reservations')
@section('page-subtitle', 'Reservations assigned to you')

@section('content')

{{-- Stats --}}
<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
    <div class="bg-white rounded-xl shadow-sm p-6 flex items-center gap-4">
        <div class="w-12 h-12 bg-yellow-100 rounded-xl flex items-center justify-center">
            <i class="fas fa-clock text-yellow-600 text-xl"></i>
        </div>
        <div>
            <p class="text-sm text-gray-500">Pending</p>
            <p class="text-2xl font-bold text-gray-800">{{ $pendingCount }}</p>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-6 flex items-center gap-4">
        <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
            <i class="fas fa-check-circle text-green-600 text-xl"></i>
        </div>
        <div>
            <p class="text-sm text-gray-500">Confirmed</p>
            <p class="text-2xl font-bold text-gray-800">{{ $confirmedCount }}</p>
        </div>
    </div>
</div>

@if(!$agentRecord && !auth()->user()->isAdmin())
<div class="mb-6 bg-yellow-50 border border-yellow-200 rounded-xl p-4">
    <p class="text-sm text-yellow-800">Your account is not linked to an agent profile. Contact an administrator.</p>
</div>
@endif

<div class="space-y-4">
    @forelse($reservations as $res)
    <div class="bg-white rounded-xl shadow-sm p-5">
        <div class="flex items-start justify-between gap-4">

            {{-- Left: Info --}}
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 mb-1">
                    <p class="font-semibold text-gray-800">{{ $res->property->title ?? '—' }}</p>
                    <span class="text-xs px-2.5 py-0.5 rounded-full font-medium flex-shrink-0
                        {{ $res->status === 'confirmed' ? 'bg-green-100 text-green-700'   : '' }}
                        {{ $res->status === 'pending'   ? 'bg-yellow-100 text-yellow-700' : '' }}
                        {{ $res->status === 'cancelled' ? 'bg-red-100 text-red-700'       : '' }}
                        {{ $res->status === 'completed' ? 'bg-blue-100 text-blue-700'     : '' }}
                        {{ $res->status === 'expired'   ? 'bg-gray-100 text-gray-600'     : '' }}">
                        {{ ucfirst($res->status) }}
                    </span>
                </div>
                <div class="flex flex-wrap gap-4 text-xs text-gray-500 mt-1">
                    <span><i class="fas fa-user mr-1"></i>{{ $res->client->full_name ?? '—' }}</span>
                    <span><i class="fas fa-calendar mr-1"></i>{{ $res->reservation_date->format('M d, Y') }}</span>
                    <span><i class="fas fa-peso-sign mr-1"></i>Fee: ₱{{ number_format($res->reservation_fee, 0) }}</span>
                    @if($res->payments->count())
                        <span class="text-green-600"><i class="fas fa-receipt mr-1"></i>₱{{ number_format($res->payments->where('status','completed')->sum('amount'), 0) }} paid</span>
                    @endif
                </div>
            </div>

            {{-- Right: Actions --}}
            <div class="flex items-center gap-2 flex-shrink-0">
                <a href="{{ route('reservations.show', $res) }}"
                    class="flex items-center gap-1.5 px-4 py-2 bg-gray-100 text-gray-600 text-xs font-semibold rounded-lg hover:bg-gray-200 transition">
                    <i class="fas fa-eye"></i> View
                </a>
            </div>
        </div>
    </div>
    @empty
    <div class="bg-white rounded-xl shadow-sm p-16 text-center text-gray-400">
        <i class="fas fa-calendar-check text-4xl mb-3 block text-gray-200"></i>
        No reservations assigned to you yet.
    </div>
    @endforelse
</div>

@if(method_exists($reservations, 'hasPages') && $reservations->hasPages())
    <div class="mt-6">{{ $reservations->links() }}</div>
@endif

@endsection
