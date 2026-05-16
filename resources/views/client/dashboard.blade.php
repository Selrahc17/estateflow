@extends('layouts.client')

@section('title', 'My Dashboard — EstateFlow')
@section('page-title', 'My Dashboard')
@section('page-subtitle', 'Welcome back, ' . auth()->user()->name)

@section('content')

{{-- Stats --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-xl shadow-sm p-4 flex items-center gap-3">
        <div class="w-10 h-10 bg-indigo-100 rounded-xl flex items-center justify-center flex-shrink-0">
            <i class="fas fa-calendar-check text-indigo-600"></i>
        </div>
        <div>
            <p class="text-xs text-gray-500">Total</p>
            <p class="text-xl font-bold text-gray-800">{{ $totalCount }}</p>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-4 flex items-center gap-3">
        <div class="w-10 h-10 bg-yellow-100 rounded-xl flex items-center justify-center flex-shrink-0">
            <i class="fas fa-clock text-yellow-600"></i>
        </div>
        <div>
            <p class="text-xs text-gray-500">Pending</p>
            <p class="text-xl font-bold text-yellow-600">{{ $pendingCount }}</p>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-4 flex items-center gap-3">
        <div class="w-10 h-10 bg-green-100 rounded-xl flex items-center justify-center flex-shrink-0">
            <i class="fas fa-check-circle text-green-600"></i>
        </div>
        <div>
            <p class="text-xs text-gray-500">Confirmed</p>
            <p class="text-xl font-bold text-green-600">{{ $confirmedCount }}</p>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-4 flex items-center gap-3">
        <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center flex-shrink-0">
            <i class="fas fa-coins text-blue-600"></i>
        </div>
        <div>
            <p class="text-xs text-gray-500">Total Paid</p>
            <p class="text-xl font-bold text-blue-600">₱{{ number_format($totalPaid, 0) }}</p>
        </div>
    </div>
</div>

{{-- Filter Tabs --}}
<div class="flex items-center gap-2 mb-4 flex-wrap">
    @foreach(['' => 'All', 'pending' => 'Pending', 'confirmed' => 'Confirmed', 'completed' => 'Completed', 'cancelled' => 'Cancelled'] as $val => $label)
        <a href="{{ route('client.dashboard', $val ? ['status' => $val] : []) }}"
            class="px-4 py-1.5 rounded-full text-sm font-medium transition
            {{ request('status', '') === $val ? 'bg-indigo-600 text-white' : 'bg-white text-gray-600 hover:bg-gray-100 shadow-sm' }}">
            {{ $label }}
        </a>
    @endforeach
</div>

{{-- Reservations --}}
@forelse($reservations as $res)
<div class="bg-white rounded-xl shadow-sm mb-4 overflow-hidden">
    <div class="p-5">

        {{-- Header --}}
        <div class="flex items-start justify-between gap-4 mb-4">
            <div class="flex items-start gap-3 flex-1 min-w-0">
                <div class="w-14 h-14 bg-indigo-50 rounded-xl flex items-center justify-center flex-shrink-0 overflow-hidden">
                    @if($res->property?->image_main)
                        <img src="{{ asset($res->property->image_main) }}" class="w-14 h-14 object-cover">
                    @else
                        <i class="fas fa-building text-indigo-300 text-xl"></i>
                    @endif
                </div>
                <div class="min-w-0">
                    <p class="font-semibold text-gray-800 truncate">{{ $res->property->title ?? '—' }}</p>
                    <p class="text-xs text-gray-400 mt-0.5"><i class="fas fa-map-marker-alt mr-1"></i>{{ $res->property->location ?? 'Location not set' }}</p>
                    <p class="text-sm font-bold text-indigo-600 mt-1">₱{{ number_format($res->property->price ?? 0, 0) }}</p>
                </div>
            </div>
            <div class="flex flex-col items-end gap-1 flex-shrink-0">
                <span class="text-xs px-2.5 py-1 rounded-full font-medium
                    {{ $res->status === 'confirmed' ? 'bg-green-100 text-green-700'   : '' }}
                    {{ $res->status === 'pending'   ? 'bg-yellow-100 text-yellow-700' : '' }}
                    {{ $res->status === 'cancelled' ? 'bg-red-100 text-red-700'       : '' }}
                    {{ $res->status === 'completed' ? 'bg-blue-100 text-blue-700'     : '' }}
                    {{ $res->status === 'expired'   ? 'bg-gray-100 text-gray-600'     : '' }}">
                    {{ ucfirst($res->status) }}
                </span>
                <span class="text-xs text-gray-400">{{ $res->reservation_date->format('M d, Y') }}</span>
            </div>
        </div>

        {{-- Details --}}
        <div class="grid grid-cols-2 md:grid-cols-3 gap-3 text-xs mb-4">
            <div class="bg-gray-50 rounded-lg p-2.5">
                <p class="text-gray-400 mb-0.5">Agent</p>
                <p class="font-medium text-gray-700">{{ $res->agent->agent_code ?? 'Not assigned' }}</p>
            </div>
            <div class="bg-gray-50 rounded-lg p-2.5">
                <p class="text-gray-400 mb-0.5">Reservation Fee</p>
                <p class="font-medium text-gray-700">₱{{ number_format($res->reservation_fee, 0) }}</p>
            </div>
            <div class="bg-gray-50 rounded-lg p-2.5">
                <p class="text-gray-400 mb-0.5">Total Paid</p>
                <p class="font-medium {{ $res->payments->where('status','completed')->sum('amount') > 0 ? 'text-green-600' : 'text-gray-400' }}">
                    ₱{{ number_format($res->payments->where('status','completed')->sum('amount'), 0) }}
                </p>
            </div>
        </div>

        {{-- Viewing Status Tracker --}}
        @if(!in_array($res->status, ['cancelled', 'expired']))
        <div class="mb-4">
            @php
                $vsteps = ['pending' => 'Appointment Set', 'viewed' => 'Viewing Done', 'payment_uploaded' => 'Proof Uploaded', 'verified' => 'Payment Verified'];
                $vkeys  = array_keys($vsteps);
                $vidx   = array_search($res->viewing_status ?? 'pending', $vkeys);
            @endphp
            <div class="flex items-center">
                @foreach($vsteps as $key => $label)
                    @php $idx = array_search($key, $vkeys); $done = $idx <= $vidx; $active = $idx === $vidx; @endphp
                    <div class="flex items-center flex-1">
                        <div class="flex flex-col items-center">
                            <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold
                                {{ $done ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-400' }}
                                {{ $active ? 'ring-2 ring-indigo-300' : '' }}">
                                @if($done && !$active)<i class="fas fa-check text-xs"></i>@else{{ $idx + 1 }}@endif
                            </div>
                            <p class="text-xs mt-1 text-center w-16 leading-tight {{ $done ? 'text-indigo-600 font-medium' : 'text-gray-400' }}">{{ $label }}</p>
                        </div>
                        @if(!$loop->last)
                            <div class="flex-1 h-0.5 mx-1 mb-4 {{ $idx < $vidx ? 'bg-indigo-600' : 'bg-gray-200' }}"></div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Action Area --}}
        @if($res->viewing_status === 'viewed')
        <div class="bg-green-50 border border-green-200 rounded-xl p-4 mb-3">
            <p class="text-sm font-semibold text-green-800 mb-1"><i class="fas fa-upload mr-1"></i> Upload Proof of Payment</p>
            <p class="text-xs text-green-700 mb-3">Your viewing is complete. Upload your reservation fee proof to proceed.</p>
            <form method="POST" action="{{ route('reservations.upload-proof', $res) }}" enctype="multipart/form-data">
                @csrf
                <div class="flex items-center gap-2">
                    <input type="file" name="proof_of_payment" accept=".jpg,.jpeg,.png,.pdf" required
                        class="flex-1 text-xs text-gray-600 border border-green-200 rounded-lg px-3 py-2 bg-white">
                    <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded-lg text-xs hover:bg-green-700 transition font-medium whitespace-nowrap">
                        <i class="fas fa-upload mr-1"></i> Upload
                    </button>
                </div>
                <p class="text-xs text-gray-400 mt-1">JPG, PNG, PDF — Max 5MB</p>
            </form>
        </div>
        @elseif($res->viewing_status === 'payment_uploaded')
        <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-3 mb-3 flex items-center gap-2">
            <i class="fas fa-clock text-yellow-500"></i>
            <p class="text-sm text-yellow-700">Proof uploaded. Waiting for admin verification.</p>
        </div>
        @elseif($res->viewing_status === 'verified')
        <div class="bg-green-50 border border-green-200 rounded-xl p-3 mb-3 flex items-center gap-2">
            <i class="fas fa-check-circle text-green-500"></i>
            <p class="text-sm text-green-700 font-medium">Payment verified! Your reservation is confirmed.</p>
        </div>
        @elseif($res->viewing_status === 'pending')
        <div class="bg-indigo-50 border border-indigo-100 rounded-xl p-3 mb-3 flex items-center gap-2">
            <i class="fas fa-info-circle text-indigo-400"></i>
            <p class="text-xs text-indigo-700">Proof of payment upload will be available after your viewing is completed.</p>
        </div>
        @endif
        @endif

        {{-- Grace Period Notice --}}
        @if($res->status === 'cancelled' && $res->cancelled_at && !$res->data_wiped_at)
        @php $daysLeft = max(0, now()->diffInDays($res->gracePeriodEndsAt(), false)); @endphp
        <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-3 mb-3 flex items-start gap-2">
            <i class="fas fa-clock text-yellow-500 mt-0.5"></i>
            <p class="text-xs text-yellow-700">
                @if($daysLeft > 0)
                    Data will be deleted in <strong>{{ $daysLeft }} day(s)</strong> ({{ $res->gracePeriodEndsAt()->format('M d, Y') }}).
                @else
                    Data scheduled for deletion. Contact us to reconsider.
                @endif
            </p>
        </div>
        @endif

        {{-- Pag-IBIG --}}
        @if($res->status === 'confirmed' && ($res->pagibig_status ?? 'not_applied') !== 'not_applied')
        <div class="bg-red-50 border border-red-100 rounded-xl p-3 mb-3">
            <p class="text-xs font-semibold text-red-700 mb-1"><i class="fas fa-home mr-1"></i> Pag-IBIG: {{ \App\Models\Reservation::PAGIBIG_STATUSES[$res->pagibig_status] }}</p>
            @if($res->pagibig_reference)
                <p class="text-xs text-red-600">Ref: {{ $res->pagibig_reference }}</p>
            @endif
        </div>
        @endif

        {{-- Footer Actions --}}
        <div class="flex items-center gap-2 flex-wrap pt-3 border-t border-gray-50">
            @if($res->status === 'confirmed' && ($res->pagibig_status ?? 'not_applied') === 'not_applied')
            <form method="POST" action="{{ route('client.pagibig.request', $res) }}">
                @csrf
                <button type="submit" onclick="return confirm('Apply for Pag-IBIG loan for this property?')"
                    class="text-xs bg-red-600 text-white px-3 py-1.5 rounded-lg hover:bg-red-700 transition font-medium">
                    <i class="fas fa-home mr-1"></i> Apply Pag-IBIG
                </button>
            </form>
            @endif

            @if(!in_array($res->status, ['cancelled', 'expired', 'completed']))
            @php $viewing = $res->siteViewingSchedules->whereIn('status', ['pending','confirmed'])->first(); @endphp
            @if(!$viewing)
            <a href="{{ route('site-viewing.create', $res) }}"
                class="text-xs bg-indigo-50 text-indigo-600 px-3 py-1.5 rounded-lg hover:bg-indigo-100 transition font-medium">
                <i class="fas fa-map-marker-alt mr-1"></i> Schedule Viewing
            </a>
            @else
            <span class="text-xs bg-green-50 text-green-700 px-3 py-1.5 rounded-lg font-medium">
                <i class="fas fa-calendar-check mr-1"></i> Viewing: {{ $viewing->preferred_date->format('M d, Y') }}
            </span>
            @endif
            @endif

            @if($res->payments->count())
            <button onclick="togglePayments({{ $res->id }})"
                class="text-xs bg-gray-50 text-gray-600 px-3 py-1.5 rounded-lg hover:bg-gray-100 transition font-medium">
                <i class="fas fa-receipt mr-1"></i> {{ $res->payments->count() }} Payment(s)
                <i class="fas fa-chevron-down text-xs ml-1" id="chevron-{{ $res->id }}"></i>
            </button>
            @endif

            @if($res->notes)
            <span class="text-xs text-gray-400 italic truncate max-w-xs">
                <i class="fas fa-comment-alt mr-1"></i>{{ Str::limit($res->notes, 40) }}
            </span>
            @endif
        </div>
    </div>

    {{-- Payments Expandable --}}
    @if($res->payments->count())
    <div id="payments-{{ $res->id }}" class="hidden border-t border-gray-100 bg-gray-50 px-5 py-4">
        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Payment History</p>
        <div class="space-y-2">
            @foreach($res->payments as $payment)
            <div class="bg-white rounded-lg p-3 flex items-center justify-between shadow-sm">
                <div>
                    <p class="text-sm font-medium text-gray-800">{{ ucfirst(str_replace('_', ' ', $payment->payment_type)) }}</p>
                    <p class="text-xs text-gray-400">{{ $payment->payment_date->format('M d, Y') }} · {{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}</p>
                </div>
                <div class="text-right">
                    <p class="text-sm font-bold text-gray-800">₱{{ number_format($payment->amount, 2) }}</p>
                    <span class="text-xs px-2 py-0.5 rounded-full {{ $payment->status === 'completed' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                        {{ ucfirst($payment->status) }}
                    </span>
                </div>
            </div>
            @endforeach
        </div>
        @php
            $paid      = $res->payments->where('status','completed')->sum('amount');
            $remaining = ($res->property->price ?? 0) - $paid;
        @endphp
        <div class="mt-3 pt-3 border-t border-gray-200 flex justify-between text-sm">
            <span class="text-gray-500">Remaining Balance</span>
            <span class="font-bold {{ $remaining <= 0 ? 'text-green-600' : 'text-red-600' }}">
                {{ $remaining <= 0 ? 'Fully Paid ✓' : '₱' . number_format($remaining, 2) }}
            </span>
        </div>
    </div>
    @endif

</div>
@empty
<div class="bg-white rounded-xl shadow-sm p-16 text-center text-gray-400">
    <i class="fas fa-calendar-check text-5xl mb-4 block text-gray-200"></i>
    @if(request('status'))
        <p class="text-sm">No {{ request('status') }} reservations found.</p>
        <a href="{{ route('client.dashboard') }}" class="mt-3 inline-block text-indigo-600 hover:underline text-sm">View all</a>
    @else
        <p class="text-sm mb-4">You have no reservations yet.</p>
        <a href="{{ route('home.browse') }}" class="bg-indigo-600 text-white px-6 py-2.5 rounded-xl text-sm hover:bg-indigo-700 transition font-medium">
            <i class="fas fa-search mr-2"></i>Browse Properties
        </a>
    @endif
</div>
@endforelse

@if($reservations->hasPages())
<div class="mt-4">{{ $reservations->links() }}</div>
@endif

@endsection

@push('scripts')
<script>
function togglePayments(id) {
    const panel   = document.getElementById('payments-' + id);
    const chevron = document.getElementById('chevron-' + id);
    panel.classList.toggle('hidden');
    chevron.style.transform = panel.classList.contains('hidden') ? '' : 'rotate(180deg)';
}
</script>
@endpush
