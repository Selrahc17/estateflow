@extends('layouts.app')

@section('title', 'Follow-Up Schedules — EstateFlow')
@section('page-title', 'Follow-Up Schedules')
@section('page-subtitle', 'Manage client follow-ups')

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
        <p class="text-2xl font-bold text-red-600">{{ $todayCount }}</p>
        <p class="text-xs text-gray-500 mt-1">Due Today</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-4 text-center">
        <p class="text-2xl font-bold text-green-600">{{ \App\Models\FollowUpSchedule::where('status','done')->count() }}</p>
        <p class="text-xs text-gray-500 mt-1">Completed</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-4 text-center border-2 border-indigo-100">
        <a href="{{ route('follow-ups.create') }}" class="block">
            <p class="text-2xl font-bold text-indigo-600"><i class="fas fa-plus"></i></p>
            <p class="text-xs text-gray-500 mt-1">New Follow-Up</p>
        </a>
    </div>
</div>

{{-- Filters --}}
<div class="bg-white rounded-xl shadow-sm p-4 mb-6">
    <form method="GET" action="{{ route('follow-ups.index') }}" class="flex flex-wrap gap-3 items-end">
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Search Client</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Client name..."
                class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 w-44">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Status</label>
            <select name="status" class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">All</option>
                <option value="pending"   {{ request('status') === 'pending'   ? 'selected' : '' }}>Pending</option>
                <option value="done"      {{ request('status') === 'done'      ? 'selected' : '' }}>Done</option>
                <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Type</label>
            <select name="type" class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">All Types</option>
                @foreach(['call' => 'Call', 'email' => 'Email', 'site_visit' => 'Site Visit', 'meeting' => 'Meeting'] as $val => $label)
                    <option value="{{ $val }}" {{ request('type') === $val ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-indigo-700 transition">
            <i class="fas fa-search mr-1"></i> Filter
        </button>
        <a href="{{ route('follow-ups.index') }}" class="bg-gray-100 text-gray-600 px-4 py-2 rounded-lg text-sm hover:bg-gray-200 transition">Reset</a>
    </form>
</div>

{{-- Table --}}
<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-100">
            <tr>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Client</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Type</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Date & Time</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Property</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Agent</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @forelse($schedules as $schedule)
            @php $isOverdue = $schedule->status === 'pending' && $schedule->follow_up_date->isPast(); @endphp
            <tr class="hover:bg-gray-50 transition {{ $isOverdue ? 'bg-red-50' : '' }}">
                <td class="px-5 py-4">
                    <p class="font-medium text-gray-800">{{ $schedule->client->full_name ?? '—' }}</p>
                    <p class="text-xs text-gray-400">{{ $schedule->client->phone ?? '' }}</p>
                </td>
                <td class="px-5 py-4">
                    @php
                        $typeIcons = ['call' => 'fa-phone', 'email' => 'fa-envelope', 'site_visit' => 'fa-map-marker-alt', 'meeting' => 'fa-users'];
                        $typeColors = ['call' => 'bg-blue-100 text-blue-700', 'email' => 'bg-purple-100 text-purple-700', 'site_visit' => 'bg-green-100 text-green-700', 'meeting' => 'bg-yellow-100 text-yellow-700'];
                    @endphp
                    <span class="text-xs px-2.5 py-1 rounded-full font-medium {{ $typeColors[$schedule->type] ?? 'bg-gray-100 text-gray-600' }}">
                        <i class="fas {{ $typeIcons[$schedule->type] ?? 'fa-calendar' }} mr-1"></i>
                        {{ ucfirst(str_replace('_', ' ', $schedule->type)) }}
                    </span>
                </td>
                <td class="px-5 py-4">
                    <p class="font-medium {{ $isOverdue ? 'text-red-600' : 'text-gray-800' }}">
                        {{ $schedule->follow_up_date->format('M d, Y') }}
                        @if($isOverdue) <span class="text-xs text-red-500">(Overdue)</span> @endif
                        @if($schedule->follow_up_date->isToday()) <span class="text-xs text-orange-500">(Today)</span> @endif
                    </p>
                    @if($schedule->follow_up_time)
                        <p class="text-xs text-gray-400">{{ \Carbon\Carbon::parse($schedule->follow_up_time)->format('g:i A') }}</p>
                    @endif
                </td>
                <td class="px-5 py-4 text-xs text-gray-500">
                    {{ $schedule->reservation->property->title ?? '—' }}
                </td>
                <td class="px-5 py-4 text-xs text-gray-500">
                    {{ $schedule->agent->full_name ?? '—' }}
                </td>
                <td class="px-5 py-4">
                    <span class="text-xs px-2.5 py-1 rounded-full font-medium
                        {{ $schedule->status === 'pending'   ? 'bg-yellow-100 text-yellow-700' : '' }}
                        {{ $schedule->status === 'done'      ? 'bg-green-100 text-green-700'   : '' }}
                        {{ $schedule->status === 'cancelled' ? 'bg-gray-100 text-gray-500'     : '' }}">
                        {{ ucfirst($schedule->status) }}
                    </span>
                </td>
                <td class="px-5 py-4">
                    <div class="flex items-center gap-2">
                        @if($schedule->status === 'pending')
                            <form method="POST" action="{{ route('follow-ups.done', $schedule) }}">
                                @csrf @method('PATCH')
                                <button class="text-xs bg-green-600 text-white px-3 py-1.5 rounded-lg hover:bg-green-700 transition">
                                    <i class="fas fa-check mr-1"></i>Done
                                </button>
                            </form>
                            <form method="POST" action="{{ route('follow-ups.cancel', $schedule) }}">
                                @csrf @method('PATCH')
                                <button class="text-xs bg-gray-100 text-gray-500 px-3 py-1.5 rounded-lg hover:bg-red-50 hover:text-red-500 transition">
                                    <i class="fas fa-times"></i>
                                </button>
                            </form>
                        @endif
                        @if($schedule->notes)
                            <span title="{{ $schedule->notes }}" class="text-gray-300 hover:text-gray-500 cursor-help">
                                <i class="fas fa-comment-alt text-xs"></i>
                            </span>
                        @endif
                        <form method="POST" action="{{ route('follow-ups.destroy', $schedule) }}"
                            onsubmit="return confirm('Delete this follow-up?')">
                            @csrf @method('DELETE')
                            <button class="text-xs text-gray-300 hover:text-red-500 transition">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="px-5 py-16 text-center text-gray-400">
                    <i class="fas fa-calendar-check text-4xl mb-3 block text-gray-200"></i>
                    No follow-up schedules found.
                    <div class="mt-4">
                        <a href="{{ route('follow-ups.create') }}" class="text-sm bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition">
                            Schedule First Follow-Up
                        </a>
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    @if($schedules->hasPages())
        <div class="px-5 py-4 border-t border-gray-100">{{ $schedules->links() }}</div>
    @endif
</div>

@endsection
