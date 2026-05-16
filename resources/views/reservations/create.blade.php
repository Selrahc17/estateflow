@extends('layouts.app')
@section('title', 'New Reservation - EstateFlow')
@section('page-title', 'New Reservation')
@section('page-subtitle', 'Create a property reservation')
@section('content')
<div class="max-w-2xl">

    @if($errors->any())
        <div class="mb-4 bg-red-50 border border-red-200 rounded-xl p-4">
            <ul class="text-sm text-red-600 space-y-1 list-disc list-inside">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

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
    </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm p-6">
        <form method="POST" action="{{ route('reservations.store') }}">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Property <span class="text-red-500">*</span></label>
                    @if($selectedProperty)
                        <div class="w-full px-3 py-2 border border-gray-100 bg-gray-50 rounded-lg text-sm text-gray-700">{{ $selectedProperty->title }}</div>
                        <input type="hidden" name="property_id" value="{{ $selectedProperty->id }}">
                    @else
                        <select name="property_id" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">Select Property</option>
                            @foreach($properties as $property)
                                <option value="{{ $property->id }}" {{ old('property_id') == $property->id ? 'selected' : '' }}>
                                    {{ $property->title }} — ₱{{ number_format($property->price, 0) }}
                                </option>
                            @endforeach
                        </select>
                    @endif
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Client <span class="text-red-500">*</span></label>
                    <select name="client_id" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">Select Client</option>
                        @foreach($clients as $client)
                            <option value="{{ $client->id }}" {{ old('client_id', request('client_id')) == $client->id ? 'selected' : '' }}>
                                {{ $client->full_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status <span class="text-red-500">*</span></label>
                    <select name="status" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        @foreach(['pending','confirmed','cancelled','expired','completed'] as $s)
                            <option value="{{ $s }}" {{ old('status','pending') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Agent</label>
                    <select name="agent_id" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">No preference</option>
                        @foreach($agents as $agent)
                            <option value="{{ $agent->id }}" {{ old('agent_id') == $agent->id ? 'selected' : '' }}>
                                {{ $agent->agent_code }} — {{ $agent->full_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Reservation Date <span class="text-red-500">*</span></label>
                    <input type="date" name="reservation_date" value="{{ old('reservation_date', now()->format('Y-m-d')) }}"
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

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

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                    <textarea name="notes" rows="3"
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('notes') }}</textarea>
                </div>

            </div>

            <div class="mt-6 flex gap-3">
                <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded-lg text-sm hover:bg-indigo-700 transition font-medium">
                    Save Reservation
                </button>
                <a href="{{ route('reservations.index') }}" class="bg-gray-100 text-gray-600 px-6 py-2 rounded-lg text-sm hover:bg-gray-200 transition">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
