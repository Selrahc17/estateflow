@extends('layouts.app')

@section('title', 'Create User - EstateFlow')
@section('page-title', 'Create User Account')
@section('page-subtitle', 'Manually create an agent, staff, finance, or admin account')

@section('content')
<div class="max-w-2xl">
    <div class="bg-white rounded-xl shadow-sm p-6">
        <form method="POST" action="{{ route('admin.users.store') }}">
            @csrf

            @if($errors->any())
                <div class="mb-4 bg-red-50 border border-red-200 rounded-xl p-4">
                    <ul class="text-sm text-red-600 space-y-1 list-disc list-inside">
                        @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                    </ul>
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Full Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}"
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email Address <span class="text-red-500">*</span></label>
                    <input type="email" name="email" value="{{ old('email') }}"
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Role <span class="text-red-500">*</span></label>
                    <select name="role" id="roleSelect" onchange="toggleAgentFields()"
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="agent"   {{ old('role', 'agent') === 'agent'   ? 'selected' : '' }}>Agent</option>
                        <option value="finance" {{ old('role') === 'finance' ? 'selected' : '' }}>Finance</option>
                        <option value="staff"   {{ old('role') === 'staff'   ? 'selected' : '' }}>Staff</option>
                        <option value="admin"   {{ old('role') === 'admin'   ? 'selected' : '' }}>Admin</option>
                        <option value="client"  {{ old('role') === 'client'  ? 'selected' : '' }}>Client</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Password <span class="text-red-500">*</span></label>
                    <input type="password" name="password"
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Confirm Password <span class="text-red-500">*</span></label>
                    <input type="password" name="password_confirmation"
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                {{-- Agent Profile Fields (shown only when role = agent) --}}
                <div id="agentFields" class="md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-4">

                    <div class="md:col-span-2">
                        <hr class="border-gray-100 mb-2">
                        <p class="text-xs font-semibold text-indigo-700 uppercase tracking-wider mb-3">
                            <i class="fas fa-user-tie mr-1"></i> Agent Profile
                        </p>
                    </div>

                    <div class="md:col-span-2 bg-indigo-50 border border-indigo-200 rounded-xl p-3 flex items-center gap-3">
                        <i class="fas fa-id-badge text-indigo-500"></i>
                        <div>
                            <p class="text-xs font-semibold text-indigo-700">Agent Code</p>
                            <p class="text-sm font-mono font-bold text-indigo-900">Auto-generated upon saving (e.g. AGT-004)</p>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Phone <span class="text-red-500">*</span></label>
                        <input type="text" name="agent_phone" value="{{ old('agent_phone') }}"
                            placeholder="09XXXXXXXXX"
                            class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">License Number</label>
                        <input type="text" name="license_number" value="{{ old('license_number') }}"
                            class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Commission Rate (%)</label>
                        <input type="number" name="commission_rate" value="{{ old('commission_rate', 0) }}" step="0.01" min="0" max="100"
                            class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select name="agent_status" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="suspended">Suspended</option>
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                        <textarea name="agent_address" rows="2"
                            class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('agent_address') }}</textarea>
                    </div>

                </div>
                {{-- End Agent Fields --}}

                <div class="md:col-span-2 bg-blue-50 border border-blue-100 rounded-xl p-3 text-xs text-blue-700">
                    <i class="fas fa-info-circle mr-1"></i>
                    Accounts created by admin are <strong>active immediately</strong> and do not require approval.
                </div>

            </div>

            <div class="mt-6 flex gap-3">
                <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded-lg text-sm hover:bg-indigo-700 transition font-medium">
                    Create Account
                </button>
                <a href="{{ route('admin.users') }}" class="bg-gray-100 text-gray-600 px-6 py-2 rounded-lg text-sm hover:bg-gray-200 transition">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
function toggleAgentFields() {
    const role   = document.getElementById('roleSelect').value;
    const fields = document.getElementById('agentFields');
    fields.style.display = role === 'agent' ? 'contents' : 'none';
}
// Run on page load to respect old() value
toggleAgentFields();
</script>
@endsection
