@extends('layouts.app')

@section('title', 'Data Retention — EstateFlow')
@section('page-title', 'Data Retention & Privacy')
@section('page-subtitle', 'Manage client data privacy and retention policies')

@section('content')

@if(session('success'))
    <div class="mb-4 bg-green-50 border border-green-200 text-green-700 text-sm px-4 py-3 rounded-xl flex items-center gap-2">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
    </div>
@endif

{{-- Info Banner --}}
<div class="bg-indigo-50 border border-indigo-200 rounded-xl p-4 mb-6 flex items-start gap-3">
    <i class="fas fa-shield-alt text-indigo-500 mt-0.5 text-lg"></i>
    <div>
        <p class="text-sm font-semibold text-indigo-800">7-Day Grace Period Policy</p>
        <p class="text-xs text-indigo-600 mt-1">When a reservation is cancelled, the client has <strong>7 days</strong> to reconsider. After 7 days with no reactivation, all personal data (documents, ID details, contact info) is automatically wiped. Only an anonymized audit log entry is kept.</p>
    </div>
</div>

{{-- Stats --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-xl shadow-sm p-4 text-center">
        <p class="text-2xl font-bold text-yellow-600">{{ $pendingWipe }}</p>
        <p class="text-xs text-gray-500 mt-1">Pending Wipe</p>
        <p class="text-xs text-gray-400">(within grace period)</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-4 text-center">
        <p class="text-2xl font-bold text-red-600">{{ $overdueWipe }}</p>
        <p class="text-xs text-gray-500 mt-1">Overdue Wipe</p>
        <p class="text-xs text-gray-400">(grace period passed)</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-4 text-center">
        <p class="text-2xl font-bold text-green-600">{{ $wipedCount }}</p>
        <p class="text-xs text-gray-500 mt-1">Already Wiped</p>
        <p class="text-xs text-gray-400">(data removed)</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-4 text-center">
        <form method="POST" action="{{ route('retention.run') }}">
            @csrf
            <button type="submit" class="w-full">
                <p class="text-2xl font-bold text-indigo-600"><i class="fas fa-play"></i></p>
                <p class="text-xs text-gray-500 mt-1">Run Wipe Now</p>
                <p class="text-xs text-gray-400">(manual trigger)</p>
            </button>
        </form>
    </div>
</div>

{{-- Pending Wipe Table --}}
<div class="bg-white rounded-xl shadow-sm overflow-hidden mb-6">
    <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
        <h3 class="font-semibold text-gray-800 flex items-center gap-2">
            <i class="fas fa-clock text-yellow-500"></i> Cancelled Reservations — Grace Period Active
        </h3>
        <span class="text-xs text-gray-400">Data will be wiped after 7 days from cancellation</span>
    </div>
    <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-100">
            <tr>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Reservation</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Client</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Cancelled At</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Wipe Scheduled</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Days Left</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Action</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @forelse($gracePeriodReservations as $res)
            @php $daysLeft = now()->diffInDays($res->gracePeriodEndsAt(), false); @endphp
            <tr class="hover:bg-gray-50 transition">
                <td class="px-5 py-4">
                    <p class="font-medium text-gray-800">#{{ $res->id }}</p>
                    <p class="text-xs text-gray-400">{{ $res->property->title ?? '—' }}</p>
                </td>
                <td class="px-5 py-4">
                    <p class="font-medium text-gray-800">{{ $res->client->full_name ?? '—' }}</p>
                    <p class="text-xs text-gray-400">{{ $res->client->email ?? '' }}</p>
                </td>
                <td class="px-5 py-4 text-xs text-gray-500">
                    {{ $res->cancelled_at->format('M d, Y h:i A') }}
                </td>
                <td class="px-5 py-4 text-xs text-gray-500">
                    {{ $res->gracePeriodEndsAt()->format('M d, Y h:i A') }}
                </td>
                <td class="px-5 py-4">
                    <span class="text-xs font-semibold {{ $daysLeft <= 1 ? 'text-red-600' : 'text-yellow-600' }}">
                        {{ max(0, $daysLeft) }} day(s)
                    </span>
                </td>
                <td class="px-5 py-4">
                    <a href="{{ route('reservations.edit', $res) }}"
                        class="text-xs bg-green-100 text-green-700 px-3 py-1.5 rounded-lg hover:bg-green-200 transition">
                        <i class="fas fa-undo mr-1"></i>Reactivate
                    </a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="px-5 py-10 text-center text-gray-400 text-sm">
                    <i class="fas fa-check-circle text-gray-200 text-3xl mb-2 block"></i>
                    No reservations in grace period.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- Overdue Wipe Table --}}
@if($overdueReservations->count())
<div class="bg-white rounded-xl shadow-sm overflow-hidden mb-6 border-l-4 border-red-400">
    <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
        <h3 class="font-semibold text-gray-800 flex items-center gap-2">
            <i class="fas fa-exclamation-triangle text-red-500"></i> Overdue — Awaiting Wipe
        </h3>
        <span class="text-xs text-red-500">Grace period has passed — run wipe to process these</span>
    </div>
    <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-100">
            <tr>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Reservation</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Client</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Cancelled At</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Overdue By</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @foreach($overdueReservations as $res)
            @php $overdueDays = abs(now()->diffInDays($res->gracePeriodEndsAt())); @endphp
            <tr class="hover:bg-red-50 transition">
                <td class="px-5 py-4">
                    <p class="font-medium text-gray-800">#{{ $res->id }}</p>
                    <p class="text-xs text-gray-400">{{ $res->property->title ?? '—' }}</p>
                </td>
                <td class="px-5 py-4">
                    <p class="font-medium text-gray-800">{{ $res->client->full_name ?? '—' }}</p>
                    <p class="text-xs text-gray-400">{{ $res->client->email ?? '' }}</p>
                </td>
                <td class="px-5 py-4 text-xs text-gray-500">{{ $res->cancelled_at->format('M d, Y') }}</td>
                <td class="px-5 py-4">
                    <span class="text-xs font-semibold text-red-600">{{ $overdueDays }} day(s) overdue</span>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

{{-- Already Wiped Table --}}
<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-100">
        <h3 class="font-semibold text-gray-800 flex items-center gap-2">
            <i class="fas fa-check-circle text-green-500"></i> Wiped Records
            <span class="text-xs text-gray-400 font-normal">(personal data removed, audit trail kept)</span>
        </h3>
    </div>
    <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-100">
            <tr>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Reservation</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Property</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Cancelled At</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Data Wiped At</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @forelse($wipedReservations as $res)
            <tr class="hover:bg-gray-50 transition opacity-60">
                <td class="px-5 py-4">
                    <p class="font-medium text-gray-500">#{{ $res->id }}</p>
                </td>
                <td class="px-5 py-4 text-xs text-gray-400">{{ $res->property->title ?? '—' }}</td>
                <td class="px-5 py-4 text-xs text-gray-400">{{ $res->cancelled_at?->format('M d, Y') ?? '—' }}</td>
                <td class="px-5 py-4">
                    <span class="text-xs text-green-600 font-medium">
                        <i class="fas fa-check mr-1"></i>{{ $res->data_wiped_at->format('M d, Y h:i A') }}
                    </span>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="px-5 py-10 text-center text-gray-400 text-sm">No wiped records yet.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    @if($wipedReservations->hasPages())
        <div class="px-5 py-4 border-t border-gray-100">{{ $wipedReservations->links() }}</div>
    @endif
</div>

@endsection
