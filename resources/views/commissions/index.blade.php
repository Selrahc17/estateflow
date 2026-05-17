@extends('layouts.app')
@section('title', 'Agent Commissions — EstateFlow')
@section('page-title', 'Agent Commissions')
@section('page-subtitle', 'Track and manage agent commission payouts')

@section('content')

@if(session('success'))
    <div class="mb-4 bg-green-50 border border-green-200 text-green-700 text-sm px-5 py-3 rounded-xl flex items-center gap-2">
        <i class="fas fa-check-circle text-green-500"></i> {{ session('success') }}
    </div>
@endif
@if(session('error'))
    <div class="mb-4 bg-red-50 border border-red-200 text-red-700 text-sm px-5 py-3 rounded-xl flex items-center gap-2">
        <i class="fas fa-exclamation-circle text-red-500"></i> {{ session('error') }}
    </div>
@endif

{{-- Stats --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <div class="bg-white rounded-xl shadow-sm p-5 flex items-center gap-4">
        <div class="w-11 h-11 bg-yellow-100 rounded-xl flex items-center justify-center">
            <i class="fas fa-clock text-yellow-600"></i>
        </div>
        <div>
            <p class="text-xs text-gray-500">Pending</p>
            <p class="text-xl font-bold text-gray-800">₱{{ number_format($totalPending, 2) }}</p>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-5 flex items-center gap-4">
        <div class="w-11 h-11 bg-blue-100 rounded-xl flex items-center justify-center">
            <i class="fas fa-check text-blue-600"></i>
        </div>
        <div>
            <p class="text-xs text-gray-500">Approved</p>
            <p class="text-xl font-bold text-gray-800">₱{{ number_format($totalApproved, 2) }}</p>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-5 flex items-center gap-4">
        <div class="w-11 h-11 bg-green-100 rounded-xl flex items-center justify-center">
            <i class="fas fa-money-bill-wave text-green-600"></i>
        </div>
        <div>
            <p class="text-xs text-gray-500">Total Paid Out</p>
            <p class="text-xl font-bold text-gray-800">₱{{ number_format($totalPaid, 2) }}</p>
        </div>
    </div>
</div>

{{-- Filters --}}
<div class="bg-white rounded-xl shadow-sm p-4 mb-6">
    <form method="GET" class="flex flex-wrap gap-3 items-end">
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Status</label>
            <select name="status" class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">All Status</option>
                @foreach(['pending','approved','paid','cancelled'] as $s)
                    <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Agent</label>
            <select name="agent_id" class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">All Agents</option>
                @foreach($agents as $agent)
                    <option value="{{ $agent->id }}" {{ request('agent_id') == $agent->id ? 'selected' : '' }}>{{ $agent->full_name }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-indigo-700 transition">Filter</button>
        <a href="{{ route('commissions.index') }}" class="px-4 py-2 bg-gray-100 text-gray-600 rounded-lg text-sm hover:bg-gray-200 transition">Reset</a>
    </form>
</div>

{{-- Table --}}
<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-xs text-gray-500 uppercase tracking-wider">
            <tr>
                <th class="px-6 py-3 text-left">Agent</th>
                <th class="px-6 py-3 text-left">Property / Client</th>
                <th class="px-6 py-3 text-right">Property Price</th>
                <th class="px-6 py-3 text-right">Rate</th>
                <th class="px-6 py-3 text-right">Commission</th>
                <th class="px-6 py-3 text-center">Status</th>
                <th class="px-6 py-3 text-right">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @forelse($commissions as $c)
            <tr class="hover:bg-gray-50 transition">
                <td class="px-6 py-4">
                    <p class="font-medium text-gray-800">{{ $c->agent->full_name ?? '—' }}</p>
                    <p class="text-xs text-gray-400">{{ $c->agent->agent_code ?? '' }}</p>
                </td>
                <td class="px-6 py-4">
                    <p class="font-medium text-gray-800">{{ $c->reservation->property->title ?? '—' }}</p>
                    <p class="text-xs text-gray-400">{{ $c->reservation->client->full_name ?? '' }}</p>
                </td>
                <td class="px-6 py-4 text-right text-gray-700">₱{{ number_format($c->property_price, 2) }}</td>
                <td class="px-6 py-4 text-right text-gray-700">{{ $c->commission_rate }}%</td>
                <td class="px-6 py-4 text-right font-bold text-indigo-600">₱{{ number_format($c->commission_amount, 2) }}</td>
                <td class="px-6 py-4 text-center">
                    <span class="text-xs px-2.5 py-1 rounded-full font-medium {{ \App\Models\AgentCommission::STATUS_COLORS[$c->status] ?? 'bg-gray-100 text-gray-600' }}">
                        {{ ucfirst($c->status) }}
                    </span>
                    @if($c->paid_at)
                        <p class="text-xs text-gray-400 mt-0.5">{{ $c->paid_at->format('M d, Y') }}</p>
                    @endif
                </td>
                <td class="px-6 py-4 text-right">
                    <div class="flex items-center justify-end gap-2">
                        @if($c->status === 'pending')
                            <form method="POST" action="{{ route('commissions.approve', $c) }}">
                                @csrf @method('PATCH')
                                <button class="text-xs bg-blue-600 text-white px-3 py-1.5 rounded-lg hover:bg-blue-700 transition">Approve</button>
                            </form>
                            <form method="POST" action="{{ route('commissions.cancel', $c) }}">
                                @csrf @method('PATCH')
                                <button class="text-xs bg-red-50 text-red-600 px-3 py-1.5 rounded-lg hover:bg-red-100 transition">Cancel</button>
                            </form>
                        @elseif($c->status === 'approved')
                            <button type="button" onclick="document.getElementById('pay-modal-{{ $c->id }}').classList.remove('hidden')"
                                class="text-xs bg-green-600 text-white px-3 py-1.5 rounded-lg hover:bg-green-700 transition">
                                Mark Paid
                            </button>
                        @elseif($c->status === 'paid')
                            <span class="text-xs text-gray-400">OR# {{ $c->or_number ?? '—' }}</span>
                        @endif
                    </div>
                </td>
            </tr>

            {{-- Pay Modal --}}
            @if($c->status === 'approved')
            <div id="pay-modal-{{ $c->id }}" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
                <div class="bg-white rounded-xl shadow-xl p-6 w-full max-w-sm mx-4">
                    <h3 class="font-semibold text-gray-800 mb-1">Mark Commission as Paid</h3>
                    <p class="text-xs text-gray-500 mb-4">₱{{ number_format($c->commission_amount, 2) }} — {{ $c->agent->full_name ?? '' }}</p>
                    <form method="POST" action="{{ route('commissions.paid', $c) }}">
                        @csrf @method('PATCH')
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">OR Number <span class="text-red-500">*</span></label>
                            <input type="text" name="or_number" required placeholder="e.g. OR-2024-XXXXX"
                                class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <div class="flex gap-3">
                            <button type="button" onclick="document.getElementById('pay-modal-{{ $c->id }}').classList.add('hidden')"
                                class="flex-1 bg-gray-100 text-gray-700 py-2 rounded-lg text-sm hover:bg-gray-200 transition">Cancel</button>
                            <button type="submit"
                                class="flex-1 bg-green-600 text-white py-2 rounded-lg text-sm hover:bg-green-700 transition font-medium">Confirm</button>
                        </div>
                    </form>
                </div>
            </div>
            @endif

            @empty
            <tr>
                <td colspan="7" class="px-6 py-12 text-center text-gray-400">
                    <i class="fas fa-hand-holding-usd text-3xl mb-2 block text-gray-200"></i>
                    No commissions found.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($commissions->hasPages())
    <div class="mt-6">{{ $commissions->links() }}</div>
@endif

@endsection
