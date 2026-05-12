@extends('layouts.app')

@section('title', 'Staff Dashboard - EstateFlow')
@section('page-title', 'Staff Dashboard')
@section('page-subtitle', 'Welcome back, ' . auth()->user()->name)

@section('content')

@if(!$contractorRecord)
<div class="mb-6 bg-yellow-50 border border-yellow-200 rounded-xl p-4 flex items-start gap-3">
    <i class="fas fa-exclamation-triangle text-yellow-500 mt-0.5"></i>
    <div>
        <p class="text-sm font-medium text-yellow-800">Your account is not linked to a staff profile yet.</p>
        <p class="text-xs text-yellow-600 mt-1">Please contact an administrator to complete your setup.</p>
    </div>
</div>
@endif

{{-- Stats --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-xl shadow-sm p-5 flex items-center gap-4">
        <div class="w-11 h-11 bg-blue-100 rounded-xl flex items-center justify-center">
            <i class="fas fa-project-diagram text-blue-600"></i>
        </div>
        <div>
            <p class="text-xs text-gray-500">My Projects</p>
            <p class="text-2xl font-bold text-gray-800">{{ $myProjects }}</p>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-5 flex items-center gap-4">
        <div class="w-11 h-11 bg-green-100 rounded-xl flex items-center justify-center">
            <i class="fas fa-spinner text-green-600"></i>
        </div>
        <div>
            <p class="text-xs text-gray-500">Active</p>
            <p class="text-2xl font-bold text-gray-800">{{ $activeProjects }}</p>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-5 flex items-center gap-4">
        <div class="w-11 h-11 bg-indigo-100 rounded-xl flex items-center justify-center">
            <i class="fas fa-flag text-indigo-600"></i>
        </div>
        <div>
            <p class="text-xs text-gray-500">Milestones Done</p>
            <p class="text-2xl font-bold text-gray-800">{{ $completedMilestones }}</p>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-5 flex items-center gap-4">
        <div class="w-11 h-11 bg-yellow-100 rounded-xl flex items-center justify-center">
            <i class="fas fa-clipboard-list text-yellow-600"></i>
        </div>
        <div>
            <p class="text-xs text-gray-500">Progress Logs</p>
            <p class="text-2xl font-bold text-gray-800">{{ $totalLogs }}</p>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Projects Timeline --}}
    <div class="lg:col-span-2 space-y-4">
        <h3 class="font-semibold text-gray-800">Construction Projects</h3>

        @forelse($recentProjects as $project)
        @php
            $start      = $project->start_date;
            $target     = $project->estimated_completion_date;
            $today      = now();
            $totalDays  = $start && $target ? $start->diffInDays($target) : 0;
            $elapsed    = $start ? min($start->diffInDays($today), $totalDays) : 0;
            $timePercent = $totalDays > 0 ? round(($elapsed / $totalDays) * 100) : 0;
            $isOnTrack  = $project->completion_percentage >= ($timePercent - 10);
            $isOverdue  = $target && $target->isPast() && $project->status !== 'completed';
        @endphp
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="h-1.5 w-full bg-gray-100">
                <div class="h-1.5 {{ $project->completion_percentage >= 100 ? 'bg-green-500' : ($isOnTrack ? 'bg-indigo-500' : 'bg-red-400') }}"
                    style="width: {{ $project->completion_percentage }}%"></div>
            </div>
            <div class="p-5">
                <div class="flex items-start justify-between mb-3">
                    <div>
                        <p class="font-semibold text-gray-800">{{ $project->name }}</p>
                        <p class="text-xs text-gray-400 mt-0.5">
                            <i class="fas fa-building mr-1"></i>{{ $project->property->title ?? 'No property linked' }}
                        </p>
                    </div>
                    <div class="flex items-center gap-2">
                        @if($isOverdue)
                            <span class="text-xs bg-red-100 text-red-700 px-2.5 py-1 rounded-full font-medium">
                                <i class="fas fa-exclamation-circle mr-1"></i>Overdue
                            </span>
                        @elseif($project->status === 'completed')
                            <span class="text-xs bg-green-100 text-green-700 px-2.5 py-1 rounded-full font-medium">
                                <i class="fas fa-check-circle mr-1"></i>Completed
                            </span>
                        @elseif($isOnTrack)
                            <span class="text-xs bg-indigo-100 text-indigo-700 px-2.5 py-1 rounded-full font-medium">
                                <i class="fas fa-check mr-1"></i>On Track
                            </span>
                        @else
                            <span class="text-xs bg-yellow-100 text-yellow-700 px-2.5 py-1 rounded-full font-medium">
                                <i class="fas fa-exclamation mr-1"></i>Behind
                            </span>
                        @endif
                        <span class="text-sm font-bold text-indigo-600">{{ $project->completion_percentage }}%</span>
                    </div>
                </div>

                {{-- Timeline Bar --}}
                <div class="flex items-center gap-2 text-xs text-gray-400 mb-3">
                    <span>{{ $start?->format('M d, Y') ?? '—' }}</span>
                    <div class="flex-1 h-1.5 bg-gray-100 rounded-full relative">
                        <div class="h-1.5 bg-indigo-200 rounded-full" style="width: {{ $timePercent }}%"></div>
                        <div class="h-1.5 {{ $isOnTrack ? 'bg-indigo-600' : 'bg-red-400' }} rounded-full absolute top-0 left-0" style="width: {{ $project->completion_percentage }}%"></div>
                    </div>
                    <span class="{{ $isOverdue ? 'text-red-500 font-medium' : '' }}">{{ $target?->format('M d, Y') ?? '—' }}</span>
                </div>

                <div class="flex items-center justify-between">
                    @if($target)
                        @php $daysLeft = (int) $today->diffInDays($target, false); @endphp
                        <p class="text-xs {{ $daysLeft < 0 ? 'text-red-500 font-medium' : 'text-gray-400' }}">
                            {{ $daysLeft < 0 ? abs($daysLeft) . ' days overdue' : $daysLeft . ' days remaining' }}
                        </p>
                    @else
                        <p class="text-xs text-gray-400">No target date set</p>
                    @endif
                    <a href="{{ route('contractor.project.detail', $project) }}"
                        class="text-xs bg-indigo-600 text-white px-3 py-1.5 rounded-lg hover:bg-indigo-700 transition font-medium">
                        View Details
                    </a>
                </div>
            </div>
        </div>
        @empty
        <div class="bg-white rounded-xl shadow-sm p-12 text-center text-gray-400">
            <i class="fas fa-project-diagram text-3xl mb-2 block text-gray-200"></i>
            <p class="text-sm">No projects assigned yet.</p>
        </div>
        @endforelse
    </div>

    {{-- Right Sidebar --}}
    <div class="space-y-4">

        {{-- Construction Company --}}
        @if($contractorRecord)
        <div class="bg-white rounded-xl shadow-sm p-5">
            <h3 class="font-semibold text-gray-800 mb-4 text-sm flex items-center gap-2">
                <i class="fas fa-hard-hat text-yellow-500"></i> Construction Company
            </h3>
            <div class="space-y-2.5 text-sm">
                <div class="flex items-start gap-2">
                    <i class="fas fa-building text-gray-300 mt-0.5 w-4 flex-shrink-0"></i>
                    <span class="font-semibold text-gray-800">{{ $contractorRecord->company_name }}</span>
                </div>
                @if($contractorRecord->contact_person)
                <div class="flex items-start gap-2">
                    <i class="fas fa-user text-gray-300 mt-0.5 w-4 flex-shrink-0"></i>
                    <span class="text-gray-600">{{ $contractorRecord->contact_person }}</span>
                </div>
                @endif
                @if($contractorRecord->phone)
                <div class="flex items-start gap-2">
                    <i class="fas fa-phone text-gray-300 mt-0.5 w-4 flex-shrink-0"></i>
                    <span class="text-gray-600">{{ $contractorRecord->phone }}</span>
                </div>
                @endif
                @if($contractorRecord->email)
                <div class="flex items-start gap-2">
                    <i class="fas fa-envelope text-gray-300 mt-0.5 w-4 flex-shrink-0"></i>
                    <span class="text-gray-600">{{ $contractorRecord->email }}</span>
                </div>
                @endif
                @if($contractorRecord->specialization)
                <div class="flex items-start gap-2">
                    <i class="fas fa-tools text-gray-300 mt-0.5 w-4 flex-shrink-0"></i>
                    <span class="text-gray-600">{{ $contractorRecord->specialization }}</span>
                </div>
                @endif
                <span class="inline-block text-xs px-2 py-0.5 rounded-full mt-1
                    {{ $contractorRecord->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                    {{ ucfirst($contractorRecord->status) }}
                </span>
            </div>
        </div>
        @endif

        {{-- Quick Actions --}}
        <div class="bg-white rounded-xl shadow-sm p-5">
            <h3 class="font-semibold text-gray-800 mb-3 text-sm">Quick Actions</h3>
            <div class="space-y-2">
                <a href="{{ route('contractor.projects') }}"
                    class="flex items-center gap-3 p-3 bg-indigo-50 rounded-xl hover:bg-indigo-100 transition">
                    <i class="fas fa-project-diagram text-indigo-600 w-4 text-center"></i>
                    <span class="text-sm font-medium text-indigo-700">All Projects</span>
                </a>
                <a href="{{ route('progress-logs.create') }}"
                    class="flex items-center gap-3 p-3 bg-green-50 rounded-xl hover:bg-green-100 transition">
                    <i class="fas fa-camera text-green-600 w-4 text-center"></i>
                    <span class="text-sm font-medium text-green-700">Upload Progress</span>
                </a>
                <a href="{{ route('messages.index') }}"
                    class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl hover:bg-gray-100 transition">
                    <i class="fas fa-comments text-gray-600 w-4 text-center"></i>
                    <span class="text-sm font-medium text-gray-700">Messages</span>
                </a>
            </div>
        </div>
    </div>
</div>

@endsection
