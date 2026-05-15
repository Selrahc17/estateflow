@extends('layouts.app')

@section('title', 'Lead Management — EstateFlow')
@section('page-title', 'Lead Management')
@section('page-subtitle', 'Track and convert potential buyers')

@section('content')

@if(session('success'))
    <div class="mb-4 bg-green-50 border border-green-200 text-green-700 text-sm px-4 py-3 rounded-xl flex items-center gap-2">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
    </div>
@endif

{{-- Stats --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-xl shadow-sm p-4 text-center">
        <p class="text-2xl font-bold text-blue-600">{{ $newCount }}</p>
        <p class="text-xs text-gray-500 mt-1">New Leads</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-4 text-center">
        <p class="text-2xl font-bold text-yellow-600">{{ $qualifiedCount }}</p>
        <p class="text-xs text-gray-500 mt-1">Qualified</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-4 text-center">
        <p class="text-2xl font-bold text-green-600">{{ $convertedCount }}</p>
        <p class="text-xs text-gray-500 mt-1">Converted</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-4 text-center border-2 border-indigo-100">
        <a href="{{ route('leads.create') }}" class="block">
            <p class="text-2xl font-bold text-indigo-600"><i class="fas fa-plus"></i></p>
            <p class="text-xs text-gray-500 mt-1">Add Lead</p>
        </a>
    </div>
</div>

{{-- Pipeline Visual --}}
<div class="bg-white rounded-xl shadow-sm p-4 mb-6">
    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Sales Pipeline</p>
    <div class="flex items-center gap-1">
        @php
            $statuses = ['new' => ['label' => 'New', 'color' => 'bg-blue-500'],
                         'contacted' => ['label' => 'Contacted', 'color' => 'bg-purple-500'],
                         'qualified' => ['label' => 'Qualified', 'color' => 'bg-yellow-500'],
                         'converted' => ['label' => 'Converted', 'color' => 'bg-green-500'],
                         'lost'      => ['label' => 'Lost', 'color' => 'bg-red-400']];
            $total = \App\Models\Lead::count() ?: 1;
        @endphp
        @foreach($statuses as $key => $s)
            @php $count = \App\Models\Lead::where('status', $key)->count(); @endphp
            <div class="flex-1 text-center">
                <div class="h-2 {{ $s['color'] }} rounded-full mb-1" style="opacity: {{ max(0.2, $count / $total) }}"></div>
                <p class="text-xs font-semibold text-gray-700">{{ $count }}</p>
                <p class="text-xs text-gray-400">{{ $s['label'] }}</p>
            </div>
            @if(!$loop->last)
                <i class="fas fa-chevron-right text-gray-300 text-xs mb-4"></i>
            @endif
        @endforeach
    </div>
</div>

{{-- Filters --}}
<div class="bg-white rounded-xl shadow-sm p-4 mb-6">
    <form method="GET" action="{{ route('leads.index') }}" class="flex flex-wrap gap-3 items-end">
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Search</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Name, email, phone..."
                class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 w-44">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Status</label>
            <select name="status" class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">All</option>
                @foreach(['new' => 'New', 'contacted' => 'Contacted', 'qualified' => 'Qualified', 'converted' => 'Converted', 'lost' => 'Lost'] as $val => $label)
                    <option value="{{ $val }}" {{ request('status') === $val ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Source</label>
            <select name="source" class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">All Sources</option>
                @foreach(['website_inquiry' => 'Website Inquiry', 'referral' => 'Referral', 'walk_in' => 'Walk-in', 'social_media' => 'Social Media', 'phone_call' => 'Phone Call', 'other' => 'Other'] as $val => $label)
                    <option value="{{ $val }}" {{ request('source') === $val ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-indigo-700 transition">
            <i class="fas fa-search mr-1"></i> Filter
        </button>
        <a href="{{ route('leads.index') }}" class="bg-gray-100 text-gray-600 px-4 py-2 rounded-lg text-sm hover:bg-gray-200 transition">Reset</a>
    </form>
</div>

{{-- Table --}}
<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-100">
            <tr>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Lead</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Source</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Interested In</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Budget</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Agent</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @forelse($leads as $lead)
            <tr class="hover:bg-gray-50 transition">
                <td class="px-5 py-4">
                    <p class="font-medium text-gray-800">{{ $lead->name }}</p>
                    <p class="text-xs text-gray-400">{{ $lead->email ?? '—' }}</p>
                    <p class="text-xs text-gray-400">{{ $lead->phone ?? '' }}</p>
                    @if($lead->last_contacted_at)
                        <p class="text-xs text-indigo-400 mt-0.5"><i class="fas fa-clock mr-1"></i>Last contact: {{ $lead->last_contacted_at->diffForHumans() }}</p>
                    @endif
                </td>
                <td class="px-5 py-4">
                    @php
                        $sourceColors = ['website_inquiry' => 'bg-blue-100 text-blue-700', 'referral' => 'bg-purple-100 text-purple-700', 'walk_in' => 'bg-green-100 text-green-700', 'social_media' => 'bg-pink-100 text-pink-700', 'phone_call' => 'bg-yellow-100 text-yellow-700', 'other' => 'bg-gray-100 text-gray-600'];
                        $sourceLabels = ['website_inquiry' => 'Website', 'referral' => 'Referral', 'walk_in' => 'Walk-in', 'social_media' => 'Social', 'phone_call' => 'Phone', 'other' => 'Other'];
                    @endphp
                    <span class="text-xs px-2.5 py-1 rounded-full font-medium {{ $sourceColors[$lead->source] ?? 'bg-gray-100 text-gray-600' }}">
                        {{ $sourceLabels[$lead->source] ?? ucfirst($lead->source) }}
                    </span>
                </td>
                <td class="px-5 py-4 text-xs text-gray-600">
                    {{ $lead->interestedProperty->title ?? ($lead->preferred_location ?? '—') }}
                </td>
                <td class="px-5 py-4 text-xs text-gray-600">
                    @if($lead->budget_min || $lead->budget_max)
                        ₱{{ number_format($lead->budget_min ?? 0, 0) }} – ₱{{ number_format($lead->budget_max ?? 0, 0) }}
                    @else
                        <span class="text-gray-400">—</span>
                    @endif
                </td>
                <td class="px-5 py-4 text-xs text-gray-500">
                    {{ $lead->assignedAgent->full_name ?? '—' }}
                </td>
                <td class="px-5 py-4">
                    @php
                        $statusColors = ['new' => 'bg-blue-100 text-blue-700', 'contacted' => 'bg-purple-100 text-purple-700', 'qualified' => 'bg-yellow-100 text-yellow-700', 'converted' => 'bg-green-100 text-green-700', 'lost' => 'bg-red-100 text-red-600'];
                    @endphp
                    <span class="text-xs px-2.5 py-1 rounded-full font-medium {{ $statusColors[$lead->status] ?? '' }}">
                        {{ ucfirst($lead->status) }}
                    </span>
                </td>
                <td class="px-5 py-4">
                    <div class="flex items-center gap-2">
                        <a href="{{ route('leads.edit', $lead) }}"
                            class="text-xs bg-indigo-50 text-indigo-600 px-3 py-1.5 rounded-lg hover:bg-indigo-100 transition">
                            <i class="fas fa-edit"></i>
                        </a>
                        {{-- Quick status update --}}
                        @if($lead->status !== 'converted' && $lead->status !== 'lost')
                        <div class="relative group">
                            <button class="text-xs bg-gray-50 text-gray-600 px-3 py-1.5 rounded-lg hover:bg-gray-100 transition">
                                <i class="fas fa-ellipsis-h"></i>
                            </button>
                            <div class="hidden group-hover:block absolute right-0 mt-1 w-36 bg-white rounded-xl shadow-lg border border-gray-100 z-10 py-1">
                                @foreach(['contacted' => 'Mark Contacted', 'qualified' => 'Mark Qualified', 'converted' => 'Mark Converted', 'lost' => 'Mark Lost'] as $s => $label)
                                    @if($s !== $lead->status)
                                    <form method="POST" action="{{ route('leads.status', $lead) }}">
                                        @csrf @method('PATCH')
                                        <input type="hidden" name="status" value="{{ $s }}">
                                        <button class="w-full text-left px-3 py-2 text-xs text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition">
                                            {{ $label }}
                                        </button>
                                    </form>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                        @endif
                        <form method="POST" action="{{ route('leads.destroy', $lead) }}"
                            onsubmit="return confirm('Delete this lead?')">
                            @csrf @method('DELETE')
                            <button class="text-xs text-gray-300 hover:text-red-500 transition">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="px-5 py-16 text-center text-gray-400">
                    <i class="fas fa-user-plus text-4xl mb-3 block text-gray-200"></i>
                    No leads yet.
                    <div class="mt-4">
                        <a href="{{ route('leads.create') }}" class="text-sm bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition">
                            Add First Lead
                        </a>
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    @if($leads->hasPages())
        <div class="px-5 py-4 border-t border-gray-100">{{ $leads->links() }}</div>
    @endif
</div>

@endsection
