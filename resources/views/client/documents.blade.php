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

    <h1 class="text-2xl font-bold text-gray-900 mb-1">My Documents</h1>
    <p class="text-gray-500 text-sm mb-8">Documents linked to your profile and reservations</p>

    @if(!$clientRecord)
    <div class="mb-6 bg-yellow-50 border border-yellow-200 rounded-xl p-4 flex items-start gap-3">
        <i class="fas fa-exclamation-triangle text-yellow-500 mt-0.5"></i>
        <div>
            <p class="text-sm font-medium text-yellow-800">Your account is not linked to a client profile yet.</p>
            <p class="text-xs text-yellow-600 mt-1">Please contact an administrator.</p>
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
                                @if($doc->description)
                                    <p class="text-xs text-gray-400 mt-0.5">{{ Str::limit($doc->description, 50) }}</p>
                                @endif
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
                        @else
                            <span class="text-xs bg-yellow-100 text-yellow-700 px-2 py-1 rounded-full">
                                <i class="fas fa-clock mr-1"></i>Pending
                            </span>
                        @endif
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
        <p>No documents found for your account.</p>
        <p class="text-xs mt-2">Documents uploaded by your agent or admin will appear here.</p>
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

</body>
</html>
