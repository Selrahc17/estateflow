@extends('layouts.app')

@section('title', 'Archived Audit Logs — EstateFlow')
@section('page-title', 'Archived Audit Logs')
@section('page-subtitle', 'Restore or permanently delete archived log entries')

@section('content')

@if(session('success'))
    <div class="mb-4 bg-green-50 border border-green-200 text-green-700 text-sm px-4 py-3 rounded-xl flex items-center gap-2">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
    </div>
@endif

<div class="flex items-center justify-between mb-6">
    <p class="text-sm text-gray-500">{{ $logs->total() }} archived log entries</p>
    <a href="{{ route('audit-logs.index') }}" class="text-sm text-indigo-600 hover:underline">
        <i class="fas fa-arrow-left mr-1"></i> Back to Audit Logs
    </a>
</div>

<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-100">
            <tr>
                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Action</th>
                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">User</th>
                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Description</th>
                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Archived At</th>
                <th class="text-right px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @forelse($logs as $log)
            <tr class="hover:bg-gray-50 transition">
                <td class="px-6 py-4">
                    <span class="text-xs px-2.5 py-1 rounded-full font-medium
                        {{ $log->action === 'login'   ? 'bg-green-100 text-green-700'   : '' }}
                        {{ $log->action === 'logout'  ? 'bg-gray-100 text-gray-600'     : '' }}
                        {{ $log->action === 'create'  ? 'bg-blue-100 text-blue-700'     : '' }}
                        {{ $log->action === 'update'  ? 'bg-yellow-100 text-yellow-700' : '' }}
                        {{ $log->action === 'delete'  ? 'bg-red-100 text-red-700'       : '' }}
                        {{ !in_array($log->action, ['login','logout','create','update','delete']) ? 'bg-indigo-100 text-indigo-700' : '' }}">
                        {{ ucfirst($log->action) }}
                    </span>
                </td>
                <td class="px-6 py-4">
                    <p class="font-medium text-gray-800">{{ $log->user->name ?? 'System' }}</p>
                    <p class="text-xs text-gray-400">{{ $log->user->email ?? '' }}</p>
                </td>
                <td class="px-6 py-4 text-gray-600 max-w-xs">
                    <p class="truncate">{{ $log->description }}</p>
                </td>
                <td class="px-6 py-4 text-xs text-gray-500">
                    <p>{{ $log->deleted_at->format('M d, Y') }}</p>
                    <p>{{ $log->deleted_at->format('h:i A') }}</p>
                </td>
                <td class="px-6 py-4">
                    <div class="flex items-center justify-end gap-2">
                        <form method="POST" action="{{ route('audit-logs.restore', $log->id) }}">
                            @csrf @method('PATCH')
                            <button class="text-xs px-3 py-1.5 bg-green-100 text-green-700 rounded-lg hover:bg-green-200 transition">
                                <i class="fas fa-undo mr-1"></i>Restore
                            </button>
                        </form>
                        <form method="POST" action="{{ route('audit-logs.force-delete', $log->id) }}"
                            onsubmit="return confirm('Permanently delete this log? This cannot be undone.')">
                            @csrf @method('DELETE')
                            <button class="text-xs px-3 py-1.5 bg-red-100 text-red-600 rounded-lg hover:bg-red-200 transition">
                                <i class="fas fa-trash mr-1"></i>Delete Forever
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="px-6 py-16 text-center text-gray-400">
                    <i class="fas fa-archive text-4xl mb-3 block text-gray-200"></i>
                    No archived audit logs.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    @if($logs->hasPages())
        <div class="px-6 py-4 border-t border-gray-100">{{ $logs->links() }}</div>
    @endif
</div>

@endsection
