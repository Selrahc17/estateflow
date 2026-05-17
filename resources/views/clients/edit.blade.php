@extends('layouts.app')

@section('title', 'Edit Client - EstateFlow')
@section('page-title', 'Edit Client')
@section('page-subtitle', $client->full_name)

@section('content')
<div class="max-w-2xl">
    <div class="bg-white rounded-xl shadow-sm p-6">
        <form method="POST" action="{{ route('clients.update', $client) }}">
            @csrf @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">First Name <span class="text-red-500">*</span></label>
                    <input type="text" name="first_name" value="{{ old('first_name', $client->first_name) }}"
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Last Name <span class="text-red-500">*</span></label>
                    <input type="text" name="last_name" value="{{ old('last_name', $client->last_name) }}"
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Phone <span class="text-red-500">*</span></label>
                    <input type="text" name="phone" value="{{ old('phone', $client->phone) }}"
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Alt Phone</label>
                    <input type="text" name="phone_alt" value="{{ old('phone_alt', $client->phone_alt) }}"
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" name="email" value="{{ old('email', $client->email) }}"
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                    <textarea name="address" rows="2"
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('address', $client->address) }}</textarea>
                </div>

                <div class="md:col-span-2 border-t border-gray-100 pt-4">
                    <p class="text-sm font-semibold text-gray-700 mb-3">Identification</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">ID Type</label>
                    <select name="id_type" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">Select ID Type</option>
                        @foreach(["Passport","Driver's License","SSS ID","PhilHealth ID","Voter's ID","National ID","TIN ID"] as $idType)
                            <option value="{{ $idType }}" {{ old('id_type', $client->id_type) === $idType ? 'selected' : '' }}>{{ $idType }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">ID Number</label>
                    <input type="text" name="id_number" value="{{ old('id_number', $client->id_number) }}"
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">ID Expiry</label>
                    <input type="date" name="id_expiry" value="{{ old('id_expiry', $client->id_expiry?->format('Y-m-d')) }}"
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                {{-- Pag-IBIG / Financial Info --}}
                <div class="md:col-span-2 border-t border-gray-100 pt-4">
                    <p class="text-sm font-semibold text-gray-700 mb-3">Pag-IBIG / Financial Info</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">HDMF MID Number</label>
                    <input type="text" name="hdmf_mid" value="{{ old('hdmf_mid', $client->hdmf_mid) }}"
                        placeholder="e.g. 1234-5678-9012"
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <p class="text-xs text-gray-400 mt-1">12-digit Pag-IBIG Membership ID</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Monthly Income (₱)</label>
                    <input type="number" name="monthly_income" value="{{ old('monthly_income', $client->monthly_income) }}"
                        placeholder="e.g. 25000" min="0" step="0.01"
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status <span class="text-red-500">*</span></label>
                    <select name="status" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="active"      {{ old('status', $client->status) === 'active'      ? 'selected' : '' }}>Active</option>
                        <option value="inactive"    {{ old('status', $client->status) === 'inactive'    ? 'selected' : '' }}>Inactive</option>
                        <option value="blacklisted" {{ old('status', $client->status) === 'blacklisted' ? 'selected' : '' }}>Blacklisted</option>
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Linked User Account</label>
                    <select name="user_id" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">None</option>
                        @foreach($users as $u)
                            <option value="{{ $u->id }}" {{ old('user_id', $client->user_id) == $u->id ? 'selected' : '' }}>{{ $u->name }} ({{ $u->email }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                    <textarea name="notes" rows="3"
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('notes', $client->notes) }}</textarea>
                </div>
            </div>

            <div class="mt-6 flex gap-3">
                <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded-lg text-sm hover:bg-indigo-700 transition font-medium">
                    Update Client
                </button>
                <a href="{{ route('clients.show', $client) }}" class="bg-gray-100 text-gray-600 px-6 py-2 rounded-lg text-sm hover:bg-gray-200 transition">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
