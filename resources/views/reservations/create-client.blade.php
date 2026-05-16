@extends('layouts.client')
@section('title', 'Reserve a Property — EstateFlow')
@section('page-title', 'Reserve a Property')
@section('page-subtitle', 'Fill in the details below to submit your reservation request')
@section('content')

<div class="max-w-2xl">

    <a href="{{ route('client.reservations') }}" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-indigo-600 transition mb-6">
        <i class="fas fa-arrow-left"></i> Back to My Reservations
    </a>

    @if(!$myClientRecord)
        <div class="mb-6 bg-red-50 border border-red-200 rounded-xl p-4 flex items-start gap-3">
            <i class="fas fa-exclamation-circle text-red-500 mt-0.5"></i>
            <div>
                <p class="text-sm font-medium text-red-800">Your client profile is not set up yet.</p>
                <p class="text-xs text-red-600 mt-1">Please contact an administrator before making a reservation.</p>
            </div>
        </div>
    @endif

    {{-- Property Card --}}
    @if($selectedProperty)
    <div class="bg-white rounded-xl shadow-sm p-5 mb-6 flex items-center gap-4 border border-indigo-100">
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
        <span class="text-xs px-2.5 py-1 rounded-full font-medium bg-green-100 text-green-700 flex-shrink-0">Available</span>
    </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm p-6">
        <form method="POST" action="{{ route('reservations.store') }}">
            @csrf
            <input type="hidden" name="client_id" value="{{ $myClientRecord?->id }}">
            <input type="hidden" name="status" value="pending">
            @if($selectedProperty)
                <input type="hidden" name="property_id" value="{{ $selectedProperty->id }}">
            @endif

            <div class="space-y-6">

                {{-- Your Info --}}
                <div class="bg-indigo-50 border border-indigo-100 rounded-xl p-4">
                    <p class="text-xs font-semibold text-indigo-700 mb-2"><i class="fas fa-user mr-1"></i> Your Information</p>
                    <div class="grid grid-cols-2 gap-2 text-sm text-indigo-800">
                        <div><span class="text-indigo-400 text-xs">Name:</span> {{ $myClientRecord?->full_name ?? auth()->user()->name }}</div>
                        <div><span class="text-indigo-400 text-xs">Phone:</span> {{ $myClientRecord?->phone ?? '—' }}</div>
                        <div class="col-span-2"><span class="text-indigo-400 text-xs">Email:</span> {{ auth()->user()->email }}</div>
                    </div>
                </div>

                {{-- Property Details --}}
                <div>
                    <p class="text-sm font-semibold text-gray-700 mb-3"><i class="fas fa-building mr-1 text-indigo-400"></i> Property Details</p>
                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Block</label>
                            <input type="text" name="block" value="{{ old('block', $selectedProperty?->block) }}" readonly
                                class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm bg-gray-50 text-gray-700">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Lot</label>
                            <input type="text" name="lot" value="{{ old('lot', $selectedProperty?->lot) }}" readonly
                                class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm bg-gray-50 text-gray-700">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Property Type</label>
                            <input type="text" value="{{ $selectedProperty?->propertyType?->name ?? '—' }}" readonly
                                class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm bg-gray-50 text-gray-700">
                        </div>
                    </div>
                </div>

                {{-- Payment Scheme --}}
                <div>
                    <p class="text-sm font-semibold text-gray-700 mb-3"><i class="fas fa-credit-card mr-1 text-indigo-400"></i> Payment Scheme <span class="text-red-500">*</span></p>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="scheme-option cursor-pointer">
                            <input type="radio" name="payment_scheme" value="cash_bank" class="sr-only"
                                {{ old('payment_scheme', 'cash_bank') === 'cash_bank' ? 'checked' : '' }}>
                            <div class="scheme-card border-2 rounded-xl p-4 transition-all border-indigo-500 bg-indigo-50">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 bg-indigo-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                        <i class="fas fa-university text-indigo-600"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-gray-800">Cash / Bank Transfer</p>
                                        <p class="text-xs text-gray-400">Direct payment to Villa Rosalina</p>
                                    </div>
                                </div>
                            </div>
                        </label>
                        <label class="scheme-option cursor-pointer">
                            <input type="radio" name="payment_scheme" value="pagibig" class="sr-only"
                                {{ old('payment_scheme') === 'pagibig' ? 'checked' : '' }}>
                            <div class="scheme-card border-2 rounded-xl p-4 transition-all border-gray-200 bg-white">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 bg-red-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                        <i class="fas fa-home text-red-600"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-gray-800">Pag-IBIG</p>
                                        <p class="text-xs text-gray-400">Housing loan via Pag-IBIG Fund</p>
                                    </div>
                                </div>
                            </div>
                        </label>
                    </div>
                </div>

                {{-- Employment Type (Pag-IBIG only) --}}
                <div id="employment-section" class="{{ old('payment_scheme') === 'pagibig' ? '' : 'hidden' }}">
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Employment Type <span class="text-red-500">*</span>
                    </label>
                    <select name="employment_type" id="employment_type"
                        class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">Select employment type...</option>
                        @foreach(\App\Models\Reservation::EMPLOYMENT_TYPES as $value => $label)
                            <option value="{{ $value }}" {{ old('employment_type') === $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Co-Borrower Details --}}
                <div id="coborrower-section" class="hidden">
                    <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4">
                        <p class="text-sm font-semibold text-yellow-800 mb-3"><i class="fas fa-user-friends mr-1"></i> Co-Borrower Details</p>
                        <div class="space-y-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Full Name <span class="text-red-500">*</span></label>
                                <input type="text" name="coborrower_name" value="{{ old('coborrower_name') }}"
                                    placeholder="Co-borrower's full name"
                                    class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Relationship <span class="text-red-500">*</span></label>
                                    <input type="text" name="coborrower_relationship" value="{{ old('coborrower_relationship') }}"
                                        placeholder="e.g. Spouse, Parent"
                                        class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Contact Number <span class="text-red-500">*</span></label>
                                    <input type="text" name="coborrower_contact" value="{{ old('coborrower_contact') }}"
                                        placeholder="09XXXXXXXXX"
                                        class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Preferred Agent --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Preferred Agent <span class="text-red-500">*</span></label>
                    <select name="agent_id" required class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        @foreach($agents as $agent)
                            <option value="{{ $agent->id }}" {{ old('agent_id') == $agent->id ? 'selected' : '' }}>
                                {{ $agent->agent_code }}
                            </option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-400 mt-1"><i class="fas fa-shield-alt mr-1"></i>Agent codes are used to ensure neutrality in agent selection.</p>
                </div>

                {{-- Appointment Date --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Preferred Appointment Date <span class="text-red-500">*</span></label>
                    <input type="date" name="reservation_date"
                        value="{{ old('reservation_date', now()->addDays(3)->format('Y-m-d')) }}"
                        min="{{ now()->addDay()->format('Y-m-d') }}"
                        class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <p class="text-xs text-gray-400 mt-1">Your preferred date for the face-to-face property viewing.</p>
                </div>

                {{-- Notes --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Message to Agent <span class="text-gray-400 font-normal">(optional)</span></label>
                    <textarea name="notes" rows="3"
                        placeholder="Any questions or special requests for the agent..."
                        class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 resize-none">{{ old('notes') }}</textarea>
                </div>

                {{-- What happens next --}}
                <div class="bg-blue-50 border border-blue-100 rounded-xl p-4 text-xs text-blue-700 space-y-1">
                    <p class="font-semibold mb-2"><i class="fas fa-info-circle mr-1"></i> What happens next?</p>
                    <p>1. Your reservation request will be submitted as <strong>Pending</strong></p>
                    <p>2. You'll receive a confirmation email with your appointment details</p>
                    <p>3. Reminders will be sent 3, 2, and 1 day before your appointment</p>
                    <p>4. After the viewing, a document checklist will be generated based on your payment scheme</p>
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="submit" {{ !$myClientRecord ? 'disabled' : '' }}
                        class="flex-1 bg-indigo-600 text-white py-3 rounded-xl font-semibold hover:bg-indigo-700 transition text-sm disabled:opacity-50 disabled:cursor-not-allowed">
                        <i class="fas fa-calendar-check mr-2"></i>Submit Reservation Request
                    </button>
                    <a href="{{ route('client.reservations') }}"
                        class="px-6 py-3 bg-gray-100 text-gray-600 rounded-xl text-sm hover:bg-gray-200 transition font-medium">
                        Cancel
                    </a>
                </div>

            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
const COBORROWER_TYPES = @json(\App\Models\Reservation::COBORROWER_TYPES);

// Payment scheme toggle
document.querySelectorAll('input[name="payment_scheme"]').forEach(radio => {
    radio.addEventListener('change', () => updateScheme());
});

function updateScheme() {
    const scheme = document.querySelector('input[name="payment_scheme"]:checked')?.value;
    const empSection = document.getElementById('employment-section');
    const empSelect  = document.getElementById('employment_type');

    // Update card styles
    document.querySelectorAll('.scheme-option').forEach(opt => {
        const card  = opt.querySelector('.scheme-card');
        const radio = opt.querySelector('input[type="radio"]');
        if (radio.checked) {
            card.classList.add('border-indigo-500', 'bg-indigo-50');
            card.classList.remove('border-gray-200', 'bg-white');
        } else {
            card.classList.remove('border-indigo-500', 'bg-indigo-50');
            card.classList.add('border-gray-200', 'bg-white');
        }
    });

    if (scheme === 'pagibig') {
        empSection.classList.remove('hidden');
    } else {
        empSection.classList.add('hidden');
        empSelect.value = '';
        document.getElementById('coborrower-section').classList.add('hidden');
    }
}

// Employment type toggle for co-borrower
document.getElementById('employment_type').addEventListener('change', function () {
    const cobSection = document.getElementById('coborrower-section');
    if (COBORROWER_TYPES.includes(this.value)) {
        cobSection.classList.remove('hidden');
    } else {
        cobSection.classList.add('hidden');
    }
});

// Init on page load (handles old() repopulation after validation fail)
updateScheme();
const empVal = document.getElementById('employment_type').value;
if (COBORROWER_TYPES.includes(empVal)) {
    document.getElementById('coborrower-section').classList.remove('hidden');
}
</script>
@endpush
