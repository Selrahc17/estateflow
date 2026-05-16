@extends('layouts.app')
@section('title', 'Pending RF Verification — EstateFlow')
@section('page-title', 'Pending RF Verification')
@section('page-subtitle', 'Reservations awaiting Reservation Fee verification')
@section('content')

@if(session('success'))
    <div class="mb-4 bg-green-50 border border-green-200 text-green-700 text-sm px-4 py-3 rounded-xl flex items-center gap-2">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
    </div>
@endif

<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-100">
            <tr>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Client</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Property</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">RF Deadline</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Proof</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Action</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @forelse($reservations as $res)
            <tr class="hover:bg-gray-50 transition">
                <td class="px-5 py-4">
                    <p class="font-medium text-gray-800">{{ $res->client->full_name ?? '—' }}</p>
                    <p class="text-xs text-gray-400">{{ $res->client->phone ?? '' }}</p>
                </td>
                <td class="px-5 py-4">
                    <p class="font-medium text-gray-800">{{ $res->property->title ?? '—' }}</p>
                    @if($res->property?->block)
                        <p class="text-xs text-gray-400">Blk {{ $res->property->block }}, Lot {{ $res->property->lot }}</p>
                    @endif
                </td>
                <td class="px-5 py-4">
                    @if($res->rf_deadline)
                        <p class="text-sm {{ $res->rf_deadline->isPast() ? 'text-red-600 font-medium' : 'text-gray-700' }}">
                            {{ $res->rf_deadline->format('M d, Y') }}
                            @if($res->rf_deadline->isPast()) <span class="text-xs">(Overdue)</span> @endif
                        </p>
                    @else
                        <span class="text-gray-400 text-xs">Not set</span>
                    @endif
                </td>
                <td class="px-5 py-4">
                    @if($res->proof_of_payment)
                        <a href="{{ asset('storage/' . $res->proof_of_payment) }}" target="_blank"
                            class="text-xs text-indigo-600 hover:underline">
                            <i class="fas fa-file mr-1"></i>View Proof
                        </a>
                    @else
                        <span class="text-xs text-gray-400">—</span>
                    @endif
                </td>
                <td class="px-5 py-4">
                    <form method="POST" action="{{ route('reservations.verify-rf', $res) }}" class="flex items-center gap-2">
                        @csrf @method('PATCH')
                        <input type="text" name="rf_or_number" placeholder="OR Number" required
                            class="px-3 py-1.5 border border-gray-200 rounded-lg text-xs focus:outline-none focus:ring-2 focus:ring-indigo-500 w-32">
                        <button type="submit"
                            class="bg-green-600 text-white px-3 py-1.5 rounded-lg text-xs hover:bg-green-700 transition font-medium whitespace-nowrap">
                            Verify RF
                        </button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="px-5 py-16 text-center text-gray-400">
                    <i class="fas fa-check-circle text-4xl mb-3 block text-gray-200"></i>
                    No pending RF verifications.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@endsection
