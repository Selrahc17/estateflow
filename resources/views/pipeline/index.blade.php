@extends('layouts.app')

@section('title', 'Sales Pipeline — EstateFlow')
@section('page-title', 'Sales Pipeline')
@section('page-subtitle', 'Track leads and deals across all stages')

@section('content')

{{-- Stats Bar --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-xl shadow-sm p-4">
        <p class="text-xs text-gray-500 mb-1">Pipeline Value</p>
        <p class="text-xl font-bold text-indigo-600">₱{{ number_format($pipelineValue, 0) }}</p>
        <p class="text-xs text-gray-400 mt-0.5">Active deals</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-4">
        <p class="text-xs text-gray-500 mb-1">Closed Revenue</p>
        <p class="text-xl font-bold text-green-600">₱{{ number_format($closedRevenue, 0) }}</p>
        <p class="text-xs text-gray-400 mt-0.5">Completed sales</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-4">
        <p class="text-xs text-gray-500 mb-1">Total Leads</p>
        <p class="text-xl font-bold text-blue-600">{{ $totalLeads }}</p>
        <p class="text-xs text-gray-400 mt-0.5">{{ $convertedLeads }} converted</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-4">
        <p class="text-xs text-gray-500 mb-1">Conversion Rate</p>
        <p class="text-xl font-bold {{ $conversionRate >= 50 ? 'text-green-600' : ($conversionRate >= 25 ? 'text-yellow-600' : 'text-red-500') }}">
            {{ $conversionRate }}%
        </p>
        <p class="text-xs text-gray-400 mt-0.5">Leads → Clients</p>
    </div>
</div>

{{-- Kanban Board --}}
<div class="overflow-x-auto pb-4">
    <div class="flex gap-4 min-w-max">

        {{-- Stage 1: New --}}
        @include('pipeline.column', [
            'title'   => 'New Leads',
            'icon'    => 'fa-user-plus',
            'color'   => 'blue',
            'items'   => $newLeads,
            'type'    => 'lead',
            'status'  => 'new',
            'count'   => $newLeads->count(),
        ])

        {{-- Stage 2: Contacted --}}
        @include('pipeline.column', [
            'title'   => 'Contacted',
            'icon'    => 'fa-phone',
            'color'   => 'purple',
            'items'   => $contactedLeads,
            'type'    => 'lead',
            'status'  => 'contacted',
            'count'   => $contactedLeads->count(),
        ])

        {{-- Stage 3: Qualified --}}
        @include('pipeline.column', [
            'title'   => 'Qualified',
            'icon'    => 'fa-star',
            'color'   => 'yellow',
            'items'   => $qualifiedLeads,
            'type'    => 'lead',
            'status'  => 'qualified',
            'count'   => $qualifiedLeads->count(),
        ])

        {{-- Divider --}}
        <div class="flex flex-col items-center justify-center px-1">
            <div class="h-full w-px bg-gray-200"></div>
            <span class="text-xs text-gray-400 bg-gray-100 px-2 py-1 rounded-full my-2 whitespace-nowrap">Becomes Client</span>
            <div class="h-full w-px bg-gray-200"></div>
        </div>

        {{-- Stage 4: Reserved --}}
        @include('pipeline.column', [
            'title'   => 'Reserved',
            'icon'    => 'fa-calendar-check',
            'color'   => 'orange',
            'items'   => $reservedDeals,
            'type'    => 'reservation',
            'status'  => 'pending',
            'count'   => $reservedDeals->count(),
        ])

        {{-- Stage 5: Active Buyer --}}
        @include('pipeline.column', [
            'title'   => 'Active Buyer',
            'icon'    => 'fa-receipt',
            'color'   => 'indigo',
            'items'   => $activeDeals,
            'type'    => 'reservation',
            'status'  => 'confirmed',
            'count'   => $activeDeals->count(),
        ])

        {{-- Stage 6: Closed --}}
        @include('pipeline.column', [
            'title'   => 'Closed / Sold',
            'icon'    => 'fa-trophy',
            'color'   => 'green',
            'items'   => $closedDeals,
            'type'    => 'reservation',
            'status'  => 'completed',
            'count'   => $closedDeals->count(),
        ])

    </div>
</div>

@endsection
