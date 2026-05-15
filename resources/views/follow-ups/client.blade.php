<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Follow-Ups — EstateFlow</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50 font-sans">

@include('partials.client-nav')

<div class="max-w-4xl mx-auto px-6 py-8">

    <h1 class="text-2xl font-bold text-gray-900 mb-1">My Follow-Ups</h1>
    <p class="text-gray-500 text-sm mb-8">Scheduled calls, emails, and meetings from your agent</p>

    @forelse($schedules as $schedule)
    @php
        $typeIcons  = ['call' => 'fa-phone', 'email' => 'fa-envelope', 'site_visit' => 'fa-map-marker-alt', 'meeting' => 'fa-users'];
        $typeColors = ['call' => 'bg-blue-100 text-blue-700', 'email' => 'bg-purple-100 text-purple-700', 'site_visit' => 'bg-green-100 text-green-700', 'meeting' => 'bg-yellow-100 text-yellow-700'];
        $isOverdue  = $schedule->status === 'pending' && $schedule->follow_up_date->isPast();
    @endphp
    <div class="bg-white rounded-xl shadow-sm p-5 mb-4 border-l-4
        {{ $schedule->status === 'done'      ? 'border-green-400' : '' }}
        {{ $schedule->status === 'cancelled' ? 'border-gray-300'  : '' }}
        {{ $schedule->status === 'pending' && !$isOverdue ? 'border-indigo-400' : '' }}
        {{ $isOverdue ? 'border-red-400' : '' }}">
        <div class="flex items-start justify-between gap-4">
            <div class="flex items-start gap-4">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0 {{ $typeColors[$schedule->type] ?? 'bg-gray-100 text-gray-500' }}">
                    <i class="fas {{ $typeIcons[$schedule->type] ?? 'fa-calendar' }}"></i>
                </div>
                <div>
                    <p class="font-semibold text-gray-800">{{ ucfirst(str_replace('_', ' ', $schedule->type)) }} Follow-Up</p>
                    <p class="text-sm text-gray-500 mt-0.5">
                        <i class="fas fa-calendar mr-1"></i>
                        {{ $schedule->follow_up_date->format('M d, Y') }}
                        @if($schedule->follow_up_time)
                            at {{ \Carbon\Carbon::parse($schedule->follow_up_time)->format('g:i A') }}
                        @endif
                        @if($isOverdue) <span class="text-red-500 font-medium">(Overdue)</span> @endif
                        @if($schedule->follow_up_date->isToday()) <span class="text-orange-500 font-medium">(Today)</span> @endif
                    </p>
                    @if($schedule->agent)
                        <p class="text-xs text-gray-400 mt-1"><i class="fas fa-user-tie mr-1"></i>Agent: {{ $schedule->agent->full_name }}</p>
                    @endif
                    @if($schedule->reservation?->property)
                        <p class="text-xs text-gray-400 mt-0.5"><i class="fas fa-building mr-1"></i>{{ $schedule->reservation->property->title }}</p>
                    @endif
                    @if($schedule->notes)
                        <p class="text-xs text-gray-500 mt-2 bg-gray-50 rounded-lg px-3 py-2">
                            <i class="fas fa-comment-alt mr-1"></i>{{ $schedule->notes }}
                        </p>
                    @endif
                </div>
            </div>
            <span class="text-xs px-2.5 py-1 rounded-full font-medium flex-shrink-0
                {{ $schedule->status === 'pending'   ? 'bg-yellow-100 text-yellow-700' : '' }}
                {{ $schedule->status === 'done'      ? 'bg-green-100 text-green-700'   : '' }}
                {{ $schedule->status === 'cancelled' ? 'bg-gray-100 text-gray-500'     : '' }}">
                {{ ucfirst($schedule->status) }}
            </span>
        </div>
    </div>
    @empty
    <div class="bg-white rounded-xl shadow-sm p-16 text-center text-gray-400">
        <i class="fas fa-calendar-check text-4xl mb-3 block text-gray-200"></i>
        <p>No follow-ups scheduled yet.</p>
        <p class="text-xs mt-2">Your agent will schedule follow-ups to keep you updated.</p>
    </div>
    @endforelse

    @if($schedules->hasPages())
        <div class="mt-4">{{ $schedules->links() }}</div>
    @endif
</div>

<footer class="bg-gray-900 text-gray-400 py-8 px-6 text-center text-sm mt-16">
    <p>© {{ date('Y') }} EstateFlow. All rights reserved.</p>
</footer>

</body>
</html>
