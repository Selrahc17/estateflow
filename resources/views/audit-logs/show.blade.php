@extends('layouts.app')

@section('title', 'Audit Log Entry - EstateFlow')
@section('page-title', 'Audit Log Entry')
@section('page-subtitle', 'Full activity details')

@section('content')
<div class="max-w-2xl">
    <div class="bg-white rounded-xl shadow-sm p-6">

        {{-- Header --}}
        <div class="flex items-center justify-between mb-6 pb-6 border-b border-gray-100">
            <div>
                <span class="text-sm px-3 py-1.5 rounded-full font-medium
                    {{ $auditLog->action === 'login'   ? 'bg-green-100 text-green-700'   : '' }}
                    {{ $auditLog->action === 'logout'  ? 'bg-gray-100 text-gray-600'     : '' }}
                    {{ $auditLog->action === 'create'  ? 'bg-blue-100 text-blue-700'     : '' }}
                    {{ $auditLog->action === 'update'  ? 'bg-yellow-100 text-yellow-700' : '' }}
                    {{ $auditLog->action === 'delete'  ? 'bg-red-100 text-red-700'       : '' }}
                    {{ !in_array($auditLog->action, ['login','logout','create','update','delete']) ? 'bg-indigo-100 text-indigo-700' : '' }}">
                    {{ ucfirst($auditLog->action) }}
                </span>
            </div>
            <p class="text-sm text-gray-400">{{ $auditLog->created_at->format('M d, Y h:i:s A') }}</p>
        </div>

        <div class="space-y-4 text-sm">
            <div class="flex justify-between py-2 border-b border-gray-50">
                <span class="text-gray-500">User</span>
                <span class="font-medium text-gray-800">{{ $auditLog->user->name ?? 'System' }}</span>
            </div>
            @if($auditLog->description)
            <div class="flex justify-between py-2 border-b border-gray-50">
                <span class="text-gray-500">Description</span>
                <span class="font-medium text-gray-800 text-right max-w-xs">{{ $auditLog->description }}</span>
            </div>
            @endif
            @if($auditLog->auditable_type)
            <div class="flex justify-between py-2 border-b border-gray-50">
                <span class="text-gray-500">Model</span>
                <span class="font-medium text-gray-800">{{ class_basename($auditLog->auditable_type) }} #{{ $auditLog->auditable_id }}</span>
            </div>
            @endif
            @if($auditLog->ip_address)
            <div class="flex justify-between py-2 border-b border-gray-50">
                <span class="text-gray-500">IP Address</span>
                <span class="font-medium text-gray-800">{{ $auditLog->ip_address }}</span>
            </div>
            @endif
            @if($auditLog->user_agent)
            <div class="py-2 border-b border-gray-50">
                <p class="text-gray-500 mb-1">User Agent</p>
                <p class="text-gray-700 text-xs break-all">{{ $auditLog->user_agent }}</p>
            </div>
            @endif
        </div>

        {{-- Old Values --}}
        @if($auditLog->old_values && count($auditLog->old_values))
        <div class="mt-6">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">Previous Values</h3>
            <div class="bg-red-50 rounded-xl p-4 space-y-2">
                @foreach($auditLog->old_values as $key => $value)
                <div class="flex justify-between text-xs">
                    <span class="text-red-500 font-medium">{{ $key }}</span>
                    <span class="text-red-700">{{ is_array($value) ? json_encode($value) : $value }}</span>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- New Values --}}
        @if($auditLog->new_values && count($auditLog->new_values))
        <div class="mt-4">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">New Values</h3>
            <div class="bg-green-50 rounded-xl p-4 space-y-2">
                @foreach($auditLog->new_values as $key => $value)
                <div class="flex justify-between text-xs">
                    <span class="text-green-600 font-medium">{{ $key }}</span>
                    <span class="text-green-800">{{ is_array($value) ? json_encode($value) : $value }}</span>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <div class="mt-6 flex gap-3">
            <form method="POST" action="{{ route('audit-logs.destroy', $auditLog) }}"
                onsubmit="return confirm('Delete this log entry?')">
                @csrf @method('DELETE')
                <button type="submit" class="bg-red-50 text-red-600 px-6 py-2 rounded-lg text-sm hover:bg-red-100 transition">
                    <i class="fas fa-trash mr-1"></i> Delete
                </button>
            </form>
            <a href="{{ route('audit-logs.index') }}" class="bg-gray-100 text-gray-600 px-6 py-2 rounded-lg text-sm hover:bg-gray-200 transition">Back</a>
        </div>
    </div>
</div>
@endsection
