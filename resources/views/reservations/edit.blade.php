@extends('layouts.app')

@section('title', 'Edit Reservation - EstateFlow')
@section('page-title', 'Edit Reservation')
@section('page-subtitle', '{{ $reservation->property->title ?? "" }}')

@section('content')
<div class="max-w-2xl">
    <div class="bg-white rounded-xl shadow-sm p-6">

        @if(auth()->user()->isAgent())
        {{-- Agent: simplified form — status + notes only --}}
        <div class="mb-6 pb-4 border-b border-gray-100">
            <p class="text-sm font-medium text-gray-800">{{ $reservation->property->title ?? '—' }}</p>
            <p class="text-xs text-gray-400 mt-1">Client: {{ $reservation->client->full_name ?? '—' }} &nbsp;·&nbsp; Date: {{ $reservation->reservation_date->format('M d, Y') }}</p>
        </div>
        <form method="POST" action="{{ route('reservations.update', $reservation) }}">
            @csrf @method('PUT')
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status <span class="text-red-500">*</span></label>
                    <select name="status" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="pending"   {{ $reservation->status === 'pending'   ? 'selected' : '' }}>Pending</option>
                        <option value="confirmed" {{ $reservation->status === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                        <option value="cancelled" {{ $reservation->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        <option value="expired"   {{ $reservation->status === 'expired'   ? 'selected' : '' }}>Expired</option>
                        <option value="completed" {{ $reservation->status === 'completed' ? 'selected' : '' }}>Completed</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                    <textarea name="notes" rows="3"
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('notes', $reservation->notes) }}</textarea>
                </div>
            </div>
            <div class="mt-6 flex gap-3">
                <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded-lg text-sm hover:bg-indigo-700 transition font-medium">Update Status</button>
                <a href="{{ route('agent.reservations') }}" class="bg-gray-100 text-gray-600 px-6 py-2 rounded-lg text-sm hover:bg-gray-200 transition">Cancel</a>
            </div>
        </form>

        @else
        {{-- Admin: full edit form --}}
        <form method="POST" action="{{ route('reservations.update', $reservation) }}">
            @csrf @method('PUT')
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Property <span class="text-red-500">*</span></label>
                    <select name="property_id" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        @foreach($properties as $property)
                            <option value="{{ $property->id }}" {{ old('property_id', $reservation->property_id) == $property->id ? 'selected' : '' }}>
                                {{ $property->title }} — ₱{{ number_format($property->price, 2) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Client <span class="text-red-500">*</span></label>
                    <select name="client_id" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        @foreach($clients as $client)
                            <option value="{{ $client->id }}" {{ old('client_id', $reservation->client_id) == $client->id ? 'selected' : '' }}>
                                {{ $client->full_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Agent</label>
                    <select name="agent_id" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">None</option>
                        @foreach($agents as $agent)
                            <option value="{{ $agent->id }}" {{ old('agent_id', $reservation->agent_id) == $agent->id ? 'selected' : '' }}>
                                {{ $agent->full_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Reservation Date <span class="text-red-500">*</span></label>
                    <input type="date" name="reservation_date" value="{{ old('reservation_date', $reservation->reservation_date->format('Y-m-d')) }}"
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Expiry Date</label>
                    <input type="date" name="expiry_date" value="{{ old('expiry_date', $reservation->expiry_date?->format('Y-m-d')) }}"
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Reservation Fee (₱)</label>
                    <input type="number" name="reservation_fee" value="{{ old('reservation_fee', $reservation->reservation_fee) }}" step="0.01" min="0"
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status <span class="text-red-500">*</span></label>
                    <select name="status" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="pending"   {{ old('status', $reservation->status) === 'pending'   ? 'selected' : '' }}>Pending</option>
                        <option value="confirmed" {{ old('status', $reservation->status) === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                        <option value="cancelled" {{ old('status', $reservation->status) === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        <option value="expired"   {{ old('status', $reservation->status) === 'expired'   ? 'selected' : '' }}>Expired</option>
                        <option value="completed" {{ old('status', $reservation->status) === 'completed' ? 'selected' : '' }}>Completed</option>
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                    <textarea name="notes" rows="3"
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('notes', $reservation->notes) }}</textarea>
                </div>
            </div>

            <div class="mt-6 flex gap-3">
                <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded-lg text-sm hover:bg-indigo-700 transition font-medium">Update Reservation</button>
                <a href="{{ route('reservations.show', $reservation) }}" class="bg-gray-100 text-gray-600 px-6 py-2 rounded-lg text-sm hover:bg-gray-200 transition">Cancel</a>
            </div>
        </form>
        @endif
    </div>
</div>
@endsection
