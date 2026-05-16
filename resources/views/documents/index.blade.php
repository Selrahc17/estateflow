@extends('layouts.app')

@section('title', 'Documents - EstateFlow')
@section('page-title', 'Documents')
@section('page-subtitle', 'Manage all uploaded documents')

@section('content')

{{-- Stats --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-6 mb-6">
    <div class="bg-white rounded-xl shadow-sm p-6 flex items-center gap-4">
        <div class="w-12 h-12 bg-indigo-100 rounded-xl flex items-center justify-center">
            <i class="fas fa-file-alt text-indigo-600 text-xl"></i>
        </div>
        <div>
            <p class="text-sm text-gray-500">Total Documents</p>
            <p class="text-2xl font-bold text-gray-800">{{ $totalDocuments }}</p>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-6 flex items-center gap-4">
        <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
            <i class="fas fa-check-circle text-green-600 text-xl"></i>
        </div>
        <div>
            <p class="text-sm text-gray-500">Verified</p>
            <p class="text-2xl font-bold text-gray-800">{{ $verifiedCount }}</p>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-6 flex items-center gap-4">
        <div class="w-12 h-12 bg-yellow-100 rounded-xl flex items-center justify-center">
            <i class="fas fa-clock text-yellow-600 text-xl"></i>
        </div>
        <div>
            <p class="text-sm text-gray-500">Pending Verification</p>
            <p class="text-2xl font-bold text-gray-800">{{ $pendingCount }}</p>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm p-6 flex items-center gap-4">
        <div class="w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center">
            <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
        </div>
        <div>
            <p class="text-sm text-gray-500">Expired / Expiring</p>
            <p class="text-2xl font-bold text-gray-800">{{ $expiredCount + $expiringSoon }}</p>
        </div>
    </div>
</div>

{{-- Expiry Alert --}}
@if($expiredCount > 0 || $expiringSoon > 0)
<div class="mb-6 bg-red-50 border border-red-200 rounded-xl p-4 flex items-start gap-3">
    <i class="fas fa-exclamation-triangle text-red-500 mt-0.5"></i>
    <div>
        @if($expiredCount > 0)
            <p class="text-sm font-medium text-red-800">{{ $expiredCount }} document(s) have expired.</p>
        @endif
        @if($expiringSoon > 0)
            <p class="text-sm font-medium text-orange-800">{{ $expiringSoon }} document(s) are expiring within 30 days.</p>
        @endif
        <p class="text-xs text-red-600 mt-1">Please review and renew them as soon as possible.</p>
    </div>
</div>
@endif

{{-- Filters --}}
<div class="bg-white rounded-xl shadow-sm p-5 mb-6">
    <form method="GET" action="{{ route('documents.index') }}" class="flex flex-wrap gap-3 items-end">
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Search</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Title or type..."
                class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 w-56">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Type</label>
            <select name="document_type" class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">All Types</option>
                @foreach(['contract','permit','title','id','payment_receipt','deed_of_sale','floor_plan','other'] as $type)
                    <option value="{{ $type }}" {{ request('document_type') === $type ? 'selected' : '' }}>
                        {{ ucfirst(str_replace('_', ' ', $type)) }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Verified</label>
            <select name="is_verified" class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">All</option>
                <option value="1" {{ request('is_verified') === '1' ? 'selected' : '' }}>Verified</option>
                <option value="0" {{ request('is_verified') === '0' ? 'selected' : '' }}>Unverified</option>
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Expiry</label>
            <select name="expiry_status" class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">All</option>
                <option value="expired"       {{ request('expiry_status') === 'expired'       ? 'selected' : '' }}>Expired</option>
                <option value="expiring_soon" {{ request('expiry_status') === 'expiring_soon' ? 'selected' : '' }}>Expiring Soon</option>
            </select>
        </div>
        <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-indigo-700 transition">
            <i class="fas fa-search mr-1"></i> Filter
        </button>
        <a href="{{ route('documents.index') }}" class="bg-gray-100 text-gray-600 px-4 py-2 rounded-lg text-sm hover:bg-gray-200 transition">Reset</a>
    </form>
</div>

{{-- Header Row --}}
<div class="flex items-center justify-between mb-4">
    <p class="text-sm text-gray-500">{{ $documents->total() }} documents found</p>
    <a href="{{ route('documents.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-indigo-700 transition">
        <i class="fas fa-upload mr-1"></i> Upload Document
    </a>
</div>

{{-- Table --}}
<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-100">
            <tr>
                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Document</th>
                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Type</th>
                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Attached To</th>
                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Size</th>
                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Expiry</th>
                <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Verified</th>
                <th class="text-right px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @forelse($documents as $document)
            <tr class="hover:bg-gray-50 transition">
                <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 bg-indigo-50 rounded-lg flex items-center justify-center">
                            @if(str_contains($document->file_type, 'pdf'))
                                <i class="fas fa-file-pdf text-red-500"></i>
                            @elseif(str_contains($document->file_type, 'image'))
                                <i class="fas fa-file-image text-blue-500"></i>
                            @else
                                <i class="fas fa-file-alt text-gray-400"></i>
                            @endif
                        </div>
                        <div>
                            <p class="font-medium text-gray-800">{{ $document->title }}</p>
                            <p class="text-xs text-gray-400">{{ $document->file_name }}</p>
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4">
                    <span class="text-xs px-2 py-1 bg-gray-100 text-gray-600 rounded-full">
                        {{ ucfirst(str_replace('_', ' ', $document->document_type)) }}
                    </span>
                </td>
                <td class="px-6 py-4 text-xs text-gray-500">
                    @if($document->documentable_type === 'App\Models\Reservation')
                        @php $res = $document->documentable; @endphp
                        Reservation — {{ $res?->client?->full_name ?? '#'.$document->documentable_id }}<br>
                        <span class="text-gray-400">{{ $res?->property?->title ?? '' }}</span>
                    @elseif($document->documentable_type === 'App\Models\Client')
                        Client — {{ $document->documentable?->full_name ?? '#'.$document->documentable_id }}
                    @else
                        {{ class_basename($document->documentable_type) }} #{{ $document->documentable_id }}
                    @endif
                </td>
                <td class="px-6 py-4 text-gray-600">{{ $document->file_size_formatted }}</td>
                <td class="px-6 py-4">
                    @if($document->expiry_date)
                        @if($document->isExpired())
                            <span class="text-xs px-2.5 py-1 rounded-full bg-red-100 text-red-700 font-medium">
                                <i class="fas fa-times-circle mr-1"></i>Expired {{ $document->expiry_date->format('M d, Y') }}
                            </span>
                        @elseif($document->isExpiringSoon())
                            <span class="text-xs px-2.5 py-1 rounded-full bg-orange-100 text-orange-700 font-medium">
                                <i class="fas fa-exclamation-circle mr-1"></i>{{ $document->expiry_date->format('M d, Y') }}
                            </span>
                        @else
                            <span class="text-xs text-gray-500">{{ $document->expiry_date->format('M d, Y') }}</span>
                        @endif
                    @else
                        <span class="text-xs text-gray-300">—</span>
                    @endif
                </td>
                <td class="px-6 py-4">
                    @if($document->is_verified)
                        <span class="text-xs px-2.5 py-1 rounded-full bg-green-100 text-green-700 font-medium">
                            <i class="fas fa-check mr-1"></i>Verified
                        </span>
                    @else
                        <span class="text-xs px-2.5 py-1 rounded-full bg-yellow-100 text-yellow-700 font-medium">Pending</span>
                    @endif
                </td>
                <td class="px-6 py-4">
                    <div class="flex items-center justify-end gap-2">
                        <a href="{{ route('documents.download', $document) }}" class="text-xs px-3 py-1.5 bg-green-50 text-green-600 rounded-lg hover:bg-green-100 transition">
                            <i class="fas fa-download"></i>
                        </a>
                        <button type="button"
                            onclick="openPreview({!! Js::from(asset('storage/' . $document->file_path)) !!}, {!! Js::from($document->file_type) !!}, {!! Js::from($document->title) !!})"
                            class="text-xs px-3 py-1.5 bg-indigo-50 text-indigo-600 rounded-lg hover:bg-indigo-100 transition">
                            <i class="fas fa-eye"></i>
                        </button>
                        @if(auth()->user()->isAdmin() && !$document->is_verified)
                            <form method="POST" action="{{ route('documents.verify', $document) }}">
                                @csrf @method('PATCH')
                                <button type="submit" class="text-xs px-3 py-1.5 bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-100 transition">Verify</button>
                            </form>
                        @endif
                        @if(auth()->user()->isAdmin())
                            <form method="POST" action="{{ route('documents.destroy', $document) }}"
                                onsubmit="return confirm('Delete this document?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-xs px-3 py-1.5 bg-gray-50 text-gray-500 rounded-lg hover:bg-red-50 hover:text-red-600 transition">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="px-6 py-16 text-center text-gray-400">
                    <i class="fas fa-file-alt text-4xl mb-3 block text-gray-200"></i>
                    No documents found.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
    @if($documents->hasPages())
        <div class="px-6 py-4 border-t border-gray-100">{{ $documents->links() }}</div>
    @endif
</div>

{{-- Document Preview Modal --}}
<div id="doc-preview-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-75">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-3xl mx-4 overflow-hidden">
        <div class="flex items-center justify-between px-5 py-3 border-b border-gray-100">
            <p class="font-semibold text-gray-800 text-sm" id="preview-title"></p>
            <button onclick="closePreview()" class="text-gray-400 hover:text-gray-600 transition">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>
        <div class="p-4 flex items-center justify-center bg-gray-50" style="min-height:400px">
            <img id="preview-img" src="" alt="" class="hidden max-w-full max-h-96 rounded-lg object-contain">
            <iframe id="preview-pdf" src="" class="hidden w-full" style="height:480px"></iframe>
            <p id="preview-unsupported" class="hidden text-sm text-gray-400">Preview not available. <a id="preview-download" href="#" target="_blank" class="text-indigo-600 hover:underline">Open file</a></p>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function openPreview(url, type, title) {
    document.getElementById('preview-title').textContent = title;
    document.getElementById('preview-img').classList.add('hidden');
    document.getElementById('preview-pdf').classList.add('hidden');
    document.getElementById('preview-unsupported').classList.add('hidden');
    if (type && type.includes('image')) {
        const img = document.getElementById('preview-img');
        img.src = url;
        img.classList.remove('hidden');
    } else if (type && type.includes('pdf')) {
        const pdf = document.getElementById('preview-pdf');
        pdf.src = url;
        pdf.classList.remove('hidden');
    } else {
        document.getElementById('preview-download').href = url;
        document.getElementById('preview-unsupported').classList.remove('hidden');
    }
    document.getElementById('doc-preview-modal').classList.remove('hidden');
}
function closePreview() {
    document.getElementById('doc-preview-modal').classList.add('hidden');
    document.getElementById('preview-pdf').src = '';
}
</script>
@endpush
