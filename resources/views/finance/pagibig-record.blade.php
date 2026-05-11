@extends('layouts.app')

@section('title', 'Record Pag-IBIG Payment - EstateFlow')
@section('page-title', 'Record Pag-IBIG Payment')
@section('page-subtitle', 'Record a payment received from Pag-IBIG (HDMF)')

@section('content')
<div class="max-w-2xl">

    {{-- Reservation Summary --}}
    <div class="bg-indigo-50 border border-indigo-100 rounded-xl p-5 mb-6">
        <p class="text-xs font-semibold text-indigo-500 uppercase tracking-wider mb-3">Reservation Summary</p>
        <div class="grid grid-cols-2 gap-4 text-sm">
            <div>
                <p class="text-gray-500 text-xs">Client</p>
                <p class="font-semibold text-gray-800">{{ $reservation->client->full_name ?? '—' }}</p>
            </div>
            <div>
                <p class="text-gray-500 text-xs">Property</p>
                <p class="font-semibold text-gray-800">{{ $reservation->property->title ?? '—' }}</p>
            </div>
            <div>
                <p class="text-gray-500 text-xs">Property Price</p>
                <p class="font-semibold text-gray-800">₱{{ number_format($reservation->property->price ?? 0, 2) }}</p>
            </div>
            <div>
                <p class="text-gray-500 text-xs">Total Paid So Far</p>
                <p class="font-semibold text-green-600">₱{{ number_format($totalPaid, 2) }}</p>
            </div>
            <div>
                <p class="text-gray-500 text-xs">Remaining Balance</p>
                <p class="font-bold text-red-600 text-lg">₱{{ number_format($remaining, 2) }}</p>
            </div>
            @if($reservation->pagibig_reference)
            <div>
                <p class="text-gray-500 text-xs">Pag-IBIG Reference</p>
                <p class="font-semibold text-indigo-600">{{ $reservation->pagibig_reference }}</p>
            </div>
            @endif
        </div>
    </div>

    {{-- Payment Form --}}
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="flex items-center gap-3 mb-6 pb-4 border-b border-gray-100">
            <div class="w-10 h-10 bg-red-100 rounded-xl flex items-center justify-center">
                <i class="fas fa-home text-red-600"></i>
            </div>
            <div>
                <p class="font-semibold text-gray-800">Pag-IBIG Check / Payment Form</p>
                <p class="text-xs text-gray-400">Fill in the details from the check or payment form received from Pag-IBIG</p>
            </div>
        </div>

        @if($errors->any())
            <div class="mb-4 bg-red-50 border border-red-200 rounded-lg p-4">
                <ul class="text-sm text-red-600 list-disc list-inside space-y-1">
                    @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('finance.pagibig.store', $reservation) }}" enctype="multipart/form-data">
            @csrf
            <div class="space-y-4">

                {{-- Check / Reference Number --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Check Number / Reference Number
                        <span class="text-gray-400 font-normal">(from the Pag-IBIG check or form)</span>
                    </label>
                    <input type="text" name="check_number" value="{{ old('check_number') }}"
                        placeholder="e.g. CHK-0012345 or HDMF-TRN-2024-XXXXX"
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                {{-- Amount --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Amount on Check / Form <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute left-3 top-2.5 text-gray-400 text-sm font-medium">₱</span>
                        <input type="number" name="amount" value="{{ old('amount', $remaining) }}"
                            step="0.01" min="1" max="{{ $remaining }}"
                            class="w-full pl-7 pr-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <p class="text-xs text-gray-400 mt-1">Remaining balance: ₱{{ number_format($remaining, 2) }}</p>
                </div>

                {{-- Payment Date --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Date on Check / Payment Date <span class="text-red-500">*</span>
                    </label>
                    <input type="date" name="payment_date" value="{{ old('payment_date', now()->format('Y-m-d')) }}"
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                {{-- Payment Type --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Payment Type <span class="text-red-500">*</span></label>
                    <select name="payment_type" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="partial"      {{ old('payment_type') === 'partial'      ? 'selected' : '' }}>Partial Payment (more payments expected)</option>
                        <option value="full_payment" {{ old('payment_type') === 'full_payment' ? 'selected' : '' }}>Full Payment (final payment)</option>
                    </select>
                </div>

                {{-- Description / Notes --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Notes <span class="text-gray-400 font-normal">(optional)</span></label>
                    <textarea name="description" rows="2"
                        placeholder="e.g. 3rd tranche from Pag-IBIG, received via check..."
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 resize-none">{{ old('description') }}</textarea>
                </div>

                {{-- Proof / Scan of Check --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Attach Check / Document Scan
                        <span class="text-gray-400 font-normal">(optional, max 5MB)</span>
                    </label>
                    <input type="file" name="proof_image" accept="image/*"
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <p class="text-xs text-gray-400 mt-1">You can attach a photo or scan of the check received from Pag-IBIG.</p>
                </div>

            </div>

            <div class="mt-6 flex gap-3">
                <button type="submit"
                    class="bg-red-600 text-white px-6 py-2 rounded-lg text-sm hover:bg-red-700 transition font-medium">
                    <i class="fas fa-save mr-1"></i> Record Pag-IBIG Payment
                </button>
                <a href="{{ route('finance.pagibig') }}"
                    class="bg-gray-100 text-gray-600 px-6 py-2 rounded-lg text-sm hover:bg-gray-200 transition">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
