@extends('layouts.app')

@section('title', 'Resources - EstateFlow')
@section('page-title', 'Resources')
@section('page-subtitle', 'Manage project materials, equipment, and labor')

@section('content')

{{-- Stats --}}
<div class="grid grid-cols-2 md:grid-cols-3 gap-6 mb-6">
    <div class="bg-white rounded-xl shadow-sm p-6 flex items-center gap-4">
        <div class="w-12 h-12 bg-indigo-100 rounded-xl flex items-center justify-center">
            <i class="fas fa-boxes text-indigo-600 text-xl"></i>
        </div>
        <div>
            <p class="text-sm text-gray-500">Total Resources</p>
            <p class="text-2xl font-bold text-gray-800">{{ $totalCount }}</p>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-6 flex items-center gap-4">
        <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
            <i class="fas fa-peso-sign text-green-600 text-xl"></i>
        </div>
        <div>
            <p class="text-sm text-gray-500">Total Cost</p>
            <p class="text-lg font-bold text-gray-800">₱{{ number_format($totalCost, 0) }}</p>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-6 flex items-center gap-4">
        <div class="w-12 h-12 bg-yellow-100 rounded-xl flex items-center justify-center">
            <i class="fas fa-clock text-yellow-600 text-xl"></i>
        </div>
        <div>
            <p class="text-sm text-gray-500">Pending</p>
            <p class="text-2xl font-bold text-gray-800">{{ $pendingCount }}</p>
        </div>
    </div>
</div>

{{-- Filters --}}
<div class="bg-white rounded-xl shadow-sm p-5 mb-6">
    <form method="GET" action="{{ route('resources.index') }}" class="flex flex-wrap gap-3 items-end">
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Search</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Name or description..."
                class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 w-48">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Project</label>
            <select name="project_id" class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">All Projects</option>
                @foreach($projects as $p)
                    <option value="{{ $p->id }}" {{ request('project_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Type</label>
            <select name="type" class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">All Types</option>
                @foreach(['material','equipment','labor'] as $t)
                    <option value="{{ $t }}" {{ request('type') === $t ? 'selected' : '' }}>{{ ucfirst($t) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Status</label>
            <select name="status" class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">All Status</option>
                @foreach(['pending','ordered','delivered','used','returned'] as $s)
                    <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-indigo-700 transition">
            <i class="fas fa-search mr-1"></i> Filter
        </button>
        <a href="{{ route('resources.index') }}" class="bg-gray-100 text-gray-600 px-4 py-2 rounded-lg text-sm hover:bg-gray-200 transition">Reset</a>
    </form>
</div>

{{-- Header --}}
<div class="flex items-center justify-between mb-4">
    <p class="text-sm text-gray-500">{{ $resources->total() }} resources found</p>
    @if(auth()->user()->isAdmin() || auth()->user()->isContractor())
        <a href="{{ route('resources.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-indigo-700 transition">
            <i class="fas fa-plus mr-1"></i> Add Resource
        </a>
    @endif
</div>

{{-- Table --}}
<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-100">
            <tr>
                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Resource</th>
                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Project</th>
                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Type</th>
                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Qty / Unit</th>
                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Unit Price</th>
                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Total</th>
                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                <th class="text-right px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @forelse($resources as $resource)
            <tr class="hover:bg-gray-50 transition">
                <td class="px-6 py-4">
                    <p class="font-medium text-gray-800">{{ $resource->name }}</p>
                    @if($resource->description)
                        <p class="text-xs text-gray-400">{{ Str::limit($resource->description, 45) }}</p>
                    @endif
                </td>
                <td class="px-6 py-4">
                    <a href="{{ route('projects.show', $resource->project) }}" class="text-indigo-600 hover:underline text-xs">
                        {{ $resource->project->name ?? '—' }}
                    </a>
                </td>
                <td class="px-6 py-4">
                    <span class="text-xs px-2.5 py-1 rounded-full font-medium
                        {{ $resource->type === 'material'  ? 'bg-blue-100 text-blue-700'   : '' }}
                        {{ $resource->type === 'equipment' ? 'bg-purple-100 text-purple-700' : '' }}
                        {{ $resource->type === 'labor'     ? 'bg-orange-100 text-orange-700' : '' }}">
                        {{ ucfirst($resource->type) }}
                    </span>
                </td>
                <td class="px-6 py-4 text-gray-700">{{ $resource->quantity }} {{ $resource->unit }}</td>
                <td class="px-6 py-4 text-gray-700">₱{{ number_format($resource->unit_price, 2) }}</td>
                <td class="px-6 py-4 font-medium text-gray-800">₱{{ number_format($resource->total_cost, 2) }}</td>
                <td class="px-6 py-4">
                    <span class="text-xs px-2.5 py-1 rounded-full font-medium
                        {{ $resource->status === 'pending'   ? 'bg-yellow-100 text-yellow-700' : '' }}
                        {{ $resource->status === 'ordered'   ? 'bg-blue-100 text-blue-700'     : '' }}
                        {{ $resource->status === 'delivered' ? 'bg-green-100 text-green-700'   : '' }}
                        {{ $resource->status === 'used'      ? 'bg-gray-100 text-gray-600'     : '' }}
                        {{ $resource->status === 'returned'  ? 'bg-red-100 text-red-700'       : '' }}">
                        {{ ucfirst($resource->status) }}
                    </span>
                </td>
                <td class="px-6 py-4">
                    <div class="flex items-center justify-end gap-2">
                        <a href="{{ route('resources.show', $resource) }}" class="text-xs px-3 py-1.5 bg-indigo-50 text-indigo-600 rounded-lg hover:bg-indigo-100 transition">View</a>
                        @if(auth()->user()->isAdmin() || auth()->user()->isContractor())
                            <a href="{{ route('resources.edit', $resource) }}" class="text-xs px-3 py-1.5 bg-gray-50 text-gray-600 rounded-lg hover:bg-gray-100 transition">Edit</a>
                            <form method="POST" action="{{ route('resources.destroy', $resource) }}"
                                onsubmit="return confirm('Delete this resource?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-xs px-3 py-1.5 bg-gray-50 text-gray-500 rounded-lg hover:bg-red-50 hover:text-red-600 transition">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="px-6 py-16 text-center text-gray-400">
                    <i class="fas fa-boxes text-4xl mb-3 block text-gray-200"></i>
                    No resources found.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    @if($resources->hasPages())
        <div class="px-6 py-4 border-t border-gray-100">{{ $resources->links() }}</div>
    @endif
</div>

@endsection
