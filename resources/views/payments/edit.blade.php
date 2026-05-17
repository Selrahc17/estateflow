@extends('layouts.app')

@section('title', 'Edit Payment - EstateFlow')
@section('page-title', 'Edit Payment')
@section('page-subtitle', 'Update payment details')

@section('content')
<div class="max-w-2xl">
    <div class="bg-white rounded-xl shadow-sm p-6">
        <form method="POST" action="{{ route('payments.update', $payment) }}" enctype="multipart/form-data">
            @csrf @method('PUT')
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Reservation</label>
                    <select name="reservation_id" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">No linked reservation</option>
                        @foreach($reservations as $reservation)
                            <option value="{{ $reservation->id }}" {{ old('reservation_id', $payment->reservation_id) == $reservation->id ? 'selected' : '' }}>
                                #{{ $reservation->id }} — {{ $reservation->property->title ?? '' }} ({{ $reservation->client->full_name ?? '' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Client <span class="text-red-500">*</span></label>
                    <select name="client_id" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        @foreach($clients as $client)
                            <option value="{{ $client->id }}" {{ old('client_id', $payment->client_id) == $client->id ? 'selected' : '' }}>
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
                            <option value="{{ $agent->id }}" {{ old('agent_id', $payment->agent_id) == $agent->id ? 'selected' : '' }}>
                                {{ $agent->full_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Payment Type <span class="text-red-500">*</span></label>
                    <select name="payment_type" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        @foreach(['reservation','downpayment','installment','full_payment','other'] as $type)
                            <option value="{{ $type }}" {{ old('payment_type', $payment->payment_type) === $type ? 'selected' : '' }}>
                                {{ ucfirst(str_replace('_', ' ', $type)) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Amount (₱) <span class="text-red-500">*</span></label>
                    <input type="number" name="amount" value="{{ old('amount', $payment->amount) }}" step="0.01" min="0"
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Currency</label>
                    <input type="text" name="currency" value="{{ old('currency', $payment->currency) }}" maxlength="3"
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Payment Method <span class="text-red-500">*</span></label>
                    <select name="payment_method" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        @foreach(['cash','bank_transfer','credit_card','check','pagibig'] as $method)
                            <option value="{{ $method }}" {{ old('payment_method', $payment->payment_method) === $method ? 'selected' : '' }}>
                                {{ ucfirst(str_replace('_', ' ', $method)) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Reference Number</label>
                    <input type="text" name="reference_number" value="{{ old('reference_number', $payment->reference_number) }}"
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Payment Date <span class="text-red-500">*</span></label>
                    <input type="date" name="payment_date" value="{{ old('payment_date', $payment->payment_date->format('Y-m-d')) }}"
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status <span class="text-red-500">*</span></label>
                    <select name="status" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        @foreach(['completed','pending','failed','cancelled'] as $s)
                            <option value="{{ $s }}" {{ old('status', $payment->status) === $s ? 'selected' : '' }}>
                                {{ ucfirst($s) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea name="description" rows="2"
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('description', $payment->description) }}</textarea>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Payment Proof <span class="text-gray-400 font-normal">(receipt, screenshot, etc.)</span></label>
                    @if($payment->proof_image)
                        <img src="{{ asset('storage/' . $payment->proof_image) }}" class="w-32 h-24 object-cover rounded-lg mb-2 border border-gray-200">
                    @endif
                    <input type="file" name="proof_image" accept="image/*"
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <p class="text-xs text-gray-400 mt-1">Leave blank to keep existing. Max 5MB.</p>
                </div>
            </div>

            <div class="mt-6 flex gap-3">
                <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded-lg text-sm hover:bg-indigo-700 transition font-medium">Update Payment</button>
                <a href="{{ route('payments.show', $payment) }}" class="bg-gray-100 text-gray-600 px-6 py-2 rounded-lg text-sm hover:bg-gray-200 transition">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
