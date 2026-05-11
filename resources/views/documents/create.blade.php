@extends('layouts.app')

@section('title', 'Upload Document - EstateFlow')
@section('page-title', 'Upload Document')
@section('page-subtitle', 'Attach a document to a record')

@section('content')
<div class="max-w-2xl">
    <div class="bg-white rounded-xl shadow-sm p-6">
        <form method="POST" action="{{ route('documents.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Title <span class="text-red-500">*</span></label>
                    <input type="text" name="title" value="{{ old('title') }}"
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Document Type <span class="text-red-500">*</span></label>
                    <select name="document_type" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        @foreach(['contract','permit','title','id','payment_receipt','deed_of_sale','floor_plan','other'] as $type)
                            <option value="{{ $type }}" {{ old('document_type') === $type ? 'selected' : '' }}>
                                {{ ucfirst(str_replace('_', ' ', $type)) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Attach To <span class="text-red-500">*</span></label>
                    <select name="documentable_type" id="documentable_type" onchange="updateOptions()"
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="property">Property</option>
                        <option value="client">Client</option>
                        <option value="project">Project</option>
                        <option value="reservation">Reservation</option>
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Select Record <span class="text-red-500">*</span></label>

                    <select name="documentable_id" id="property_select" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        @foreach($properties as $p)
                            <option value="{{ $p->id }}">{{ $p->title }}</option>
                        @endforeach
                    </select>

                    <select name="documentable_id" id="client_select" class="hidden w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        @foreach($clients as $c)
                            <option value="{{ $c->id }}">{{ $c->full_name }}</option>
                        @endforeach
                    </select>

                    <select name="documentable_id" id="project_select" class="hidden w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        @foreach($projects as $p)
                            <option value="{{ $p->id }}">{{ $p->name }}</option>
                        @endforeach
                    </select>

                    <select name="documentable_id" id="reservation_select" class="hidden w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        @foreach($reservations as $r)
                            <option value="{{ $r->id }}">#{{ $r->id }} — {{ $r->property->title ?? '' }} ({{ $r->client->full_name ?? '' }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">File <span class="text-red-500">*</span></label>
                    <input type="file" name="file"
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <p class="text-xs text-gray-400 mt-1">Max 10MB. Accepted: PDF, images, Word, Excel.</p>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea name="description" rows="2"
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('description') }}</textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Expiry Date <span class="text-gray-400 font-normal">(optional)</span></label>
                    <input type="date" name="expiry_date" value="{{ old('expiry_date') }}"
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <p class="text-xs text-gray-400 mt-1">Set for IDs, permits, contracts with expiry.</p>
                </div>
            </div>

            <div class="mt-6 flex gap-3">
                <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded-lg text-sm hover:bg-indigo-700 transition font-medium">
                    <i class="fas fa-upload mr-1"></i> Upload
                </button>
                <a href="{{ route('documents.index') }}" class="bg-gray-100 text-gray-600 px-6 py-2 rounded-lg text-sm hover:bg-gray-200 transition">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
function updateOptions() {
    const type = document.getElementById('documentable_type').value;
    ['property','client','project','reservation'].forEach(t => {
        const el = document.getElementById(t + '_select');
        el.classList.add('hidden');
        el.disabled = true;
    });
    const active = document.getElementById(type + '_select');
    active.classList.remove('hidden');
    active.disabled = false;
}
updateOptions();
</script>
@endsection
