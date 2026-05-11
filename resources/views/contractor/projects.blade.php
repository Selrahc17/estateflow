@extends('layouts.app')

@section('title', 'My Projects - EstateFlow')
@section('page-title', 'My Projects')
@section('page-subtitle', 'Projects assigned to your company')

@section('content')

@if(!$contractorRecord && !auth()->user()->isAdmin())
<div class="mb-6 bg-yellow-50 border border-yellow-200 rounded-xl p-4">
    <p class="text-sm text-yellow-800">Your account is not linked to a contractor profile. Contact an administrator.</p>
</div>
@endif

<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-100">
            <tr>
                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Project</th>
                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Client</th>
                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Timeline</th>
                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Progress</th>
                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                <th class="text-right px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @forelse($projects as $project)
            <tr class="hover:bg-gray-50 transition">
                <td class="px-6 py-4">
                    <p class="font-medium text-gray-800">{{ $project->name }}</p>
                    <p class="text-xs text-gray-400">{{ $project->property->title ?? 'No property linked' }}</p>
                </td>
                <td class="px-6 py-4 text-gray-700">{{ $project->client->full_name ?? '—' }}</td>
                <td class="px-6 py-4">
                    <p class="text-xs text-gray-600">{{ $project->start_date ? $project->start_date->format('M d, Y') : '—' }}</p>
                    <p class="text-xs text-gray-400">→ {{ $project->estimated_completion_date ? $project->estimated_completion_date->format('M d, Y') : '—' }}</p>
                </td>
                <td class="px-6 py-4">
                    <div class="flex items-center gap-2">
                        <div class="w-20 bg-gray-100 rounded-full h-2">
                            <div class="h-2 rounded-full {{ $project->completion_percentage >= 100 ? 'bg-green-500' : 'bg-indigo-500' }}"
                                style="width: {{ $project->completion_percentage }}%"></div>
                        </div>
                        <span class="text-xs text-gray-500">{{ $project->completion_percentage }}%</span>
                    </div>
                </td>
                <td class="px-6 py-4">
                    <span class="text-xs px-2.5 py-1 rounded-full font-medium
                        {{ $project->status === 'in_progress' ? 'bg-blue-100 text-blue-700'    : '' }}
                        {{ $project->status === 'planning'    ? 'bg-gray-100 text-gray-600'    : '' }}
                        {{ $project->status === 'completed'   ? 'bg-green-100 text-green-700'  : '' }}
                        {{ $project->status === 'on_hold'     ? 'bg-yellow-100 text-yellow-700': '' }}
                        {{ $project->status === 'cancelled'   ? 'bg-red-100 text-red-700'      : '' }}">
                        {{ ucfirst(str_replace('_', ' ', $project->status)) }}
                    </span>
                </td>
                <td class="px-6 py-4">
                    <div class="flex items-center justify-end gap-2">
                        <a href="{{ route('projects.show', $project) }}" class="text-xs px-3 py-1.5 bg-indigo-50 text-indigo-600 rounded-lg hover:bg-indigo-100 transition">View</a>
                        <a href="{{ route('progress-logs.create') }}?project_id={{ $project->id }}" class="text-xs px-3 py-1.5 bg-green-50 text-green-600 rounded-lg hover:bg-green-100 transition">Log</a>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="px-6 py-16 text-center text-gray-400">
                    <i class="fas fa-project-diagram text-4xl mb-3 block text-gray-200"></i>
                    No projects assigned to you yet.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    @if(method_exists($projects, 'hasPages') && $projects->hasPages())
        <div class="px-6 py-4 border-t border-gray-100">{{ $projects->links() }}</div>
    @endif
</div>

@endsection
