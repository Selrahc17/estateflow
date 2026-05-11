@extends('layouts.app')

@section('title', 'New Message - EstateFlow')
@section('page-title', 'New Message')
@section('page-subtitle', 'Start a conversation')

@section('content')
<div class="max-w-2xl">
    <div class="bg-white rounded-xl shadow-sm p-6">

        @if($contacts->isEmpty())
            <div class="text-center py-8 text-gray-400">
                <i class="fas fa-users text-4xl mb-3 block text-gray-200"></i>
                <p>No contacts available to message.</p>
            </div>
        @else
            <p class="text-sm text-gray-500 mb-4">Select a person to start a conversation with:</p>
            <div class="space-y-2">
                @foreach($contacts as $contact)
                    <a href="{{ route('messages.show', $contact) }}"
                        class="flex items-center gap-4 p-4 rounded-xl border border-gray-100 hover:border-indigo-300 hover:bg-indigo-50 transition {{ $selectedUserId == $contact->id ? 'border-indigo-400 bg-indigo-50' : '' }}">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center font-semibold text-white
                            {{ $contact->role === 'agent'  ? 'bg-blue-500'   : '' }}
                            {{ $contact->role === 'client' ? 'bg-purple-500' : '' }}
                            {{ $contact->role === 'admin'  ? 'bg-indigo-500' : '' }}">
                            {{ strtoupper(substr($contact->name, 0, 1)) }}
                        </div>
                        <div>
                            <p class="font-medium text-gray-800">{{ $contact->name }}</p>
                            <p class="text-xs text-gray-400 capitalize">{{ $contact->role }} · {{ $contact->email }}</p>
                        </div>
                        <i class="fas fa-chevron-right text-gray-300 ml-auto"></i>
                    </a>
                @endforeach
            </div>
        @endif

        <div class="mt-6">
            <a href="{{ route('messages.index') }}" class="text-sm text-gray-500 hover:text-gray-700 transition">
                <i class="fas fa-arrow-left mr-1"></i> Back to Messages
            </a>
        </div>
    </div>
</div>
@endsection
