<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Reservations — EstateFlow</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50 font-sans">

@include('partials.client-nav')

<div class="max-w-7xl mx-auto px-6 py-8">

    <h1 class="text-2xl font-bold text-gray-900 mb-1">My Reservations</h1>
    <p class="text-gray-500 text-sm mb-8">All your property reservations and payment history</p>

    @if(session('success'))
        <div class="mb-6 bg-green-50 border border-green-200 text-green-700 text-sm px-5 py-4 rounded-xl flex items-center gap-3">
            <i class="fas fa-check-circle text-green-500"></i> {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-6 bg-red-50 border border-red-200 text-red-700 text-sm px-5 py-4 rounded-xl flex items-center gap-3">
            <i class="fas fa-exclamation-circle text-red-500"></i> {{ session('error') }}
        </div>
    @endif

    @if(!$clientRecord)
    <div class="mb-6 bg-yellow-50 border border-yellow-200 rounded-xl p-4 flex items-start gap-3">
        <i class="fas fa-exclamation-triangle text-yellow-500 mt-0.5"></i>
        <div>
            <p class="text-sm font-medium text-yellow-800">Your account is not linked to a client profile yet.</p>
            <p class="text-xs text-yellow-600 mt-1">Please contact an administrator.</p>
        </div>
    </div>
    @endif

    {{-- Stats --}}
    <div class="grid grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm p-4 text-center">
            <p class="text-2xl font-bold text-gray-800">{{ $totalCount }}</p>
            <p class="text-xs text-gray-500 mt-1">Total</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4 text-center">
            <p class="text-2xl font-bold text-yellow-600">{{ $pendingCount }}</p>
            <p class="text-xs text-gray-500 mt-1">Pending</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm p-4 text-center">
            <p class="text-2xl font-bold text-green-600">{{ $confirmedCount }}</p>
            <p class="text-xs text-gray-500 mt-1">Confirmed</p>
        </div>
    </div>

    {{-- Filter Tabs --}}
    <div class="flex items-center gap-2 mb-6 flex-wrap">
        @foreach(['' => 'All', 'pending' => 'Pending', 'confirmed' => 'Confirmed', 'completed' => 'Completed', 'cancelled' => 'Cancelled', 'expired' => 'Expired'] as $val => $label)
            <a href="{{ route('client.reservations', $val ? ['status' => $val] : []) }}"
                class="px-4 py-2 rounded-xl text-sm font-medium transition {{ request('status', '') === $val ? 'bg-indigo-600 text-white' : 'bg-white text-gray-600 hover:bg-gray-50 shadow-sm' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    @forelse($reservations as $res)
    <div class="bg-white rounded-xl shadow-sm mb-4 overflow-hidden">

        <div class="p-6">

            {{-- Property Header --}}
            <div class="flex items-start justify-between gap-4">
                <div class="flex items-start gap-4 flex-1 min-w-0">
                    <div class="w-14 h-14 bg-indigo-50 rounded-xl flex items-center justify-center flex-shrink-0">
                        @if($res->property?->image_main)
                            <img src="{{ asset($res->property->image_main) }}" class="w-14 h-14 object-cover rounded-xl">
                        @else
                            <i class="fas fa-building text-indigo-300 text-xl"></i>
                        @endif
                    </div>
                    <div class="min-w-0">
                        <p class="font-semibold text-gray-800">{{ $res->property->title ?? '—' }}</p>
                        <p class="text-xs text-gray-400 mt-0.5">
                            <i class="fas fa-map-marker-alt mr-1"></i>{{ $res->property->location ?? 'Location not set' }}
                        </p>
                        <p class="text-sm font-bold text-indigo-600 mt-1">₱{{ number_format($res->property->price ?? 0, 0) }}</p>
                    </div>
                </div>
                <span class="text-xs px-3 py-1.5 rounded-full font-medium flex-shrink-0
                    {{ $res->status === 'confirmed' ? 'bg-green-100 text-green-700'   : '' }}
                    {{ $res->status === 'pending'   ? 'bg-yellow-100 text-yellow-700' : '' }}
                    {{ $res->status === 'cancelled' ? 'bg-red-100 text-red-700'       : '' }}
                    {{ $res->status === 'completed' ? 'bg-blue-100 text-blue-700'     : '' }}
                    {{ $res->status === 'expired'   ? 'bg-gray-100 text-gray-600'     : '' }}">
                    {{ ucfirst($res->status) }}
                </span>
            </div>

            {{-- Details Row --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-4 pt-4 border-t border-gray-50">
                <div>
                    <p class="text-xs text-gray-400">Reservation Date</p>
                    <p class="text-sm font-medium text-gray-800 mt-0.5">{{ $res->reservation_date->format('M d, Y') }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400">Agent</p>
                    <p class="text-sm font-medium text-gray-800 mt-0.5">{{ $res->agent->full_name ?? 'Not assigned' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400">Reservation Fee</p>
                    <p class="text-sm font-medium text-gray-800 mt-0.5">₱{{ number_format($res->reservation_fee, 0) }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400">Total Paid</p>
                    <p class="text-sm font-bold {{ $res->payments->where('status','completed')->sum('amount') > 0 ? 'text-green-600' : 'text-gray-400' }} mt-0.5">
                        ₱{{ number_format($res->payments->where('status','completed')->sum('amount'), 0) }}
                    </p>
                </div>
            </div>

            {{-- Pag-IBIG Section --}}
            @if($res->status === 'confirmed')
            <div class="mt-4 pt-4 border-t border-gray-50">
                @if(($res->pagibig_status ?? 'not_applied') === 'not_applied')
                    <form method="POST" action="{{ route('client.pagibig.request', $res) }}">
                        @csrf
                        <button type="submit"
                            onclick="return confirm('Submit a Pag-IBIG loan application for this property?')"
                            class="flex items-center gap-2 text-sm bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition font-medium">
                            <i class="fas fa-home"></i> Apply for Pag-IBIG Loan
                        </button>
                        <p class="text-xs text-gray-400 mt-1">Pag-IBIG will pay Villa Rosalina directly on your behalf.</p>
                    </form>
                @else
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">
                        <i class="fas fa-home mr-1 text-red-400"></i> Pag-IBIG Loan Status
                    </p>
                    @php
                        $pagibigSteps = ['applied' => 'Applied', 'verified' => 'Verified', 'approved' => 'Approved', 'released' => 'Released'];
                        $pagibigKeys  = array_keys($pagibigSteps);
                        $currentIdx   = array_search($res->pagibig_status, $pagibigKeys);
                    @endphp
                    <div class="flex items-center gap-1 mb-2">
                        @foreach($pagibigSteps as $key => $label)
                            @php $idx = array_search($key, $pagibigKeys); $done = $idx <= $currentIdx; @endphp
                            <div class="flex items-center gap-1">
                                <div class="flex flex-col items-center">
                                    <div class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold {{ $done ? 'bg-red-600 text-white' : 'bg-gray-100 text-gray-400' }}">
                                        @if($done)<i class="fas fa-check text-xs"></i>@else{{ $idx + 1 }}@endif
                                    </div>
                                    <p class="text-xs mt-0.5 {{ $done ? 'text-red-600 font-medium' : 'text-gray-400' }}">{{ $label }}</p>
                                </div>
                                @if(!$loop->last)
                                    <div class="w-6 h-0.5 mb-3 {{ $idx < $currentIdx ? 'bg-red-600' : 'bg-gray-200' }}"></div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                    @if($res->pagibig_reference)
                        <p class="text-xs text-gray-500"><span class="font-medium">Reference:</span> {{ $res->pagibig_reference }}</p>
                    @endif
                    @if($res->pagibig_status === 'verified')
                        <p class="text-xs text-yellow-600 font-medium mt-1"><i class="fas fa-check-circle mr-1"></i>Your Pag-IBIG membership has been verified. Waiting for Pag-IBIG approval.</p>
                    @elseif($res->pagibig_status === 'approved')
                        <p class="text-xs text-green-600 font-medium mt-1"><i class="fas fa-check-circle mr-1"></i>Your Pag-IBIG loan has been approved!</p>
                    @elseif($res->pagibig_status === 'released')
                        <p class="text-xs text-indigo-600 font-medium mt-1"><i class="fas fa-check-circle mr-1"></i>Pag-IBIG has released the funds to Villa Rosalina.</p>
                    @endif
                @endif
            </div>
            @endif

            {{-- Payments Toggle --}}
            @if($res->payments->count())
            <button onclick="togglePayments({{ $res->id }})"
                class="mt-4 flex items-center gap-2 text-sm text-indigo-600 hover:text-indigo-800 transition font-medium">
                <i class="fas fa-receipt"></i>
                View {{ $res->payments->count() }} Payment(s)
                <i class="fas fa-chevron-down text-xs transition-transform" id="chevron-{{ $res->id }}"></i>
            </button>
            @else
            <p class="mt-4 text-xs text-gray-400"><i class="fas fa-info-circle mr-1"></i>No payments recorded yet.</p>
            @endif

            @if($res->notes)
            <p class="mt-3 text-xs text-gray-500 bg-gray-50 rounded-lg px-3 py-2">
                <i class="fas fa-comment-alt mr-1"></i>{{ $res->notes }}
            </p>
            @endif
        </div>

        {{-- Payments Expandable --}}
        @if($res->payments->count())
        <div id="payments-{{ $res->id }}" class="hidden border-t border-gray-100 bg-gray-50">
            <div class="px-6 py-4">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Payment History</p>
                <div class="space-y-3">
                    @foreach($res->payments as $payment)
                    <div class="bg-white rounded-xl p-4 flex items-center justify-between shadow-sm">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-full flex items-center justify-center
                                {{ $payment->payment_method === 'gcash'         ? 'bg-blue-100'   : '' }}
                                {{ $payment->payment_method === 'cash'          ? 'bg-green-100'  : '' }}
                                {{ $payment->payment_method === 'bank_transfer' ? 'bg-indigo-100' : '' }}
                                {{ $payment->payment_method === 'credit_card'   ? 'bg-purple-100' : '' }}
                                {{ $payment->payment_method === 'pagibig'       ? 'bg-red-100'    : '' }}
                                {{ $payment->payment_method === 'check'         ? 'bg-gray-100'   : '' }}">
                                @if($payment->payment_method === 'gcash')
                                    <i class="fas fa-mobile-alt text-blue-500 text-sm"></i>
                                @elseif($payment->payment_method === 'cash')
                                    <i class="fas fa-money-bill text-green-500 text-sm"></i>
                                @elseif($payment->payment_method === 'bank_transfer')
                                    <i class="fas fa-university text-indigo-500 text-sm"></i>
                                @elseif($payment->payment_method === 'credit_card')
                                    <i class="fas fa-credit-card text-purple-500 text-sm"></i>
                                @elseif($payment->payment_method === 'pagibig')
                                    <i class="fas fa-home text-red-500 text-sm"></i>
                                @else
                                    <i class="fas fa-file-alt text-gray-400 text-sm"></i>
                                @endif
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-800">
                                    {{ ucfirst(str_replace('_', ' ', $payment->payment_type)) }}
                                </p>
                                <p class="text-xs text-gray-400">
                                    {{ $payment->payment_date->format('M d, Y') }}
                                    · {{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}
                                    @if($payment->reference_number) · Ref: {{ $payment->reference_number }} @endif
                                </p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-bold text-gray-800">₱{{ number_format($payment->amount, 2) }}</p>
                            <span class="text-xs px-2 py-0.5 rounded-full
                                {{ $payment->status === 'completed' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                                {{ ucfirst($payment->status) }}
                            </span>
                        </div>
                    </div>
                    @endforeach
                </div>

                <div class="mt-4 pt-3 border-t border-gray-200 flex items-center justify-between">
                    <p class="text-sm text-gray-500">Total Paid (Completed)</p>
                    <p class="text-sm font-bold text-green-600">
                        ₱{{ number_format($res->payments->where('status','completed')->sum('amount'), 2) }}
                    </p>
                </div>
                @if($res->property)
                <div class="flex items-center justify-between mt-1">
                    <p class="text-sm text-gray-500">Property Price</p>
                    <p class="text-sm font-bold text-gray-800">₱{{ number_format($res->property->price, 2) }}</p>
                </div>
                @php $remaining = ($res->property->price ?? 0) - $res->payments->where('status','completed')->sum('amount'); @endphp
                @if($remaining > 0)
                <div class="flex items-center justify-between mt-1">
                    <p class="text-sm text-gray-500">Remaining Balance</p>
                    <p class="text-sm font-bold text-red-600">₱{{ number_format($remaining, 2) }}</p>
                </div>
                @else
                <div class="flex items-center justify-between mt-1">
                    <p class="text-sm text-gray-500">Balance</p>
                    <p class="text-sm font-bold text-green-600">Fully Paid ✓</p>
                </div>
                @endif
                @endif
            </div>
        </div>
        @endif

    </div>
    @empty
    <div class="bg-white rounded-xl shadow-sm p-16 text-center text-gray-400">
        <i class="fas fa-calendar-check text-4xl mb-3 block text-gray-200"></i>
        @if(request('status'))
            <p>No {{ request('status') }} reservations found.</p>
            <a href="{{ route('client.reservations') }}" class="mt-4 inline-block text-indigo-600 hover:underline text-sm">View all reservations</a>
        @else
            <p>You have no reservations yet.</p>
            <a href="{{ route('home.browse') }}" class="mt-4 inline-block bg-indigo-600 text-white px-6 py-2 rounded-lg text-sm hover:bg-indigo-700 transition">
                Browse Properties
            </a>
        @endif
    </div>
    @endforelse

    @if(method_exists($reservations, 'hasPages') && $reservations->hasPages())
        <div class="mt-6 flex items-center justify-between">
            <p class="text-sm text-gray-500">
                Showing {{ $reservations->firstItem() }}–{{ $reservations->lastItem() }} of {{ $reservations->total() }} reservations
            </p>
            <div>{{ $reservations->links() }}</div>
        </div>
    @endif

</div>

<footer class="bg-gray-900 text-gray-400 py-8 px-6 text-center text-sm mt-16">
    <p>© {{ date('Y') }} EstateFlow. All rights reserved.</p>
    <div class="flex items-center justify-center gap-6 mt-2">
        <a href="{{ route('home') }}" class="hover:text-white transition">Home</a>
        <a href="{{ route('home.browse') }}" class="hover:text-white transition">Browse</a>
    </div>
</footer>

<script>
function togglePayments(id) {
    const panel   = document.getElementById('payments-' + id);
    const chevron = document.getElementById('chevron-' + id);
    panel.classList.toggle('hidden');
    chevron.style.transform = panel.classList.contains('hidden') ? '' : 'rotate(180deg)';
}
</script>

</body>
</html>
