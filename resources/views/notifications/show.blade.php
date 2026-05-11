@extends('layouts.app')

@section('title', 'Notification - EstateFlow')
@section('page-title', 'Notification')
@section('page-subtitle', 'Notification Details')

@section('content')
<div class="max-w-2xl">
    <div class="bg-white rounded-xl shadow-sm p-6">

        {{-- Header --}}
        <div class="flex items-start justify-between mb-6 pb-6 border-b border-gray-100">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-full flex items-center justify-center
                    {{ $notification->priority === 'urgent' ? 'bg-red-100'    : '' }}
                    {{ $notification->priority === 'high'   ? 'bg-orange-100' : '' }}
                    {{ $notification->priority === 'normal' ? 'bg-blue-100'   : '' }}
                    {{ $notification->priority === 'low'    ? 'bg-gray-100'   : '' }}">
                    <i class="fas fa-bell text-xl
                        {{ $notification->priority === 'urgent' ? 'text-red-600'    : '' }}
                        {{ $notification->priority === 'high'   ? 'text-orange-600' : '' }}
                        {{ $notification->priority === 'normal' ? 'text-blue-600'   : '' }}
                        {{ $notification->priority === 'low'    ? 'text-gray-500'   : '' }}"></i>
                </div>
                <div>
                    <h2 class="font-bold text-gray-800 text-lg">{{ ucfirst(str_replace('_', ' ', $notification->type)) }}</h2>
                    <p class="text-xs text-gray-400">{{ $notification->created_at->format('M d, Y h:i A') }}</p>
                </div>
            </div>
            <div class="flex gap-2">
                <span class="text-xs px-2.5 py-1 rounded-full font-medium
                    {{ $notification->priority === 'urgent' ? 'bg-red-100 text-red-700'       : '' }}
                    {{ $notification->priority === 'high'   ? 'bg-orange-100 text-orange-700' : '' }}
                    {{ $notification->priority === 'normal' ? 'bg-blue-100 text-blue-700'     : '' }}
                    {{ $notification->priority === 'low'    ? 'bg-gray-100 text-gray-600'     : '' }}">
                    {{ ucfirst($notification->priority) }}
                </span>
                <span class="text-xs px-2.5 py-1 rounded-full font-medium {{ $notification->is_read ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                    {{ $notification->is_read ? 'Read' : 'Unread' }}
                </span>
            </div>
        </div>

        {{-- Message --}}
        <div class="mb-6">
            <p class="text-sm text-gray-700 leading-relaxed">{{ $notification->data['message'] ?? '' }}</p>
        </div>

        @if($notification->read_at)
        <p class="text-xs text-gray-400 mb-6">Read at: {{ $notification->read_at->format('M d, Y h:i A') }}</p>
        @endif

        <div class="flex gap-3">
            @if(!$notification->is_read)
                <form method="POST" action="{{ route('notifications.read', $notification) }}">
                    @csrf @method('PATCH')
                    <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded-lg text-sm hover:bg-indigo-700 transition font-medium">
                        <i class="fas fa-check mr-1"></i> Mark as Read
                    </button>
                </form>
            @endif
            @if(auth()->user()->isAdmin())
                <form method="POST" action="{{ route('notifications.destroy', $notification) }}"
                    onsubmit="return confirm('Delete this notification?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="bg-red-50 text-red-600 px-6 py-2 rounded-lg text-sm hover:bg-red-100 transition">
                        <i class="fas fa-trash mr-1"></i> Delete
                    </button>
                </form>
            @endif
            <a href="{{ route('notifications.index') }}" class="bg-gray-100 text-gray-600 px-6 py-2 rounded-lg text-sm hover:bg-gray-200 transition">Back</a>
        </div>
    </div>
</div>
@endsection
