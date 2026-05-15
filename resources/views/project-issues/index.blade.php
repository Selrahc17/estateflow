@extends('layouts.app')

@section('title', 'Project Issues — EstateFlow')
@section('page-title', 'Delay & Issue Reports')
@section('page-subtitle', 'Track and resolve project issues')

@section('content')

@if(session('success'))
    <div class="mb-4 bg-green-50 border border-green-200 text-green-700 text-sm px-4 py-3 rounded-xl flex items-center gap-2">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
    </div>
@endif

{{-- Stats --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-xl shadow-sm p-4 text-center">
        <p class="text-2xl font-bold text-red-600">{{ $openCount }}</p>
        <p class="text-xs text-gray-500 mt-1">Open Issues</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-4 text-center">
        <p class="text-2xl font-bold text-orange-600">{{ $criticalCount }}</p>
        <p class="text-xs text-gray-500 mt-1">Critical</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-4 text-center">
        <p class="text-2xl font-bold text-green-600">{{ \App\Models\ProjectIssue::where('status','resolved')->count() }}</p>
        <p class="text-xs text-gray-500 mt-1">Resolved</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-4 text-center">
        <p class="text-2xl font-bold text-indigo-600">{{ \App\Models\ProjectIssue::whereIn('status',['open','in_progress'])->sum('impact_days') }}</p>
        <p class="text-xs text-gray-500 mt-1">Total Delay Days</p>
    </div>
</div>

{{-- Filters --}}
<div class="bg-white rounded-xl shadow-sm p-4 mb-6">
    <form method="GET" action="{{ route('project-issues.index') }}" class="flex flex-wrap gap-3 items-end">
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Project</label>
            <select name="project_id" class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">All Projects</option>
                @foreach($projects as $project)
                    <option value="{{ $project->id }}" {{ request('project_id') == $project->id ? 'selected' : '' }}>{{ $project->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Status</label>
            <select name="status" class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">All</option>
                @foreach(['open' => 'Open', 'in_progress' => 'In Progress', 'resolved' => 'Resolved', 'dismissed' => 'Dismissed'] as $val => $label)
                    <option value="{{ $val }}" {{ request('status') === $val ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Severity</label>
            <select name="severity" class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">All</option>
                @foreach(['low' => 'Low', 'medium' => 'Medium', 'high' => 'High', 'critical' => 'Critical'] as $val => $label)
                    <option value="{{ $val }}" {{ request('severity') === $val ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Type</label>
            <select name="type" class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">All Types</option>
                @foreach(['delay' => 'Delay', 'material_shortage' => 'Material Shortage', 'safety' => 'Safety', 'quality' => 'Quality', 'weather' => 'Weather', 'other' => 'Other'] as $val => $label)
                    <option value="{{ $val }}" {{ request('type') === $val ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-indigo-700 transition">
            <i class="fas fa-search mr-1"></i> Filter
        </button>
        <a href="{{ route('project-issues.index') }}" class="bg-gray-100 text-gray-600 px-4 py-2 rounded-lg text-sm hover:bg-gray-200 transition">Reset</a>
    </form>
</div>

{{-- Issues Table --}}
<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-100">
            <tr>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Issue</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Project</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Severity</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Impact</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Reported By</th>
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @forelse($issues as $issue)
            <tr class="hover:bg-gray-50 transition {{ $issue->severity === 'critical' && in_array($issue->status, ['open','in_progress']) ? 'bg-red-50' : '' }}">
                <td class="px-5 py-4">
                    <p class="font-medium text-gray-800">{{ $issue->title }}</p>
                    <span class="text-xs px-2 py-0.5 rounded-full
                        {{ $issue->type === 'delay'             ? 'bg-red-100 text-red-600'     : '' }}
                        {{ $issue->type === 'material_shortage' ? 'bg-orange-100 text-orange-600' : '' }}
                        {{ $issue->type === 'safety'            ? 'bg-pink-100 text-pink-600'   : '' }}
                        {{ $issue->type === 'quality'           ? 'bg-purple-100 text-purple-600' : '' }}
                        {{ $issue->type === 'weather'           ? 'bg-blue-100 text-blue-600'   : '' }}
                        {{ $issue->type === 'other'             ? 'bg-gray-100 text-gray-600'   : '' }}">
                        {{ ucfirst(str_replace('_', ' ', $issue->type)) }}
                    </span>
                </td>
                <td class="px-5 py-4">
                    <a href="{{ route('projects.show', $issue->project) }}" class="text-indigo-600 hover:underline text-sm">
                        {{ $issue->project->name ?? '—' }}
                    </a>
                </td>
                <td class="px-5 py-4">
                    <span class="text-xs px-2.5 py-1 rounded-full font-semibold
                        {{ $issue->severity === 'low'      ? 'bg-gray-100 text-gray-600'     : '' }}
                        {{ $issue->severity === 'medium'   ? 'bg-yellow-100 text-yellow-700' : '' }}
                        {{ $issue->severity === 'high'     ? 'bg-orange-100 text-orange-700' : '' }}
                        {{ $issue->severity === 'critical' ? 'bg-red-100 text-red-700'       : '' }}">
                        {{ ucfirst($issue->severity) }}
                    </span>
                </td>
                <td class="px-5 py-4 text-sm">
                    @if($issue->impact_days > 0)
                        <span class="text-red-600 font-medium">{{ $issue->impact_days }} day(s)</span>
                    @else
                        <span class="text-gray-400">—</span>
                    @endif
                </td>
                <td class="px-5 py-4">
                    <span class="text-xs px-2.5 py-1 rounded-full font-medium
                        {{ $issue->status === 'open'        ? 'bg-red-100 text-red-700'     : '' }}
                        {{ $issue->status === 'in_progress' ? 'bg-blue-100 text-blue-700'   : '' }}
                        {{ $issue->status === 'resolved'    ? 'bg-green-100 text-green-700' : '' }}
                        {{ $issue->status === 'dismissed'   ? 'bg-gray-100 text-gray-500'   : '' }}">
                        {{ ucfirst(str_replace('_', ' ', $issue->status)) }}
                    </span>
                </td>
                <td class="px-5 py-4 text-xs text-gray-500">
                    <p>{{ $issue->reportedBy->name ?? '—' }}</p>
                    <p class="text-gray-400">{{ $issue->created_at->format('M d, Y') }}</p>
                </td>
                <td class="px-5 py-4">
                    <div class="flex items-center gap-2">
                        @if(auth()->user()->isAdmin() && in_array($issue->status, ['open', 'in_progress']))
                            <button
                                data-id="{{ $issue->id }}"
                                data-title="{{ addslashes($issue->title) }}"
                                onclick="openResolveModal(this.dataset.id, this.dataset.title)"
                                class="text-xs bg-indigo-600 text-white px-3 py-1.5 rounded-lg hover:bg-indigo-700 transition">
                                Update
                            </button>
                        @endif
                        @if(auth()->user()->isAdmin())
                            <form method="POST" action="{{ route('project-issues.destroy', $issue) }}"
                                onsubmit="return confirm('Delete this issue?')">
                                @csrf @method('DELETE')
                                <button class="text-xs text-gray-300 hover:text-red-500 transition">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        @endif
                    </div>
                    @if($issue->resolution_notes)
                        <p class="text-xs text-gray-400 mt-1 italic">"{{ Str::limit($issue->resolution_notes, 40) }}"</p>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="px-5 py-16 text-center text-gray-400">
                    <i class="fas fa-check-circle text-4xl mb-3 block text-gray-200"></i>
                    No issues reported.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    @if($issues->hasPages())
        <div class="px-5 py-4 border-t border-gray-100">{{ $issues->links() }}</div>
    @endif
</div>

{{-- Resolve Modal --}}
<div id="resolveModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6">
        <h3 class="font-bold text-gray-900 mb-1">Update Issue Status</h3>
        <p class="text-sm text-gray-500 mb-4" id="modalIssueTitle"></p>
        <form method="POST" id="resolveForm" class="space-y-4">
            @csrf @method('PATCH')
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">New Status</label>
                <select name="status" class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="in_progress">In Progress</option>
                    <option value="resolved">Resolved</option>
                    <option value="dismissed">Dismissed</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Resolution Notes <span class="text-gray-400 font-normal">(optional)</span></label>
                <textarea name="resolution_notes" rows="3" placeholder="What was done to resolve this issue..."
                    class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 resize-none"></textarea>
            </div>
            <div class="flex gap-3">
                <button type="submit" class="flex-1 bg-indigo-600 text-white py-2.5 rounded-xl text-sm font-semibold hover:bg-indigo-700 transition">Save</button>
                <button type="button" onclick="closeResolveModal()" class="flex-1 bg-gray-100 text-gray-600 py-2.5 rounded-xl text-sm hover:bg-gray-200 transition">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
function openResolveModal(id, title) {
    document.getElementById('modalIssueTitle').textContent = title;
    document.getElementById('resolveForm').action = '/project-issues/' + id + '/status';
    const modal = document.getElementById('resolveModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}
function closeResolveModal() {
    const modal = document.getElementById('resolveModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}
</script>

@endsection
