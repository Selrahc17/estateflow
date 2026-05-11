@extends('layouts.app')

@section('title', 'Audit Logs - EstateFlow')
@section('page-title', 'Audit Logs')
@section('page-subtitle', 'System activity trail')

@section('content')

{{-- Stats --}}
<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
    <div class="bg-white rounded-xl shadow-sm p-6 flex items-center gap-4">
        <div class="w-12 h-12 bg-indigo-100 rounded-xl flex items-center justify-center">
            <i class="fas fa-history text-indigo-600 text-xl"></i>
        </div>
        <div>
            <p class="text-sm text-gray-500">Total Log Entries</p>
            <p class="text-2xl font-bold text-gray-800">{{ $totalLogs }}</p>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-6 flex items-center gap-4">
        <div class="w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center">
            <i class="fas fa-trash text-red-600 text-xl"></i>
        </div>
        <div class="flex-1">
            <p class="text-sm text-gray-500">Clear All Logs</p>
            <p class="text-xs text-gray-400">This action cannot be undone</p>
        </div>
        <form method="POST" action="{{ route('audit-logs.clear') }}"
            onsubmit="return confirm('Clear ALL audit logs? This cannot be undone.')">
            @csrf
            <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-red-700 transition">
                Clear All
            </button>
        </form>
    </div>
</div>

{{-- Filters --}}
<div class="bg-white rounded-xl shadow-sm p-5 mb-6">
    <form method="GET" action="{{ route('audit-logs.index') }}" class="flex flex-wrap gap-3 items-end">
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Search</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Description or action..."
                class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 w-56">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Action</label>
            <select name="action" class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">All Actions</option>
                @foreach($actions as $action)
                    <option value="{{ $action }}" {{ request('action') === $action ? 'selected' : '' }}>
                        {{ ucfirst($action) }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">User</label>
            <select name="user_id" class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">All Users</option>
                @foreach($users as $user)
                    <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                        {{ $user->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-indigo-700 transition">
            <i class="fas fa-search mr-1"></i> Filter
        </button>
        <a href="{{ route('audit-logs.index') }}" class="bg-gray-100 text-gray-600 px-4 py-2 rounded-lg text-sm hover:bg-gray-200 transition">Reset</a>
    </form>
</div>

{{-- Table --}}
<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-100">
            <tr>
                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Action</th>
                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">User</th>
                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Description</th>
                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Model</th>
                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">IP Address</th>
                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Time</th>
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
                    @if($log->auditable_type)
                        {{ class_basename($log->auditable_type) }}
                        @if($log->auditable_id) #{{ $log->auditable_id }} @endif
                    @else
                        —
                    @endif
                </td>
                <td class="px-6 py-4 text-xs text-gray-500">{{ $log->ip_address ?? '—' }}</td>
                <td class="px-6 py-4 text-xs text-gray-500">
                    <p>{{ $log->created_at->format('M d, Y') }}</p>
                    <p>{{ $log->created_at->format('h:i A') }}</p>
                </td>
                <td class="px-6 py-4">
                    <div class="flex items-center justify-end gap-2">
                        <a href="{{ route('audit-logs.show', $log) }}" class="text-xs px-3 py-1.5 bg-indigo-50 text-indigo-600 rounded-lg hover:bg-indigo-100 transition">View</a>
                        <form method="POST" action="{{ route('audit-logs.destroy', $log) }}"
                            onsubmit="return confirm('Delete this log entry?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-xs px-3 py-1.5 bg-gray-50 text-gray-500 rounded-lg hover:bg-red-50 hover:text-red-600 transition">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="px-6 py-16 text-center text-gray-400">
                    <i class="fas fa-history text-4xl mb-3 block text-gray-200"></i>
                    No audit logs found.
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
