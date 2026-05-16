@extends('layouts.client')
@section('title', 'Schedule Site Viewing — EstateFlow')
@section('page-title', 'Schedule a Site Viewing')
@section('page-subtitle', 'Request a visit to view your reserved property in person')
@section('content')

<div class="max-w-2xl">

    <a href="{{ route('client.reservations') }}" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-indigo-600 transition mb-6">
        <i class="fas fa-arrow-left"></i> Back to Reservations
    </a>

    {{-- Property Info --}}
    <div class="bg-white rounded-xl shadow-sm p-4 mb-6 flex items-center gap-4">
        <div class="w-14 h-14 bg-indigo-50 rounded-xl flex items-center justify-center flex-shrink-0 overflow-hidden">
            @if($reservation->property?->image_main)
                <img src="{{ asset($reservation->property->image_main) }}" class="w-14 h-14 object-cover rounded-xl">
            @else
                <i class="fas fa-building text-indigo-300 text-xl"></i>
            @endif
        </div>
        <div>
            <p class="font-semibold text-gray-800">{{ $reservation->property->title ?? '—' }}</p>
            <p class="text-xs text-gray-400 mt-0.5">
                <i class="fas fa-map-marker-alt mr-1"></i>{{ $reservation->property->location ?? 'Location not set' }}
            </p>
            @if($reservation->property?->block)
                <p class="text-xs text-gray-400">Blk {{ $reservation->property->block }}, Lot {{ $reservation->property->lot }}</p>
            @endif
            <span class="text-xs px-2 py-0.5 rounded-full font-medium mt-1 inline-block
                {{ $reservation->status === 'confirmed' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                {{ ucfirst($reservation->status) }}
            </span>
        </div>
    </div>

    {{-- Already has a pending/confirmed schedule --}}
    @if($existing)
    <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 flex items-start gap-3">
        <i class="fas fa-calendar-check text-yellow-500 mt-0.5 flex-shrink-0"></i>
        <div>
            <p class="text-sm font-semibold text-yellow-800">You already have a {{ $existing->status }} site viewing.</p>
            <p class="text-xs text-yellow-600 mt-1">
                Scheduled: {{ $existing->preferred_date->format('M d, Y') }} at {{ \Carbon\Carbon::parse($existing->preferred_time)->format('g:i A') }}
            </p>
            <p class="text-xs text-yellow-600 mt-1">Please wait for it to be resolved before submitting a new one.</p>
        </div>
    </div>

    @else

    <div class="bg-white rounded-xl shadow-sm p-6">
        <form method="POST" action="{{ route('site-viewing.store', $reservation) }}" class="space-y-4">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Preferred Date <span class="text-red-500">*</span></label>
                    <input type="date" name="preferred_date" value="{{ old('preferred_date') }}"
                        min="{{ now()->addDay()->format('Y-m-d') }}"
                        class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Preferred Time <span class="text-red-500">*</span></label>
                    <select name="preferred_time" class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">Select time</option>
                        @foreach(['08:00', '09:00', '10:00', '11:00', '13:00', '14:00', '15:00', '16:00'] as $time)
                            <option value="{{ $time }}" {{ old('preferred_time') === $time ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::parse($time)->format('g:i A') }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Notes <span class="text-gray-400 font-normal">(optional)</span></label>
                <textarea name="notes" rows="3"
                    placeholder="Any special requests or questions for the agent..."
                    class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 resize-none">{{ old('notes') }}</textarea>
            </div>

            <div class="bg-blue-50 border border-blue-100 rounded-xl p-3 text-xs text-blue-700">
                <i class="fas fa-info-circle mr-1"></i>
                Office hours are Monday–Friday 8AM–5PM and Saturday 8AM–12PM. An agent will confirm your schedule within 24 hours.
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit"
                    class="bg-indigo-600 text-white px-6 py-2.5 rounded-xl text-sm font-semibold hover:bg-indigo-700 transition">
                    <i class="fas fa-calendar-check mr-2"></i>Submit Request
                </button>
                <a href="{{ route('client.reservations') }}"
                    class="bg-gray-100 text-gray-600 px-6 py-2.5 rounded-xl text-sm hover:bg-gray-200 transition">
                    Cancel
                </a>
            </div>
        </form>
    </div>

    @endif

</div>

@endsection
