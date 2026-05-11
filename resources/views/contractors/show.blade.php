@extends('layouts.app')

@section('title', '{{ $contractor->company_name }} - EstateFlow')
@section('page-title', '{{ $contractor->company_name }}')
@section('page-subtitle', 'Staff Details')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Left: Info --}}
    <div class="lg:col-span-1 space-y-6">
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center gap-4 mb-6">
                <div class="w-14 h-14 bg-green-100 rounded-full flex items-center justify-center text-green-600 font-bold text-xl">
                    {{ strtoupper(substr($contractor->company_name, 0, 1)) }}
                </div>
                <div>
                    <h2 class="font-bold text-gray-800 text-lg">{{ $contractor->company_name }}</h2>
                    <span class="text-xs px-2 py-0.5 rounded-full font-medium bg-blue-50 text-blue-700">
                        {{ ucfirst(str_replace('_', ' ', $contractor->type)) }}
                    </span>
                </div>
            </div>

            <div class="space-y-3 text-sm">
                <div class="flex items-center gap-3">
                    <i class="fas fa-user text-gray-400 w-4"></i>
                    <span class="text-gray-700">{{ $contractor->contact_person }}</span>
                </div>
                @if($contractor->email)
                <div class="flex items-center gap-3">
                    <i class="fas fa-envelope text-gray-400 w-4"></i>
                    <span class="text-gray-700">{{ $contractor->email }}</span>
                </div>
                @endif
                <div class="flex items-center gap-3">
                    <i class="fas fa-phone text-gray-400 w-4"></i>
                    <div>
                        <p class="text-gray-700">{{ $contractor->phone }}</p>
                        @if($contractor->phone_alt)
                            <p class="text-xs text-gray-400">{{ $contractor->phone_alt }}</p>
                        @endif
                    </div>
                </div>
                @if($contractor->license_number)
                <div class="flex items-center gap-3">
                    <i class="fas fa-id-card text-gray-400 w-4"></i>
                    <span class="text-gray-700">{{ $contractor->license_number }}</span>
                </div>
                @endif
                @if($contractor->tax_id)
                <div class="flex items-center gap-3">
                    <i class="fas fa-file-invoice text-gray-400 w-4"></i>
                    <span class="text-gray-700">TIN: {{ $contractor->tax_id }}</span>
                </div>
                @endif
                @if($contractor->address)
                <div class="flex items-start gap-3">
                    <i class="fas fa-map-marker-alt text-gray-400 mt-0.5 w-4"></i>
                    <span class="text-gray-700">{{ $contractor->address }}</span>
                </div>
                @endif
                @if($contractor->specialization)
                <div class="flex items-start gap-3">
                    <i class="fas fa-tools text-gray-400 mt-0.5 w-4"></i>
                    <span class="text-gray-700">{{ $contractor->specialization }}</span>
                </div>
                @endif
            </div>

            <div class="mt-4">
                <span class="text-xs px-2.5 py-1 rounded-full font-medium
                    {{ $contractor->status === 'active'      ? 'bg-green-100 text-green-700' : '' }}
                    {{ $contractor->status === 'inactive'    ? 'bg-gray-100 text-gray-600'   : '' }}
                    {{ $contractor->status === 'blacklisted' ? 'bg-red-100 text-red-700'     : '' }}">
                    {{ ucfirst($contractor->status) }}
                </span>
            </div>

            @if(auth()->user()->isAdmin())
            <div class="mt-6 flex gap-2">
                <a href="{{ route('contractors.edit', $contractor) }}" class="flex-1 text-center bg-indigo-600 text-white py-2 rounded-lg text-sm hover:bg-indigo-700 transition">
                    <i class="fas fa-edit mr-1"></i> Edit
                </a>
                <form method="POST" action="{{ route('contractors.destroy', $contractor) }}"
                    onsubmit="return confirm('Delete {{ $contractor->company_name }}?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="px-4 py-2 bg-red-50 text-red-600 rounded-lg text-sm hover:bg-red-100 transition">
                        <i class="fas fa-trash"></i>
                    </button>
                </form>
            </div>
            @endif
        </div>

        @if($contractor->notes)
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="font-semibold text-gray-800 mb-2 text-sm">Notes</h3>
            <p class="text-sm text-gray-600">{{ $contractor->notes }}</p>
        </div>
        @endif
    </div>

    {{-- Right: Projects --}}
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-gray-800">Projects ({{ $contractor->projects->count() }})</h3>
            </div>
            @forelse($contractor->projects as $project)
            <div class="flex items-center justify-between py-3 border-b border-gray-50 last:border-0">
                <div>
                    <p class="text-sm font-medium text-gray-800">{{ $project->name }}</p>
                    <p class="text-xs text-gray-400">
                        {{ $project->start_date ? $project->start_date->format('M d, Y') : 'No start date' }}
                        @if($project->estimated_completion_date)
                            → {{ $project->estimated_completion_date->format('M d, Y') }}
                        @endif
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <div class="text-right">
                        <p class="text-xs text-gray-500">{{ $project->completion_percentage }}% done</p>
                        <div class="w-24 bg-gray-100 rounded-full h-1.5 mt-1">
                            <div class="bg-indigo-500 h-1.5 rounded-full" style="width: {{ $project->completion_percentage }}%"></div>
                        </div>
                    </div>
                    <span class="text-xs px-2 py-1 rounded-full font-medium
                        {{ $project->status === 'in_progress' ? 'bg-blue-100 text-blue-700'   : '' }}
                        {{ $project->status === 'planning'    ? 'bg-gray-100 text-gray-600'    : '' }}
                        {{ $project->status === 'completed'   ? 'bg-green-100 text-green-700'  : '' }}
                        {{ $project->status === 'on_hold'     ? 'bg-yellow-100 text-yellow-700' : '' }}
                        {{ $project->status === 'cancelled'   ? 'bg-red-100 text-red-700'      : '' }}">
                        {{ ucfirst(str_replace('_', ' ', $project->status)) }}
                    </span>
                </div>
            </div>
            @empty
            <p class="text-sm text-gray-400">No projects assigned yet.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
