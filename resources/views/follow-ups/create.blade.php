@extends('layouts.app')

@section('title', 'Schedule Follow-Up — EstateFlow')
@section('page-title', 'Schedule Follow-Up')
@section('page-subtitle', 'Create a new client follow-up')

@section('content')
<div class="max-w-2xl">
    <div class="bg-white rounded-xl shadow-sm p-6">

        @if($errors->any())
            <div class="mb-4 bg-red-50 border border-red-200 rounded-xl p-4">
                <ul class="text-sm text-red-600 list-disc list-inside space-y-1">
                    @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('follow-ups.store') }}" class="space-y-4">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Client <span class="text-red-500">*</span></label>
                    <select name="client_id" id="client_id" onchange="filterReservations(this)"
                        class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">Select Client</option>
                        @foreach($clients as $client)
                            <option value="{{ $client->id }}"
                                {{ old('client_id', $selectedClient?->id) == $client->id ? 'selected' : '' }}>
                                {{ $client->full_name }} — {{ $client->phone ?? $client->email }}
                            </option>
                        @endforeach
                    </select>
                </div>

                @if(auth()->user()->isAdmin())
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Assign Agent</label>
                    <select name="agent_id" class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">No Agent</option>
                        @foreach($agents as $agent)
                            <option value="{{ $agent->id }}" {{ old('agent_id') == $agent->id ? 'selected' : '' }}>
                                {{ $agent->full_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @endif

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Linked Reservation</label>
                    <select name="reservation_id" id="reservation_id"
                        class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">None</option>
                        @foreach($reservations as $res)
                            <option value="{{ $res->id }}" data-client="{{ $res->client_id }}"
                                {{ old('reservation_id') == $res->id ? 'selected' : '' }}>
                                #{{ $res->id }} — {{ $res->property->title ?? '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Follow-Up Type <span class="text-red-500">*</span></label>
                    <select name="type" class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        @foreach(['call' => '📞 Call', 'email' => '✉️ Email', 'site_visit' => '🏠 Site Visit', 'meeting' => '👥 Meeting'] as $val => $label)
                            <option value="{{ $val }}" {{ old('type') === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date <span class="text-red-500">*</span></label>
                    <input type="date" name="follow_up_date" value="{{ old('follow_up_date', now()->addDay()->format('Y-m-d')) }}"
                        class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Time <span class="text-gray-400 font-normal">(optional)</span></label>
                    <input type="time" name="follow_up_time" value="{{ old('follow_up_time') }}"
                        class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Notes <span class="text-gray-400 font-normal">(optional)</span></label>
                    <textarea name="notes" rows="3" placeholder="What to discuss, reminders, etc..."
                        class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 resize-none">{{ old('notes') }}</textarea>
                </div>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit" class="bg-indigo-600 text-white px-6 py-2.5 rounded-xl text-sm font-semibold hover:bg-indigo-700 transition">
                    <i class="fas fa-calendar-plus mr-2"></i>Schedule Follow-Up
                </button>
                <a href="{{ route('follow-ups.index') }}" class="bg-gray-100 text-gray-600 px-6 py-2.5 rounded-xl text-sm hover:bg-gray-200 transition">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
function filterReservations(select) {
    const clientId = select.value;
    const resSelect = document.getElementById('reservation_id');
    Array.from(resSelect.options).forEach(opt => {
        if (!opt.value) return;
        opt.style.display = (!clientId || opt.dataset.client === clientId) ? '' : 'none';
    });
    resSelect.value = '';
}
</script>
@endsection
