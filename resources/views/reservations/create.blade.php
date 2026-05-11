@extends('layouts.app')

@section('title', 'Reserve Property - EstateFlow')
@section('page-title', auth()->user()->isClient() ? 'Reserve a Property' : 'New Reservation')
@section('page-subtitle', auth()->user()->isClient() ? 'Submit your reservation request' : 'Create a property reservation')

@section('content')
<div class="max-w-2xl">

    {{-- Client: no profile warning --}}
    @if(auth()->user()->isClient() && !$myClientRecord)
        <div class="mb-6 bg-red-50 border border-red-200 rounded-xl p-4 flex items-start gap-3">
            <i class="fas fa-exclamation-circle text-red-500 mt-0.5"></i>
            <div>
                <p class="text-sm font-medium text-red-800">Your client profile is not set up yet.</p>
                <p class="text-xs text-red-600 mt-1">Please contact an administrator before making a reservation.</p>
            </div>
        </div>
    @endif

    {{-- Property preview card if pre-selected --}}
    @if($selectedProperty)
    <div class="bg-white rounded-xl shadow-sm p-5 mb-6 flex items-center gap-4">
        @if($selectedProperty->image_main)
            <img src="{{ asset($selectedProperty->image_main) }}" class="w-20 h-16 object-cover rounded-lg flex-shrink-0">
        @else
            <div class="w-20 h-16 bg-indigo-50 rounded-lg flex items-center justify-center flex-shrink-0">
                <i class="fas fa-building text-indigo-300 text-2xl"></i>
            </div>
        @endif
        <div class="flex-1 min-w-0">
            <p class="font-semibold text-gray-800">{{ $selectedProperty->title }}</p>
            <p class="text-xs text-gray-400 mt-0.5"><i class="fas fa-map-marker-alt mr-1"></i>{{ $selectedProperty->location }}</p>
            <p class="text-indigo-600 font-bold mt-1">₱{{ number_format($selectedProperty->price, 0) }}</p>
        </div>
        <span class="text-xs px-2.5 py-1 rounded-full font-medium bg-green-100 text-green-700 flex-shrink-0">
            Available
        </span>
    </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm p-6">
        <form method="POST" action="{{ route('reservations.store') }}">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                {{-- Property --}}
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Property <span class="text-red-500">*</span></label>
                    @if($selectedProperty && auth()->user()->isClient())
                        {{-- Client: show as read-only with hidden input --}}
                        <div class="w-full px-3 py-2 border border-gray-100 bg-gray-50 rounded-lg text-sm text-gray-700">
                            {{ $selectedProperty->title }}
                        </div>
                        <input type="hidden" name="property_id" value="{{ $selectedProperty->id }}">
                    @else
                        <select name="property_id" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">Select Property</option>
                            @foreach($properties as $property)
                                <option value="{{ $property->id }}"
                                    {{ old('property_id', $selectedProperty ? $selectedProperty->id : '') == $property->id ? 'selected' : '' }}>
                                    {{ $property->title }} — ₱{{ number_format($property->price, 0) }}
                                </option>
                            @endforeach
                        </select>
                        @if($selectedProperty)
                            <p class="text-xs text-green-600 mt-1"><i class="fas fa-check-circle mr-1"></i>Pre-selected from property page.</p>
                        @endif
                    @endif
                </div>

                {{-- Client: hidden for client role, dropdown for admin/agent --}}
                @if(auth()->user()->isClient())
                    <input type="hidden" name="client_id" value="{{ $myClientRecord?->id }}">
                    <input type="hidden" name="status" value="pending">

                    {{-- Show client info as read-only --}}
                    <div class="md:col-span-2 bg-indigo-50 border border-indigo-100 rounded-xl p-4">
                        <p class="text-xs font-semibold text-indigo-700 mb-2">Your Information</p>
                        <div class="grid grid-cols-2 gap-2 text-sm text-indigo-800">
                            <div><span class="text-indigo-500 text-xs">Name:</span> {{ $myClientRecord?->full_name ?? auth()->user()->name }}</div>
                            <div><span class="text-indigo-500 text-xs">Phone:</span> {{ $myClientRecord?->phone ?? '—' }}</div>
                            <div class="col-span-2"><span class="text-indigo-500 text-xs">Email:</span> {{ auth()->user()->email }}</div>
                        </div>
                    </div>
                @else
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Client <span class="text-red-500">*</span></label>
                        <select name="client_id" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">Select Client</option>
                            @foreach($clients as $client)
                                <option value="{{ $client->id }}" {{ old('client_id') == $client->id ? 'selected' : '' }}>
                                    {{ $client->full_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status <span class="text-red-500">*</span></label>
                        <select name="status" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            @foreach(['pending','confirmed','cancelled','expired','completed'] as $s)
                                <option value="{{ $s }}" {{ old('status','pending') === $s ? 'selected' : '' }}>
                                    {{ ucfirst($s) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                {{-- Agent --}}
                <div class="{{ auth()->user()->isClient() ? 'md:col-span-2' : '' }}">
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Preferred Agent <span class="text-gray-400 font-normal">(optional)</span>
                    </label>
                    <select name="agent_id" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">No preference</option>
                        @foreach($agents as $agent)
                            <option value="{{ $agent->id }}" {{ old('agent_id') == $agent->id ? 'selected' : '' }}>
                                {{ $agent->full_name }} ({{ $agent->commission_rate }}% commission)
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Reservation Date --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Reservation Date <span class="text-red-500">*</span></label>
                    <input type="date" name="reservation_date" value="{{ old('reservation_date', now()->format('Y-m-d')) }}"
                        min="{{ now()->format('Y-m-d') }}"
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                @if(!auth()->user()->isClient())
                {{-- Expiry Date & Fee — admin/agent only --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Expiry Date</label>
                    <input type="date" name="expiry_date" value="{{ old('expiry_date') }}"
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Reservation Fee (₱)</label>
                    <input type="number" name="reservation_fee" value="{{ old('reservation_fee', 0) }}" step="0.01" min="0"
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                @endif

                {{-- Notes --}}
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        {{ auth()->user()->isClient() ? 'Message to Agent (optional)' : 'Notes' }}
                    </label>
                    <textarea name="notes" rows="3"
                        placeholder="{{ auth()->user()->isClient() ? 'Any questions or special requests for the agent...' : '' }}"
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('notes') }}</textarea>
                </div>

                {{-- Client info box --}}
                @if(auth()->user()->isClient())
                <div class="md:col-span-2 bg-blue-50 border border-blue-100 rounded-xl p-3 text-xs text-blue-700">
                    <i class="fas fa-info-circle mr-1"></i>
                    Your reservation will be submitted as <strong>Pending</strong>. An agent will review and confirm it shortly. You will be notified once confirmed.
                </div>
                @endif

            </div>

            <div class="mt-6 flex gap-3">
                <button type="submit"
                    {{ auth()->user()->isClient() && !$myClientRecord ? 'disabled' : '' }}
                    class="bg-indigo-600 text-white px-6 py-2 rounded-lg text-sm hover:bg-indigo-700 transition font-medium disabled:opacity-50 disabled:cursor-not-allowed">
                    {{ auth()->user()->isClient() ? 'Submit Reservation Request' : 'Save Reservation' }}
                </button>
                <a href="{{ auth()->user()->isClient() ? route('client.reservations') : route('reservations.index') }}"
                    class="bg-gray-100 text-gray-600 px-6 py-2 rounded-lg text-sm hover:bg-gray-200 transition">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
