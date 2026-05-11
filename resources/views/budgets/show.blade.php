@extends('layouts.app')

@section('title', 'Budget Entry - EstateFlow')
@section('page-title', 'Budget Entry')
@section('page-subtitle', '{{ ucfirst($budget->category) }}')

@section('content')
<div class="max-w-2xl">
    <div class="bg-white rounded-xl shadow-sm p-6">

        {{-- Header --}}
        <div class="flex items-start justify-between mb-6 pb-6 border-b border-gray-100">
            <div>
                <h2 class="text-lg font-bold text-gray-800">{{ ucfirst(str_replace('_', ' ', $budget->category)) }}</h2>
                <a href="{{ route('projects.show', $budget->project) }}" class="text-sm text-indigo-600 hover:underline mt-1 block">
                    <i class="fas fa-project-diagram mr-1"></i>{{ $budget->project->name ?? '—' }}
                </a>
            </div>
            <span class="text-xs px-2.5 py-1 rounded-full font-medium
                {{ $budget->status === 'planned'     ? 'bg-gray-100 text-gray-600'     : '' }}
                {{ $budget->status === 'approved'    ? 'bg-blue-100 text-blue-700'     : '' }}
                {{ $budget->status === 'in_progress' ? 'bg-yellow-100 text-yellow-700' : '' }}
                {{ $budget->status === 'completed'   ? 'bg-green-100 text-green-700'   : '' }}
                {{ $budget->status === 'over_budget' ? 'bg-red-100 text-red-700'       : '' }}">
                {{ ucfirst(str_replace('_', ' ', $budget->status)) }}
            </span>
        </div>

        @if($budget->description)
        <p class="text-sm text-gray-600 mb-6">{{ $budget->description }}</p>
        @endif

        {{-- Amounts --}}
        <div class="grid grid-cols-3 gap-4 mb-6">
            <div class="bg-blue-50 rounded-xl p-4 text-center">
                <p class="text-xs text-blue-500 mb-1">Estimated</p>
                <p class="text-lg font-bold text-blue-700">₱{{ number_format($budget->estimated_amount, 2) }}</p>
            </div>
            <div class="bg-green-50 rounded-xl p-4 text-center">
                <p class="text-xs text-green-500 mb-1">Actual</p>
                <p class="text-lg font-bold text-green-700">₱{{ number_format($budget->actual_amount, 2) }}</p>
            </div>
            @php $variance = $budget->estimated_amount - $budget->actual_amount; @endphp
            <div class="{{ $variance < 0 ? 'bg-red-50' : 'bg-gray-50' }} rounded-xl p-4 text-center">
                <p class="text-xs {{ $variance < 0 ? 'text-red-500' : 'text-gray-500' }} mb-1">Variance</p>
                <p class="text-lg font-bold {{ $variance < 0 ? 'text-red-700' : 'text-gray-700' }}">
                    {{ $variance >= 0 ? '+' : '' }}₱{{ number_format($variance, 2) }}
                </p>
            </div>
        </div>

        <div class="space-y-3 text-sm">
            <div class="flex justify-between py-2 border-b border-gray-50">
                <span class="text-gray-500">Budget Date</span>
                <span class="font-medium text-gray-800">{{ $budget->budget_date->format('M d, Y') }}</span>
            </div>
            <div class="flex justify-between py-2 border-b border-gray-50">
                <span class="text-gray-500">Currency</span>
                <span class="font-medium text-gray-800">{{ $budget->currency }}</span>
            </div>
            @if($budget->notes)
            <div class="py-2">
                <p class="text-gray-500 mb-1">Notes</p>
                <p class="text-gray-800">{{ $budget->notes }}</p>
            </div>
            @endif
        </div>

        @if(auth()->user()->isAdmin())
        <div class="mt-6 flex gap-3">
            <a href="{{ route('budgets.edit', $budget) }}" class="bg-indigo-600 text-white px-6 py-2 rounded-lg text-sm hover:bg-indigo-700 transition font-medium">
                <i class="fas fa-edit mr-1"></i> Edit
            </a>
            <form method="POST" action="{{ route('budgets.destroy', $budget) }}"
                onsubmit="return confirm('Delete this budget entry?')">
                @csrf @method('DELETE')
                <button type="submit" class="bg-red-50 text-red-600 px-6 py-2 rounded-lg text-sm hover:bg-red-100 transition">
                    <i class="fas fa-trash mr-1"></i> Delete
                </button>
            </form>
            <a href="{{ route('budgets.index') }}" class="bg-gray-100 text-gray-600 px-6 py-2 rounded-lg text-sm hover:bg-gray-200 transition">Back</a>
        </div>
        @endif
    </div>
</div>
@endsection
