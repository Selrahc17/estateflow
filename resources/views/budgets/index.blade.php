@extends('layouts.app')

@section('title', 'Budgets - EstateFlow')
@section('page-title', 'Budgets')
@section('page-subtitle', 'Track project budget entries')

@section('content')

{{-- Stats --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-6 mb-6">
    <div class="bg-white rounded-xl shadow-sm p-6 flex items-center gap-4">
        <div class="w-12 h-12 bg-indigo-100 rounded-xl flex items-center justify-center">
            <i class="fas fa-wallet text-indigo-600 text-xl"></i>
        </div>
        <div>
            <p class="text-sm text-gray-500">Total Entries</p>
            <p class="text-2xl font-bold text-gray-800">{{ $totalBudgets }}</p>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-6 flex items-center gap-4">
        <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
            <i class="fas fa-chart-bar text-blue-600 text-xl"></i>
        </div>
        <div>
            <p class="text-sm text-gray-500">Total Estimated</p>
            <p class="text-lg font-bold text-gray-800">₱{{ number_format($totalEstimated, 0) }}</p>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-6 flex items-center gap-4">
        <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
            <i class="fas fa-money-bill-wave text-green-600 text-xl"></i>
        </div>
        <div>
            <p class="text-sm text-gray-500">Total Actual</p>
            <p class="text-lg font-bold text-gray-800">₱{{ number_format($totalActual, 0) }}</p>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-6 flex items-center gap-4">
        <div class="w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center">
            <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
        </div>
        <div>
            <p class="text-sm text-gray-500">Over Budget</p>
            <p class="text-2xl font-bold text-gray-800">{{ $overBudgetCount }}</p>
        </div>
    </div>
</div>

{{-- Filters --}}
<div class="bg-white rounded-xl shadow-sm p-5 mb-6">
    <form method="GET" action="{{ route('budgets.index') }}" class="flex flex-wrap gap-3 items-end">
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Search</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Category or description..."
                class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 w-56">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Project</label>
            <select name="project_id" class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">All Projects</option>
                @foreach($projects as $project)
                    <option value="{{ $project->id }}" {{ request('project_id') == $project->id ? 'selected' : '' }}>
                        {{ $project->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Status</label>
            <select name="status" class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">All Status</option>
                @foreach(['planned','approved','in_progress','completed','over_budget'] as $s)
                    <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>
                        {{ ucfirst(str_replace('_', ' ', $s)) }}
                    </option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-indigo-700 transition">
            <i class="fas fa-search mr-1"></i> Filter
        </button>
        <a href="{{ route('budgets.index') }}" class="bg-gray-100 text-gray-600 px-4 py-2 rounded-lg text-sm hover:bg-gray-200 transition">Reset</a>
    </form>
</div>

{{-- Header Row --}}
<div class="flex items-center justify-between mb-4">
    <p class="text-sm text-gray-500">{{ $budgets->total() }} entries found</p>
    @if(auth()->user()->isAdmin())
        <a href="{{ route('budgets.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-indigo-700 transition">
            <i class="fas fa-plus mr-1"></i> Add Budget Entry
        </a>
    @endif
</div>

{{-- Table --}}
<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-100">
            <tr>
                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Category</th>
                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Project</th>
                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Estimated</th>
                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Actual</th>
                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Variance</th>
                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                <th class="text-right px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @forelse($budgets as $budget)
            @php $variance = $budget->estimated_amount - $budget->actual_amount; @endphp
            <tr class="hover:bg-gray-50 transition">
                <td class="px-6 py-4">
                    <p class="font-medium text-gray-800">{{ ucfirst($budget->category) }}</p>
                    @if($budget->description)
                        <p class="text-xs text-gray-400">{{ Str::limit($budget->description, 50) }}</p>
                    @endif
                </td>
                <td class="px-6 py-4">
                    <a href="{{ route('projects.show', $budget->project) }}" class="text-indigo-600 hover:underline text-xs">
                        {{ $budget->project->name ?? '—' }}
                    </a>
                </td>
                <td class="px-6 py-4 font-medium text-gray-800">₱{{ number_format($budget->estimated_amount, 2) }}</td>
                <td class="px-6 py-4 font-medium text-gray-800">₱{{ number_format($budget->actual_amount, 2) }}</td>
                <td class="px-6 py-4 font-medium {{ $variance < 0 ? 'text-red-600' : 'text-green-600' }}">
                    {{ $variance >= 0 ? '+' : '' }}₱{{ number_format($variance, 2) }}
                </td>
                <td class="px-6 py-4">
                    <span class="text-xs px-2.5 py-1 rounded-full font-medium
                        {{ $budget->status === 'planned'     ? 'bg-gray-100 text-gray-600'     : '' }}
                        {{ $budget->status === 'approved'    ? 'bg-blue-100 text-blue-700'     : '' }}
                        {{ $budget->status === 'in_progress' ? 'bg-yellow-100 text-yellow-700' : '' }}
                        {{ $budget->status === 'completed'   ? 'bg-green-100 text-green-700'   : '' }}
                        {{ $budget->status === 'over_budget' ? 'bg-red-100 text-red-700'       : '' }}">
                        {{ ucfirst(str_replace('_', ' ', $budget->status)) }}
                    </span>
                </td>
                <td class="px-6 py-4">
                    <div class="flex items-center justify-end gap-2">
                        <a href="{{ route('budgets.show', $budget) }}" class="text-xs px-3 py-1.5 bg-indigo-50 text-indigo-600 rounded-lg hover:bg-indigo-100 transition">View</a>
                        @if(auth()->user()->isAdmin())
                            <a href="{{ route('budgets.edit', $budget) }}" class="text-xs px-3 py-1.5 bg-gray-50 text-gray-600 rounded-lg hover:bg-gray-100 transition">Edit</a>
                            <form method="POST" action="{{ route('budgets.destroy', $budget) }}"
                                onsubmit="return confirm('Delete this budget entry?')">
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
                <td colspan="7" class="px-6 py-16 text-center text-gray-400">
                    <i class="fas fa-wallet text-4xl mb-3 block text-gray-200"></i>
                    No budget entries found.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    @if($budgets->hasPages())
        <div class="px-6 py-4 border-t border-gray-100">{{ $budgets->links() }}</div>
    @endif
</div>

@endsection
