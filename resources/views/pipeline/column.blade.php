@php
$colorMap = [
    'blue'   => ['header' => 'bg-blue-500',   'badge' => 'bg-blue-100 text-blue-700',   'btn' => 'bg-blue-50 text-blue-600 hover:bg-blue-100'],
    'purple' => ['header' => 'bg-purple-500',  'badge' => 'bg-purple-100 text-purple-700', 'btn' => 'bg-purple-50 text-purple-600 hover:bg-purple-100'],
    'yellow' => ['header' => 'bg-yellow-500',  'badge' => 'bg-yellow-100 text-yellow-700', 'btn' => 'bg-yellow-50 text-yellow-600 hover:bg-yellow-100'],
    'orange' => ['header' => 'bg-orange-500',  'badge' => 'bg-orange-100 text-orange-700', 'btn' => 'bg-orange-50 text-orange-600 hover:bg-orange-100'],
    'indigo' => ['header' => 'bg-indigo-500',  'badge' => 'bg-indigo-100 text-indigo-700', 'btn' => 'bg-indigo-50 text-indigo-600 hover:bg-indigo-100'],
    'green'  => ['header' => 'bg-green-500',   'badge' => 'bg-green-100 text-green-700',  'btn' => 'bg-green-50 text-green-600 hover:bg-green-100'],
];
$c = $colorMap[$color];

$nextStatusMap = [
    'new'       => ['status' => 'contacted', 'label' => 'Mark Contacted', 'route' => 'leads.status'],
    'contacted' => ['status' => 'qualified', 'label' => 'Mark Qualified',  'route' => 'leads.status'],
    'qualified' => ['status' => 'converted', 'label' => 'Mark Converted',  'route' => 'leads.status'],
    'pending'   => ['status' => 'confirmed', 'label' => 'Confirm',         'route' => 'reservations.update-status'],
    'confirmed' => ['status' => 'completed', 'label' => 'Mark Sold',       'route' => 'reservations.update-status'],
    'completed' => null,
];
$next = $nextStatusMap[$status] ?? null;
@endphp

<div class="w-64 flex-shrink-0">
    {{-- Column Header --}}
    <div class="{{ $c['header'] }} rounded-t-xl px-4 py-3 flex items-center justify-between">
        <div class="flex items-center gap-2">
            <i class="fas {{ $icon }} text-white text-sm"></i>
            <span class="text-white font-semibold text-sm">{{ $title }}</span>
        </div>
        <span class="bg-white bg-opacity-30 text-white text-xs font-bold px-2 py-0.5 rounded-full">{{ $count }}</span>
    </div>

    {{-- Cards --}}
    <div class="bg-gray-50 rounded-b-xl p-2 space-y-2 min-h-64 max-h-screen overflow-y-auto">
        @forelse($items as $item)
        <div class="bg-white rounded-xl p-3 shadow-sm border border-gray-100 hover:shadow-md transition">

            @if($type === 'lead')
                {{-- Lead Card --}}
                <div class="flex items-start justify-between gap-2 mb-2">
                    <div>
                        <p class="font-semibold text-gray-800 text-sm">{{ $item->name }}</p>
                        <p class="text-xs text-gray-400">{{ $item->phone ?? $item->email ?? '—' }}</p>
                    </div>
                    <span class="text-xs px-2 py-0.5 rounded-full {{ $c['badge'] }} flex-shrink-0">
                        {{ ucfirst($item->source === 'website_inquiry' ? 'Web' : str_replace('_',' ',$item->source)) }}
                    </span>
                </div>
                @if($item->interestedProperty)
                    <p class="text-xs text-gray-500 mb-1"><i class="fas fa-building mr-1 text-gray-300"></i>{{ Str::limit($item->interestedProperty->title, 30) }}</p>
                @elseif($item->preferred_location)
                    <p class="text-xs text-gray-500 mb-1"><i class="fas fa-map-marker-alt mr-1 text-gray-300"></i>{{ $item->preferred_location }}</p>
                @endif
                @if($item->budget_max)
                    <p class="text-xs text-indigo-600 font-medium mb-2">Budget: ₱{{ number_format($item->budget_max, 0) }}</p>
                @endif
                @if($item->assignedAgent)
                    <p class="text-xs text-gray-400 mb-2"><i class="fas fa-user-tie mr-1"></i>{{ $item->assignedAgent->full_name }}</p>
                @endif

            @else
                {{-- Reservation Card --}}
                <div class="flex items-start justify-between gap-2 mb-2">
                    <div>
                        <p class="font-semibold text-gray-800 text-sm">{{ $item->client->full_name ?? '—' }}</p>
                        <p class="text-xs text-gray-400">{{ $item->client->phone ?? '' }}</p>
                    </div>
                </div>
                @if($item->property)
                    <p class="text-xs text-gray-500 mb-1"><i class="fas fa-building mr-1 text-gray-300"></i>{{ Str::limit($item->property->title, 30) }}</p>
                    <p class="text-xs text-indigo-600 font-medium mb-1">₱{{ number_format($item->property->price, 0) }}</p>
                @endif
                @if($item->agent)
                    <p class="text-xs text-gray-400 mb-1"><i class="fas fa-user-tie mr-1"></i>{{ $item->agent->full_name }}</p>
                @endif
                @php $paid = $item->payments->where('status','completed')->sum('amount'); @endphp
                @if($paid > 0)
                    <p class="text-xs text-green-600 mb-2"><i class="fas fa-check-circle mr-1"></i>₱{{ number_format($paid, 0) }} paid</p>
                @endif
            @endif

            {{-- Action Button --}}
            @if($next)
                <form method="POST" action="{{ route($next['route'], $item) }}">
                    @csrf @method('PATCH')
                    <input type="hidden" name="status" value="{{ $next['status'] }}">
                    <button type="submit"
                        class="w-full text-xs py-1.5 rounded-lg font-medium transition {{ $c['btn'] }}">
                        {{ $next['label'] }} →
                    </button>
                </form>
            @else
                <div class="w-full text-xs py-1.5 rounded-lg text-center bg-green-50 text-green-600 font-medium">
                    <i class="fas fa-trophy mr-1"></i> Closed
                </div>
            @endif

            {{-- Edit link --}}
            <a href="{{ $type === 'lead' ? route('leads.edit', $item) : route('reservations.show', $item) }}"
                class="block text-center text-xs text-gray-400 hover:text-indigo-500 mt-1.5 transition">
                {{ $type === 'lead' ? 'Edit Lead' : 'View Reservation' }}
            </a>
        </div>
        @empty
        <div class="text-center py-8 text-gray-300">
            <i class="fas fa-inbox text-2xl mb-2 block"></i>
            <p class="text-xs">No items</p>
        </div>
        @endforelse
    </div>
</div>
