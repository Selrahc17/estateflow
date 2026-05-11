<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Message — EstateFlow</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50 font-sans">

@include('partials.client-nav')

<div class="max-w-2xl mx-auto px-6 py-8">

    <a href="{{ route('messages.index') }}" class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-indigo-600 transition mb-6">
        <i class="fas fa-arrow-left"></i> Back to Messages
    </a>

    <h1 class="text-2xl font-bold text-gray-900 mb-1">New Message</h1>
    <p class="text-gray-500 text-sm mb-8">Start a conversation</p>

    <div class="bg-white rounded-xl shadow-sm p-6">
        @if($contacts->isEmpty())
            <div class="text-center py-8 text-gray-400">
                <i class="fas fa-users text-4xl mb-3 block text-gray-200"></i>
                <p>No contacts available to message.</p>
            </div>
        @else
            <p class="text-sm text-gray-500 mb-4">Select a person to start a conversation with:</p>
            <div class="space-y-2">
                @foreach($contacts as $contact)
                    <a href="{{ route('messages.show', $contact) }}"
                        class="flex items-center gap-4 p-4 rounded-xl border border-gray-100 hover:border-indigo-300 hover:bg-indigo-50 transition {{ $selectedUserId == $contact->id ? 'border-indigo-400 bg-indigo-50' : '' }}">
                        <div class="w-10 h-10 rounded-full bg-blue-500 flex items-center justify-center font-semibold text-white">
                            {{ strtoupper(substr($contact->name, 0, 1)) }}
                        </div>
                        <div>
                            <p class="font-medium text-gray-800">{{ $contact->name }}</p>
                            <p class="text-xs text-gray-400 capitalize">{{ $contact->role }} · {{ $contact->email }}</p>
                        </div>
                        <i class="fas fa-chevron-right text-gray-300 ml-auto"></i>
                    </a>
                @endforeach
            </div>
        @endif
    </div>

</div>

<footer class="bg-gray-900 text-gray-400 py-8 px-6 text-center text-sm mt-16">
    <p>© {{ date('Y') }} EstateFlow. All rights reserved.</p>
</footer>

</body>
</html>
