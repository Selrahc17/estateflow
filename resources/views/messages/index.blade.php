@extends('layouts.app')

@section('title', 'Messages - EstateFlow')
@section('page-title', 'Messages')
@section('page-subtitle', 'Your conversations')

@section('content')
<div class="max-w-3xl">

    <div class="flex items-center gap-3 mb-6">
        <form method="GET" action="{{ route('messages.index') }}" class="flex-1 flex items-center gap-2">
            <div class="relative flex-1">
                <i class="fas fa-search absolute left-3 top-2.5 text-gray-400 text-sm"></i>
                <input type="text" name="search" value="{{ $search ?? '' }}"
                    placeholder="Search conversations or messages..."
                    class="w-full pl-9 pr-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <button type="submit" class="bg-indigo-600 text-white px-4 py-2.5 rounded-xl text-sm hover:bg-indigo-700 transition">Search</button>
            @if($search)
                <a href="{{ route('messages.index') }}" class="px-4 py-2.5 bg-gray-100 text-gray-600 rounded-xl text-sm hover:bg-gray-200 transition">Clear</a>
            @endif
        </form>
        @if($unreadCount > 0 && !$search)
            <span class="text-sm bg-red-100 text-red-700 px-3 py-1.5 rounded-full font-medium flex-shrink-0">{{ $unreadCount }} unread</span>
        @endif
        <a href="{{ route('messages.create') }}" class="bg-indigo-600 text-white px-4 py-2.5 rounded-xl text-sm hover:bg-indigo-700 transition flex-shrink-0">
            <i class="fas fa-pen mr-1"></i> New
        </a>
    </div>

    @if($search)
        <p class="text-sm text-gray-500 mb-4">
            Showing results for <span class="font-medium text-gray-800">"{{ $search }}"</span>
            — {{ $conversations->count() }} conversation(s) found
        </p>
    @endif

    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        @forelse($conversations as $otherUserId => $lastMsg)
            @php
                $other    = $lastMsg->from_user_id === auth()->id() ? $lastMsg->toUser : $lastMsg->fromUser;
                $isUnread = $lastMsg->to_user_id === auth()->id() && !$lastMsg->read_at;
            @endphp
            <a href="{{ route('messages.show', $other) }}"
                class="flex items-center gap-4 px-6 py-4 hover:bg-gray-50 transition border-b border-gray-50 last:border-0 {{ $isUnread ? 'bg-indigo-50' : '' }}">
                <div class="relative flex-shrink-0">
                    <div class="w-11 h-11 rounded-full flex items-center justify-center font-semibold text-white
                        {{ $other->role === 'agent'      ? 'bg-blue-500'   : '' }}
                        {{ $other->role === 'client'     ? 'bg-purple-500' : '' }}
                        {{ $other->role === 'admin'      ? 'bg-indigo-500' : '' }}
                        {{ $other->role === 'contractor' ? 'bg-green-500'  : '' }}">
                        {{ strtoupper(substr($other->name, 0, 1)) }}
                    </div>
                    @if($other->isOnline())
                        <span class="absolute bottom-0 right-0 w-3 h-3 bg-green-400 border-2 border-white rounded-full"></span>
                    @endif
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <p class="font-medium text-gray-800 {{ $isUnread ? 'font-semibold' : '' }}">{{ $other->name }}</p>
                            <span class="text-xs px-1.5 py-0.5 rounded-full capitalize
                                {{ $other->role === 'agent'      ? 'bg-blue-100 text-blue-600'    : '' }}
                                {{ $other->role === 'client'     ? 'bg-purple-100 text-purple-600': '' }}
                                {{ $other->role === 'admin'      ? 'bg-indigo-100 text-indigo-600': '' }}
                                {{ $other->role === 'contractor' ? 'bg-green-100 text-green-600'  : '' }}">
                                {{ $other->role }}
                            </span>
                        </div>
                        <p class="text-xs text-gray-400 flex-shrink-0 ml-2">{{ $lastMsg->created_at->diffForHumans() }}</p>
                    </div>
                    <p class="text-sm text-gray-500 truncate mt-0.5">
                        @if($lastMsg->from_user_id === auth()->id())<span class="text-gray-400">You: </span>@endif
                        @if($search)
                            {!! str_ireplace($search, '<mark class="bg-yellow-100 text-yellow-800 rounded px-0.5">'.e($search).'</mark>', e($lastMsg->message)) !!}
                        @else
                            {{ Str::limit($lastMsg->message, 80) }}
                        @endif
                    </p>
                    @if($lastMsg->reservation)
                        <p class="text-xs text-indigo-500 mt-0.5">
                            <i class="fas fa-home mr-1"></i>{{ $lastMsg->reservation->property->title ?? '' }}
                        </p>
                    @endif
                </div>
                @if($isUnread)
                    <div class="w-2.5 h-2.5 bg-indigo-500 rounded-full flex-shrink-0"></div>
                @endif
            </a>
        @empty
            <div class="px-6 py-16 text-center text-gray-400">
                <i class="fas fa-{{ $search ? 'search' : 'comments' }} text-4xl mb-3 block text-gray-200"></i>
                @if($search)
                    <p>No conversations found for "{{ $search }}".</p>
                    <a href="{{ route('messages.index') }}" class="mt-4 inline-block text-indigo-600 hover:underline text-sm">Clear search</a>
                @else
                    <p>No messages yet.</p>
                    <a href="{{ route('messages.create') }}" class="mt-4 inline-block bg-indigo-600 text-white px-6 py-2 rounded-lg text-sm hover:bg-indigo-700 transition">
                        Start a Conversation
                    </a>
                @endif
            </div>
        @endforelse
    </div>
</div>
@endsection
