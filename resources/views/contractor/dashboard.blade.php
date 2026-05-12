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
<div class="grid grid-cols-2 md:grid-cols-4 gap-6 mb-8">
    <div class="bg-white rounded-xl shadow-sm p-6 flex items-center gap-4">
        <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
            <i class="fas fa-project-diagram text-blue-600 text-xl"></i>
        </div>
        <div>
            <p class="text-sm text-gray-500">My Projects</p>
            <p class="text-2xl font-bold text-gray-800">{{ $myProjects }}</p>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-6 flex items-center gap-4">
        <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
            <i class="fas fa-spinner text-green-600 text-xl"></i>
        </div>
        <div>
            <p class="text-sm text-gray-500">Active</p>
            <p class="text-2xl font-bold text-gray-800">{{ $activeProjects }}</p>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-6 flex items-center gap-4">
        <div class="w-12 h-12 bg-yellow-100 rounded-xl flex items-center justify-center">
            <i class="fas fa-tasks text-yellow-600 text-xl"></i>
        </div>
        <div>
            <p class="text-sm text-gray-500">My Tasks</p>
            <p class="text-2xl font-bold text-gray-800">{{ $myTasks }}</p>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-6 flex items-center gap-4">
        <div class="w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center">
            <i class="fas fa-clock text-red-600 text-xl"></i>
        </div>
        <div>
            <p class="text-sm text-gray-500">Pending Tasks</p>
            <p class="text-2xl font-bold text-gray-800">{{ $pendingTasks }}</p>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Recent Projects --}}
    <div class="lg:col-span-2 bg-white rounded-xl shadow-sm p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-semibold text-gray-800">My Projects</h3>
            <a href="{{ route('contractor.projects') }}" class="text-sm text-indigo-600 hover:underline">View all</a>
        </div>
        @forelse($recentProjects as $project)
        <div class="py-3 border-b border-gray-50 last:border-0">
            <div class="flex items-center justify-between mb-1">
                <p class="text-sm font-medium text-gray-800">{{ $project->name }}</p>
                <span class="text-xs px-2.5 py-1 rounded-full font-medium
                    {{ $project->status === 'in_progress' ? 'bg-blue-100 text-blue-700'    : '' }}
                    {{ $project->status === 'planning'    ? 'bg-gray-100 text-gray-600'    : '' }}
                    {{ $project->status === 'completed'   ? 'bg-green-100 text-green-700'  : '' }}
                    {{ $project->status === 'on_hold'     ? 'bg-yellow-100 text-yellow-700': '' }}
                    {{ $project->status === 'cancelled'   ? 'bg-red-100 text-red-700'      : '' }}">
                    {{ ucfirst(str_replace('_', ' ', $project->status)) }}
                </span>
            </div>
            <div class="flex items-center gap-2 mt-1">
                <div class="flex-1 bg-gray-100 rounded-full h-1.5">
                    <div class="h-1.5 rounded-full bg-indigo-500" style="width: {{ $project->completion_percentage }}%"></div>
                </div>
                <span class="text-xs text-gray-500">{{ $project->completion_percentage }}%</span>
            </div>
        </div>
        @empty
        <div class="text-center py-8 text-gray-400">
            <i class="fas fa-project-diagram text-3xl mb-2 block text-gray-200"></i>
            <p class="text-sm">No projects assigned yet.</p>
        </div>
        @endforelse
    </div>

    {{-- Urgent Tasks + Quick Actions --}}
    <div class="space-y-4">

        {{-- Urgent Tasks --}}
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-gray-800">Urgent Tasks</h3>
                <a href="{{ route('contractor.tasks') }}" class="text-sm text-indigo-600 hover:underline">View all</a>
            </div>
            @forelse($urgentTasks as $task)
            <div class="flex items-center justify-between py-2 border-b border-gray-50 last:border-0">
                <div>
                    <p class="text-sm font-medium text-gray-800">{{ $task->title }}</p>
                    <p class="text-xs text-gray-400">{{ $task->project->name ?? '—' }}</p>
                </div>
                <span class="text-xs px-2 py-0.5 rounded-full font-medium flex-shrink-0 ml-2
                    {{ $task->priority === 'urgent' ? 'bg-red-100 text-red-700'       : '' }}
                    {{ $task->priority === 'high'   ? 'bg-orange-100 text-orange-700' : '' }}">
                    {{ ucfirst($task->priority) }}
                </span>
            </div>
            @empty
            <p class="text-sm text-gray-400 text-center py-4">No urgent tasks.</p>
            @endforelse
        </div>

        {{-- Quick Actions --}}
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="font-semibold text-gray-800 mb-4">Quick Actions</h3>
            <div class="space-y-2">
                <a href="{{ route('contractor.projects') }}"
                    class="flex items-center gap-3 p-3 bg-blue-50 rounded-xl hover:bg-blue-100 transition">
                    <i class="fas fa-project-diagram text-blue-600 w-5 text-center"></i>
                    <span class="text-sm font-medium text-blue-700">My Projects</span>
                </a>
                <a href="{{ route('contractor.tasks') }}"
                    class="flex items-center gap-3 p-3 bg-yellow-50 rounded-xl hover:bg-yellow-100 transition">
                    <i class="fas fa-tasks text-yellow-600 w-5 text-center"></i>
                    <span class="text-sm font-medium text-yellow-700">My Tasks</span>
                    @if($pendingTasks > 0)
                        <span class="ml-auto bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">{{ $pendingTasks }}</span>
                    @endif
                </a>
                <a href="{{ route('progress-logs.create') }}"
                    class="flex items-center gap-3 p-3 bg-green-50 rounded-xl hover:bg-green-100 transition">
                    <i class="fas fa-clipboard-list text-green-600 w-5 text-center"></i>
                    <span class="text-sm font-medium text-green-700">Log Progress</span>
                </a>
                <a href="{{ route('messages.index') }}"
                    class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl hover:bg-gray-100 transition">
                    <i class="fas fa-comments text-gray-600 w-5 text-center"></i>
                    <span class="text-sm font-medium text-gray-700">Messages</span>
                </a>
            </div>
        </div>

        {{-- Staff Profile --}}
        @if($contractorRecord)
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="font-semibold text-gray-800 mb-3 text-sm">My Company</h3>
            <div class="space-y-2 text-sm">
                <div class="flex items-center gap-2 text-gray-600">
                    <i class="fas fa-building text-gray-400 w-4"></i>
                    {{ $contractorRecord->company_name }}
                </div>
                <div class="flex items-center gap-2 text-gray-600">
                    <i class="fas fa-tools text-gray-400 w-4"></i>
                    {{ ucfirst(str_replace('_', ' ', $contractorRecord->type)) }}
                </div>
                @if($contractorRecord->specialization)
                <div class="flex items-center gap-2 text-gray-600">
                    <i class="fas fa-star text-gray-400 w-4"></i>
                    {{ $contractorRecord->specialization }}
                </div>
                @endif
                <span class="inline-block text-xs px-2 py-0.5 rounded-full mt-1
                    {{ $contractorRecord->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                    {{ ucfirst($contractorRecord->status) }}
                </span>
            </div>
        </div>
        @endif
    </div>

</div>
@endsection
