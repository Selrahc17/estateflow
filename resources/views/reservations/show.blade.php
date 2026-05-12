@extends('layouts.app')

@section('title', 'Reservation Details - EstateFlow')
@section('page-title', 'Reservation Details')
@section('page-subtitle', $reservation->property->title ?? '')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Left: Reservation Info --}}
    <div class="lg:col-span-1 space-y-6">

        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-gray-800">Reservation Info</h3>
                <span class="text-xs px-2.5 py-1 rounded-full font-medium
                    {{ $reservation->status === 'pending'   ? 'bg-yellow-100 text-yellow-700' : '' }}
                    {{ $reservation->status === 'confirmed' ? 'bg-green-100 text-green-700'   : '' }}
                    {{ $reservation->status === 'cancelled' ? 'bg-red-100 text-red-700'       : '' }}
                    {{ $reservation->status === 'expired'   ? 'bg-gray-100 text-gray-600'     : '' }}
                    {{ $reservation->status === 'completed' ? 'bg-blue-100 text-blue-700'     : '' }}">
                    {{ ucfirst($reservation->status) }}
                </span>
            </div>

            <div class="space-y-3 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-500">Reservation Date</span>
                    <span class="font-medium text-gray-800">{{ $reservation->reservation_date->format('M d, Y') }}</span>
                </div>
                @if($reservation->expiry_date)
                <div class="flex justify-between">
                    <span class="text-gray-500">Expiry Date</span>
                    <span class="font-medium text-gray-800">{{ $reservation->expiry_date->format('M d, Y') }}</span>
                </div>
                @endif
                <div class="flex justify-between">
                    <span class="text-gray-500">Reservation Fee</span>
                    <span class="font-bold text-indigo-600">₱{{ number_format($reservation->reservation_fee, 2) }}</span>
                </div>
            </div>

            @if(auth()->user()->isAdmin())
            <div class="mt-6 space-y-2">
                @php
                    $totalPaid   = $reservation->payments->where('status', 'completed')->sum('amount');
                    $propertyPrice = $reservation->property->price ?? 0;
                    $remaining   = $propertyPrice - $totalPaid;
                @endphp

                <a href="{{ route('reservations.edit', $reservation) }}" class="block text-center bg-indigo-600 text-white py-2 rounded-lg text-sm hover:bg-indigo-700 transition">
                    <i class="fas fa-edit mr-1"></i> Edit Reservation
                </a>

                @if($reservation->status === 'confirmed')
                    @if($remaining <= 0)
                    <form method="POST" action="{{ route('reservations.update-status', $reservation) }}"
                        onsubmit="return confirm('Mark this reservation as completed? This will mark the property as sold.')">
                        @csrf
                        <input type="hidden" name="_method" value="PATCH">
                        <input type="hidden" name="status" value="completed">
                        <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded-lg text-sm hover:bg-blue-700 transition font-medium">
                            <i class="fas fa-flag-checkered mr-1"></i> Mark as Completed
                        </button>
                    </form>
                    @else
                    <div class="w-full bg-gray-100 text-gray-400 py-2 rounded-lg text-sm text-center cursor-not-allowed" title="Cannot complete — balance remaining: ₱{{ number_format($remaining, 2) }}">
                        <i class="fas fa-flag-checkered mr-1"></i> Mark as Completed
                        <p class="text-xs mt-0.5">Balance must be ₱0.00 first</p>
                    </div>
                    @endif

                    <form method="POST" action="{{ route('reservations.update-status', $reservation) }}"
                        onsubmit="return confirm('Cancel this reservation?')">
                        @csrf
                        <input type="hidden" name="_method" value="PATCH">
                        <input type="hidden" name="status" value="cancelled">
                        <button type="submit" class="w-full bg-red-50 text-red-600 py-2 rounded-lg text-sm hover:bg-red-100 transition font-medium">
                            <i class="fas fa-times mr-1"></i> Cancel Reservation
                        </button>
                    </form>
                @endif

                @if($reservation->status === 'pending')
                    <form method="POST" action="{{ route('reservations.update-status', $reservation) }}">
                        @csrf
                        <input type="hidden" name="_method" value="PATCH">
                        <input type="hidden" name="status" value="confirmed">
                        <button type="submit" class="w-full bg-green-600 text-white py-2 rounded-lg text-sm hover:bg-green-700 transition font-medium">
                            <i class="fas fa-check mr-1"></i> Confirm Reservation
                        </button>
                    </form>
                    <form method="POST" action="{{ route('reservations.update-status', $reservation) }}"
                        onsubmit="return confirm('Cancel this reservation?')">
                        @csrf
                        <input type="hidden" name="_method" value="PATCH">
                        <input type="hidden" name="status" value="cancelled">
                        <button type="submit" class="w-full bg-red-50 text-red-600 py-2 rounded-lg text-sm hover:bg-red-100 transition font-medium">
                            <i class="fas fa-times mr-1"></i> Cancel Reservation
                        </button>
                    </form>
                @endif

                <form method="POST" action="{{ route('reservations.destroy', $reservation) }}"
                    onsubmit="return confirm('Delete this reservation?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="w-full px-4 py-2 bg-red-50 text-red-600 rounded-lg text-sm hover:bg-red-100 transition">
                        <i class="fas fa-trash mr-1"></i> Delete
                    </button>
                </form>
            </div>
            @endif

            @if(auth()->user()->isAgent())
            <div class="mt-6 space-y-2">
                @if($reservation->status === 'pending')
                    <form method="POST" action="{{ route('reservations.update-status', $reservation) }}">
                        @csrf
                        <input type="hidden" name="_method" value="PATCH">
                        <input type="hidden" name="status" value="confirmed">
                        <button type="submit" class="w-full bg-green-600 text-white py-2 rounded-lg text-sm hover:bg-green-700 transition font-medium">
                            <i class="fas fa-check mr-1"></i> Confirm Reservation
                        </button>
                    </form>
                    <form method="POST" action="{{ route('reservations.update-status', $reservation) }}"
                        onsubmit="return confirm('Cancel this reservation?')">
                        @csrf
                        <input type="hidden" name="_method" value="PATCH">
                        <input type="hidden" name="status" value="cancelled">
                        <button type="submit" class="w-full bg-red-50 text-red-600 py-2 rounded-lg text-sm hover:bg-red-100 transition font-medium">
                            <i class="fas fa-times mr-1"></i> Cancel Reservation
                        </button>
                    </form>
                @endif
                @if($reservation->status === 'confirmed')
                    <p class="text-xs text-gray-400 text-center py-2"><i class="fas fa-info-circle mr-1"></i>Only admin can mark as completed.</p>
                @endif
            </div>
            @endif
        </div>

        {{-- Property --}}
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="font-semibold text-gray-800 mb-3 text-sm">Property</h3>
            @if($reservation->property)
                <p class="font-medium text-gray-800">{{ $reservation->property->title }}</p>
                <p class="text-xs text-gray-400 mt-1"><i class="fas fa-map-marker-alt mr-1"></i>{{ $reservation->property->location }}</p>
                <p class="text-sm font-bold text-indigo-600 mt-2">₱{{ number_format($reservation->property->price, 2) }}</p>
                <a href="{{ route('properties.show', $reservation->property) }}" class="mt-3 block text-center text-xs bg-gray-50 text-gray-600 py-2 rounded-lg hover:bg-gray-100 transition">
                    View Property
                </a>
            @endif
        </div>

        {{-- Client --}}
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="font-semibold text-gray-800 mb-3 text-sm">Client</h3>
            @if($reservation->client)
                <p class="font-medium text-gray-800">{{ $reservation->client->full_name }}</p>
                <p class="text-xs text-gray-400 mt-1">{{ $reservation->client->phone }}</p>
                <p class="text-xs text-gray-400">{{ $reservation->client->email }}</p>
                <a href="{{ route('clients.show', $reservation->client) }}" class="mt-3 block text-center text-xs bg-gray-50 text-gray-600 py-2 rounded-lg hover:bg-gray-100 transition">
                    View Client
                </a>
            @endif
        </div>

        {{-- Pag-IBIG Status --}}
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-gray-800 text-sm">Pag-IBIG Loan Status</h3>
                @php
                    $pagibigColors = [
                        'not_applied' => 'bg-gray-100 text-gray-600',
                        'applied'     => 'bg-blue-100 text-blue-700',
                        'verified'    => 'bg-yellow-100 text-yellow-700',
                        'approved'    => 'bg-green-100 text-green-700',
                        'released'    => 'bg-indigo-100 text-indigo-700',
                    ];
                @endphp
                <span class="text-xs px-2.5 py-1 rounded-full font-medium {{ $pagibigColors[$reservation->pagibig_status ?? 'not_applied'] }}">
                    {{ \App\Models\Reservation::PAGIBIG_STATUSES[$reservation->pagibig_status ?? 'not_applied'] }}
                </span>
            </div>

            @if($reservation->pagibig_reference)
                <p class="text-xs text-gray-500 mb-4"><span class="font-medium">Reference:</span> {{ $reservation->pagibig_reference }}</p>
            @endif

            {{-- Progress Steps --}}
            <div class="flex items-center justify-between mb-6">
                @foreach(['not_applied' => 'Not Applied', 'applied' => 'Applied', 'verified' => 'Verified', 'approved' => 'Approved', 'released' => 'Released'] as $step => $label)
                    @php
                        $steps = ['not_applied', 'applied', 'verified', 'approved', 'released'];
                        $currentIndex = array_search($reservation->pagibig_status ?? 'not_applied', $steps);
                        $stepIndex = array_search($step, $steps);
                        $isDone = $stepIndex <= $currentIndex;
                    @endphp
                    <div class="flex flex-col items-center flex-1">
                        <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold {{ $isDone ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-400' }}">
                            @if($isDone)<i class="fas fa-check text-xs"></i>@else{{ $stepIndex + 1 }}@endif
                        </div>
                        <p class="text-xs mt-1 text-center {{ $isDone ? 'text-indigo-600 font-medium' : 'text-gray-400' }}">{{ $label }}</p>
                    </div>
                    @if(!$loop->last)
                        <div class="flex-1 h-0.5 {{ $stepIndex < $currentIndex ? 'bg-indigo-600' : 'bg-gray-200' }} mb-4"></div>
                    @endif
                @endforeach
            </div>

            @if(auth()->user()->isAdmin() || auth()->user()->isAgent())
            <form method="POST" action="{{ route('reservations.update-pagibig', $reservation) }}" class="space-y-3">
                @csrf @method('PATCH')
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Update Status</label>
                    <select name="pagibig_status" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        @foreach(\App\Models\Reservation::PAGIBIG_STATUSES as $value => $label)
                            <option value="{{ $value }}" {{ ($reservation->pagibig_status ?? 'not_applied') === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Pag-IBIG Reference No. <span class="text-gray-400">(optional)</span></label>
                    <input type="text" name="pagibig_reference" value="{{ $reservation->pagibig_reference }}"
                        placeholder="e.g. HDMF-2024-XXXXX"
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <button type="submit" class="w-full bg-indigo-600 text-white py-2 rounded-lg text-sm hover:bg-indigo-700 transition font-medium">
                    <i class="fas fa-save mr-1"></i> Update Pag-IBIG Status
                </button>
            </form>
            @endif
        </div>

        {{-- Document Checklist --}}
        @include('partials.document-checklist', ['docCheck' => $docCheck, 'reservation' => $reservation])

        {{-- Agent --}}
        @if($reservation->agent)
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="font-semibold text-gray-800 mb-3 text-sm">Agent</h3>
            <p class="font-medium text-gray-800">{{ $reservation->agent->full_name }}</p>
            <p class="text-xs text-gray-400 mt-1">{{ $reservation->agent->phone }}</p>
            <a href="{{ route('agents.show', $reservation->agent) }}" class="mt-3 block text-center text-xs bg-gray-50 text-gray-600 py-2 rounded-lg hover:bg-gray-100 transition">
                View Agent
            </a>
        </div>
        @endif

    </div>

    {{-- Right: Payments --}}
    <div class="lg:col-span-2 space-y-6">

        @if($reservation->notes)
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="font-semibold text-gray-800 mb-2">Notes</h3>
            <p class="text-sm text-gray-600">{{ $reservation->notes }}</p>
        </div>
        @endif

        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-gray-800">Payments ({{ $reservation->payments->count() }})</h3>
                @if(auth()->user()->isAdmin() || auth()->user()->isAgent())
                    <a href="{{ route('payments.create') }}?reservation_id={{ $reservation->id }}" class="text-xs bg-indigo-600 text-white px-3 py-1.5 rounded-lg hover:bg-indigo-700 transition">
                        <i class="fas fa-plus mr-1"></i> Add Payment
                    </a>
                @endif
            </div>

            @forelse($reservation->payments as $payment)
            <div class="flex items-center justify-between py-3 border-b border-gray-50 last:border-0">
                <div>
                    <p class="text-sm font-medium text-gray-800">{{ ucfirst(str_replace('_', ' ', $payment->payment_type)) }}</p>
                    <p class="text-xs text-gray-400">{{ $payment->payment_date }} · {{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}</p>
                    @if($payment->reference_number)
                        <p class="text-xs text-gray-400">Ref: {{ $payment->reference_number }}</p>
                    @endif
                </div>
                <div class="text-right">
                    <p class="text-sm font-bold text-gray-800">₱{{ number_format($payment->amount, 2) }}</p>
                    <span class="text-xs px-2 py-0.5 rounded-full
                        {{ $payment->status === 'completed' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                        {{ ucfirst($payment->status) }}
                    </span>
                </div>
            </div>
            @empty
            <p class="text-sm text-gray-400">No payments recorded yet.</p>
            @endforelse

            @if($reservation->payments->count())
            @php
                $totalPaid     = $reservation->payments->where('status', 'completed')->sum('amount');
                $propertyPrice = $reservation->property->price ?? 0;
                $remaining     = $propertyPrice - $totalPaid;
            @endphp
            <div class="mt-4 pt-4 border-t border-gray-100 space-y-2">
                <div class="flex justify-between">
                    <span class="text-sm text-gray-500">Property Price</span>
                    <span class="text-sm font-medium text-gray-800">₱{{ number_format($propertyPrice, 2) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-sm text-gray-500">Total Paid</span>
                    <span class="text-sm font-bold text-green-600">₱{{ number_format($totalPaid, 2) }}</span>
                </div>
                <div class="flex justify-between pt-2 border-t border-gray-100">
                    <span class="text-sm font-semibold text-gray-700">Remaining Balance</span>
                    @if($remaining <= 0)
                        <span class="text-sm font-bold text-green-600">Fully Paid ✓</span>
                    @else
                        <span class="text-sm font-bold text-red-600">₱{{ number_format($remaining, 2) }}</span>
                    @endif
                </div>
            </div>
            @endif
        </div>

    </div>
</div>
@endsection
