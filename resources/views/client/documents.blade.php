<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Documents — EstateFlow</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50 font-sans">

@include('partials.client-nav')

<div class="max-w-7xl mx-auto px-6 py-8">

    <div class="flex items-center justify-between mb-1">
        <h1 class="text-2xl font-bold text-gray-900">My Documents</h1>
        @if($clientRecord)
        <button onclick="document.getElementById('uploadModal').classList.remove('hidden')"
            class="flex items-center gap-2 bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-indigo-700 transition font-medium">
            <i class="fas fa-upload"></i> Upload Document
        </button>
        @endif
    </div>
    <p class="text-gray-500 text-sm mb-6">Documents linked to your profile and reservations</p>

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
    @if($errors->any())
        <div class="mb-6 bg-red-50 border border-red-200 rounded-xl p-4">
            <ul class="text-sm text-red-600 list-disc list-inside space-y-1">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
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

    {{-- Required Documents Info --}}
    @if($clientRecord)
    <div class="bg-indigo-50 border border-indigo-100 rounded-xl p-4 mb-6 flex items-start gap-3">
        <i class="fas fa-info-circle text-indigo-400 mt-0.5 flex-shrink-0"></i>
        <div>
            <p class="text-sm font-medium text-indigo-800">Required Documents</p>
            <p class="text-xs text-indigo-600 mt-1">
                For <span class="font-semibold">pending/confirmed</span> reservations: Valid Government ID, Proof of Income, TIN, Contract to Sell.
                For <span class="font-semibold">completed</span> reservations: additionally Deed of Sale and Transfer Certificate of Title.
            </p>
        </div>
    </div>
    @endif

    @if($documents->count())
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr>
                    <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Document</th>
                    <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Type</th>
                    <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Linked To</th>
                    <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Expiry</th>
                    <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Date</th>
                    <th class="px-6 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($documents as $doc)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 bg-indigo-50 rounded-lg flex items-center justify-center flex-shrink-0">
                                @if(str_contains($doc->file_type, 'pdf'))
                                    <i class="fas fa-file-pdf text-red-400"></i>
                                @elseif(str_contains($doc->file_type, 'image'))
                                    <i class="fas fa-file-image text-blue-400"></i>
                                @else
                                    <i class="fas fa-file-alt text-gray-400"></i>
                                @endif
                            </div>
                            <div>
                                <p class="font-medium text-gray-800">{{ $doc->title }}</p>
                                <p class="text-xs text-gray-400">{{ $doc->file_size_formatted }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded-full">
                            {{ ucfirst(str_replace('_', ' ', $doc->document_type)) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-gray-600">
                        @if($doc->documentable_type === \App\Models\Reservation::class)
                            <span class="text-xs"><i class="fas fa-calendar-check mr-1 text-indigo-400"></i>Reservation #{{ $doc->documentable_id }}</span>
                        @else
                            <span class="text-xs"><i class="fas fa-user mr-1 text-green-400"></i>My Profile</span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        @if($doc->is_verified)
                            <span class="text-xs bg-green-100 text-green-700 px-2 py-1 rounded-full">
                                <i class="fas fa-check-circle mr-1"></i>Verified
                            </span>
                        @elseif($doc->isExpired())
                            <span class="text-xs bg-red-100 text-red-700 px-2 py-1 rounded-full">
                                <i class="fas fa-times-circle mr-1"></i>Expired
                            </span>
                        @else
                            <span class="text-xs bg-yellow-100 text-yellow-700 px-2 py-1 rounded-full">
                                <i class="fas fa-clock mr-1"></i>Pending Review
                            </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-xs text-gray-400">
                        {{ $doc->expiry_date ? $doc->expiry_date->format('M d, Y') : '—' }}
                    </td>
                    <td class="px-6 py-4 text-xs text-gray-400">{{ $doc->created_at->format('M d, Y') }}</td>
                    <td class="px-6 py-4 text-right">
                        <a href="{{ route('documents.download', $doc) }}"
                           class="text-indigo-600 hover:text-indigo-800 text-xs font-medium transition">
                            <i class="fas fa-download mr-1"></i>Download
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        @if($documents->hasPages())
            <div class="px-6 py-4 border-t border-gray-100 flex items-center justify-between">
                <p class="text-sm text-gray-500">
                    Showing {{ $documents->firstItem() }}–{{ $documents->lastItem() }} of {{ $documents->total() }} documents
                </p>
                <div>{{ $documents->links() }}</div>
            </div>
        @endif
    </div>

    @else
    <div class="bg-white rounded-xl shadow-sm p-16 text-center text-gray-400">
        <i class="fas fa-folder-open text-4xl mb-3 block text-gray-200"></i>
        <p>No documents uploaded yet.</p>
        @if($clientRecord)
        <button onclick="document.getElementById('uploadModal').classList.remove('hidden')"
            class="mt-4 inline-flex items-center gap-2 bg-indigo-600 text-white px-5 py-2 rounded-lg text-sm hover:bg-indigo-700 transition">
            <i class="fas fa-upload"></i> Upload Your First Document
        </button>
        @endif
    </div>
    @endif

</div>

{{-- Upload Modal --}}
@if($clientRecord)
<div id="uploadModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 px-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md">

        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <h2 class="font-semibold text-gray-800 flex items-center gap-2">
                <i class="fas fa-upload text-indigo-500"></i> Upload Document
            </h2>
            <button onclick="document.getElementById('uploadModal').classList.add('hidden')"
                class="text-gray-400 hover:text-gray-600 transition">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <form method="POST" action="{{ route('client.documents.store') }}" enctype="multipart/form-data" class="px-6 py-5 space-y-4">
            @csrf

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Document Type <span class="text-red-500">*</span></label>
                <select name="document_type" required
                    class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">Select type...</option>
                    <option value="id">Valid Government ID</option>
                    <option value="proof_of_income">Proof of Income</option>
                    <option value="tin">TIN / Tax Identification</option>
                    <option value="contract">Contract to Sell</option>
                    <option value="deed_of_sale">Deed of Sale</option>
                    <option value="title">Transfer Certificate of Title</option>
                    <option value="other">Other</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Document Title <span class="text-red-500">*</span></label>
                <input type="text" name="title" required placeholder="e.g. PhilSys ID - Juan Dela Cruz"
                    value="{{ old('title') }}"
                    class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            @if($reservations->count())
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Link to Reservation <span class="text-gray-400">(optional)</span></label>
                <select name="reservation_id"
                    class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">My Profile (General)</option>
                    @foreach($reservations as $res)
                        <option value="{{ $res->id }}">
                            {{ $res->property->title ?? 'Property #'.$res->id }} — {{ ucfirst($res->status) }}
                        </option>
                    @endforeach
                </select>
                <p class="text-xs text-gray-400 mt-1">Link to a specific reservation if this document is for that property.</p>
            </div>
            @endif

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Expiry Date <span class="text-gray-400">(if applicable)</span></label>
                <input type="date" name="expiry_date" value="{{ old('expiry_date') }}"
                    class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">File <span class="text-red-500">*</span></label>
                <input type="file" name="file" required accept=".pdf,.jpg,.jpeg,.png"
                    class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:bg-indigo-50 file:text-indigo-600">
                <p class="text-xs text-gray-400 mt-1">Accepted: PDF, JPG, PNG — Max 10MB</p>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="button"
                    onclick="document.getElementById('uploadModal').classList.add('hidden')"
                    class="flex-1 px-4 py-2 bg-gray-100 text-gray-600 rounded-lg text-sm hover:bg-gray-200 transition">
                    Cancel
                </button>
                <button type="submit"
                    class="flex-1 px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm hover:bg-indigo-700 transition font-medium">
                    <i class="fas fa-upload mr-1"></i> Upload
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Re-open modal if validation failed --}}
@if($errors->any())
<script>document.getElementById('uploadModal').classList.remove('hidden');</script>
@endif
@endif

<footer class="bg-gray-900 text-gray-400 py-8 px-6 text-center text-sm mt-16">
    <p>© {{ date('Y') }} EstateFlow. All rights reserved.</p>
    <div class="flex items-center justify-center gap-6 mt-2">
        <a href="{{ route('home') }}" class="hover:text-white transition">Home</a>
        <a href="{{ route('home.browse') }}" class="hover:text-white transition">Browse</a>
    </div>
</footer>

</body>
</html>
