@extends('layouts.app')

@section('title', 'Notifications - EstateFlow')
@section('page-title', 'Notifications')
@section('page-subtitle', 'System notifications and alerts')

@section('content')

{{-- Stats --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
    <div class="bg-white rounded-xl shadow-sm p-6 flex items-center gap-4">
        <div class="w-12 h-12 bg-indigo-100 rounded-xl flex items-center justify-center">
            <i class="fas fa-bell text-indigo-600 text-xl"></i>
        </div>
        <div>
            <p class="text-sm text-gray-500">Total</p>
            <p class="text-2xl font-bold text-gray-800">{{ $totalCount }}</p>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-6 flex items-center gap-4">
        <div class="w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center">
            <i class="fas fa-bell text-red-600 text-xl"></i>
        </div>
        <div>
            <p class="text-sm text-gray-500">Unread</p>
            <p class="text-2xl font-bold text-gray-800">{{ $unreadCount }}</p>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-6 flex items-center gap-4">
        <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
            <i class="fas fa-check text-green-600 text-xl"></i>
        </div>
        <div>
            <p class="text-sm text-gray-500">Read</p>
            <p class="text-2xl font-bold text-gray-800">{{ $readCount }}</p>
        </div>
    </div>
</div>

{{-- Filters --}}
<div class="bg-white rounded-xl shadow-sm p-5 mb-6">
    <form method="GET" action="{{ route('notifications.index') }}" class="flex flex-wrap gap-3 items-end">
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Priority</label>
            <select name="priority" class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">All Priorities</option>
                @foreach(['low','normal','high','urgent'] as $p)
                    <option value="{{ $p }}" {{ request('priority') === $p ? 'selected' : '' }}>{{ ucfirst($p) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Status</label>
            <select name="status" class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">All</option>
                <option value="unread" {{ request('status') === 'unread' ? 'selected' : '' }}>Unread</option>
                <option value="read"   {{ request('status') === 'read'   ? 'selected' : '' }}>Read</option>
            </select>
        </div>
        <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-indigo-700 transition">
            <i class="fas fa-search mr-1"></i> Filter
        </button>
        <a href="{{ route('notifications.index') }}" class="bg-gray-100 text-gray-600 px-4 py-2 rounded-lg text-sm hover:bg-gray-200 transition">Reset</a>
    </form>
</div>

{{-- Header Row --}}
<div class="flex items-center justify-between mb-4">
    <p class="text-sm text-gray-500">{{ $notifications->total() }} notifications</p>
    <div class="flex gap-2">
        @if($unreadCount > 0)
            <form method="POST" action="{{ route('notifications.read-all') }}">
                @csrf @method('PATCH')
                <button type="submit" class="bg-gray-100 text-gray-600 px-4 py-2 rounded-lg text-sm hover:bg-gray-200 transition">
                    <i class="fas fa-check-double mr-1"></i> Mark All Read
                </button>
            </form>
        @endif
        @if(auth()->user()->isAdmin())
            <a href="{{ route('notifications.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-indigo-700 transition">
                <i class="fas fa-plus mr-1"></i> Send Notification
            </a>
        @endif
    </div>
</div>

{{-- Notifications List --}}
<div class="space-y-3">
    @forelse($notifications as $notification)
    <div class="bg-white rounded-xl shadow-sm p-5 flex items-start gap-4 {{ !$notification->is_read ? 'border-l-4 border-indigo-500' : '' }}">
        <div class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0
            {{ $notification->priority === 'urgent' ? 'bg-red-100'    : '' }}
            {{ $notification->priority === 'high'   ? 'bg-orange-100' : '' }}
            {{ $notification->priority === 'normal' ? 'bg-blue-100'   : '' }}
            {{ $notification->priority === 'low'    ? 'bg-gray-100'   : '' }}">
            <i class="fas fa-bell text-sm
                {{ $notification->priority === 'urgent' ? 'text-red-600'    : '' }}
                {{ $notification->priority === 'high'   ? 'text-orange-600' : '' }}
                {{ $notification->priority === 'normal' ? 'text-blue-600'   : '' }}
                {{ $notification->priority === 'low'    ? 'text-gray-500'   : '' }}"></i>
        </div>
        <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2 mb-1">
                <p class="text-sm font-semibold text-gray-800">{{ ucfirst(str_replace('_', ' ', $notification->type)) }}</p>
                <span class="text-xs px-2 py-0.5 rounded-full font-medium
                    {{ $notification->priority === 'urgent' ? 'bg-red-100 text-red-700'       : '' }}
                    {{ $notification->priority === 'high'   ? 'bg-orange-100 text-orange-700' : '' }}
                    {{ $notification->priority === 'normal' ? 'bg-blue-100 text-blue-700'     : '' }}
                    {{ $notification->priority === 'low'    ? 'bg-gray-100 text-gray-600'     : '' }}">
                    {{ ucfirst($notification->priority) }}
                </span>
                @if(!$notification->is_read)
                    <span class="w-2 h-2 bg-indigo-500 rounded-full"></span>
                @endif
            </div>
            <p class="text-sm text-gray-600">{{ $notification->data['message'] ?? '' }}</p>
            <p class="text-xs text-gray-400 mt-1">{{ $notification->created_at->diffForHumans() }}</p>
        </div>
        <div class="flex items-center gap-2 flex-shrink-0">
            <a href="{{ route('notifications.show', $notification) }}" class="text-xs px-3 py-1.5 bg-indigo-50 text-indigo-600 rounded-lg hover:bg-indigo-100 transition">View</a>
            @if(!$notification->is_read)
                <form method="POST" action="{{ route('notifications.read', $notification) }}">
                    @csrf @method('PATCH')
                    <button type="submit" class="text-xs px-3 py-1.5 bg-gray-50 text-gray-600 rounded-lg hover:bg-gray-100 transition">
                        <i class="fas fa-check"></i>
                    </button>
                </form>
            @endif
            @if(auth()->user()->isAdmin())
                <form method="POST" action="{{ route('notifications.destroy', $notification) }}"
                    onsubmit="return confirm('Delete this notification?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="text-xs px-3 py-1.5 bg-gray-50 text-gray-500 rounded-lg hover:bg-red-50 hover:text-red-600 transition">
                        <i class="fas fa-trash"></i>
                    </button>
                </form>
            @endif
        </div>
    </div>
    @empty
    <div class="bg-white rounded-xl shadow-sm p-16 text-center text-gray-400">
        <i class="fas fa-bell text-4xl mb-3 block text-gray-200"></i>
        No notifications found.
    </div>
    @endforelse
</div>

@if($notifications->hasPages())
    <div class="mt-6">{{ $notifications->links() }}</div>
@endif

@endsection
