@extends('layouts.app')

@section('title', 'Site Viewing Schedules — EstateFlow')
@section('page-title', 'Site Viewing Schedules')
@section('page-subtitle', 'Manage client site viewing requests')

@section('content')

@if(session('success'))
    <div class="mb-4 bg-green-50 border border-green-200 text-green-700 text-sm px-4 py-3 rounded-xl flex items-center gap-2">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
    </div>
@endif

{{-- Stats --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-xl shadow-sm p-4 text-center">
        <p class="text-2xl font-bold text-yellow-600">{{ $pendingCount }}</p>
        <p class="text-xs text-gray-500 mt-1">Pending</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-4 text-center">
        <p class="text-2xl font-bold text-green-600">{{ $confirmedCount }}</p>
        <p class="text-xs text-gray-500 mt-1">Confirmed</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-4 text-center">
        <p class="text-2xl font-bold text-blue-600">{{ $schedules->total() }}</p>
        <p class="text-xs text-gray-500 mt-1">Total</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-4 text-center">
        <p class="text-2xl font-bold text-gray-600">{{ \App\Models\SiteViewingSchedule::where('status','completed')->count() }}</p>
        <p class="text-xs text-gray-500 mt-1">Completed</p>
    </div>
</div>

{{-- Filter --}}
<div class="flex items-center gap-2 mb-6 flex-wrap">
    @foreach(['' => 'All', 'pending' => 'Pending', 'confirmed' => 'Confirmed', 'completed' => 'Completed', 'cancelled' => 'Cancelled'] as $val => $label)
        <a href="{{ route('site-viewing.index', $val ? ['status' => $val] : []) }}"
            class="px-4 py-2 rounded-xl text-sm font-medium transition {{ request('status', '') === $val ? 'bg-indigo-600 text-white' : 'bg-white text-gray-600 hover:bg-gray-50 shadow-sm' }}">
            {{ $label }}
        </a>
    @endforeach
</div>

{{-- Table --}}
<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-100">
            <tr>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Client</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Property</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Date & Time</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Confirmed By</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @forelse($schedules as $schedule)
            <tr class="hover:bg-gray-50 transition">
                <td class="px-5 py-4">
                    <p class="font-medium text-gray-800">{{ $schedule->client->full_name ?? '—' }}</p>
                    <p class="text-xs text-gray-400">{{ $schedule->client->email ?? '' }}</p>
                </td>
                <td class="px-5 py-4">
                    <p class="font-medium text-gray-800">{{ $schedule->reservation->property->title ?? '—' }}</p>
                    <p class="text-xs text-gray-400">{{ $schedule->reservation->property->location ?? '' }}</p>
                </td>
                <td class="px-5 py-4">
                    <p class="font-medium text-gray-800">{{ $schedule->preferred_date->format('M d, Y') }}</p>
                    <p class="text-xs text-gray-400">{{ \Carbon\Carbon::parse($schedule->preferred_time)->format('g:i A') }}</p>
                </td>
                <td class="px-5 py-4">
                    <span class="text-xs px-2.5 py-1 rounded-full font-medium
                        {{ $schedule->status === 'pending'   ? 'bg-yellow-100 text-yellow-700' : '' }}
                        {{ $schedule->status === 'confirmed' ? 'bg-green-100 text-green-700'   : '' }}
                        {{ $schedule->status === 'cancelled' ? 'bg-red-100 text-red-700'       : '' }}
                        {{ $schedule->status === 'completed' ? 'bg-blue-100 text-blue-700'     : '' }}">
                        {{ ucfirst($schedule->status) }}
                    </span>
                </td>
                <td class="px-5 py-4 text-xs text-gray-500">
                    {{ $schedule->confirmedBy->name ?? '—' }}
                    @if($schedule->confirmed_at)
                        <p class="text-gray-400">{{ $schedule->confirmed_at->format('M d, Y') }}</p>
                    @endif
                </td>
                <td class="px-5 py-4">
                    <div class="flex items-center gap-2">
                        @if($schedule->status === 'pending')
                            <form method="POST" action="{{ route('site-viewing.confirm', $schedule) }}">
                                @csrf @method('PATCH')
                                <button class="text-xs bg-green-600 text-white px-3 py-1.5 rounded-lg hover:bg-green-700 transition">
                                    <i class="fas fa-check mr-1"></i>Confirm
                                </button>
                            </form>
                            <form method="POST" action="{{ route('site-viewing.cancel', $schedule) }}">
                                @csrf @method('PATCH')
                                <button class="text-xs bg-red-100 text-red-600 px-3 py-1.5 rounded-lg hover:bg-red-200 transition">
                                    <i class="fas fa-times mr-1"></i>Cancel
                                </button>
                            </form>
                        @elseif($schedule->status === 'confirmed')
                            <form method="POST" action="{{ route('site-viewing.complete', $schedule) }}">
                                @csrf @method('PATCH')
                                <button class="text-xs bg-blue-600 text-white px-3 py-1.5 rounded-lg hover:bg-blue-700 transition">
                                    <i class="fas fa-flag-checkered mr-1"></i>Complete
                                </button>
                            </form>
                            <form method="POST" action="{{ route('site-viewing.cancel', $schedule) }}">
                                @csrf @method('PATCH')
                                <button class="text-xs bg-red-100 text-red-600 px-3 py-1.5 rounded-lg hover:bg-red-200 transition">
                                    <i class="fas fa-times mr-1"></i>Cancel
                                </button>
                            </form>
                        @else
                            <span class="text-xs text-gray-400">No actions</span>
                        @endif
                    </div>
                    @if($schedule->notes)
                        <p class="text-xs text-gray-400 mt-1 italic">"{{ Str::limit($schedule->notes, 50) }}"</p>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="px-5 py-16 text-center text-gray-400">
                    <i class="fas fa-calendar text-4xl mb-3 block text-gray-200"></i>
                    No site viewing schedules found.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($schedules->hasPages())
    <div class="mt-4">{{ $schedules->links() }}</div>
@endif

@endsection
