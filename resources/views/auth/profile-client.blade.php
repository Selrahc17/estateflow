<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile — EstateFlow</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50 font-sans">

@include('partials.client-nav')

<div class="max-w-2xl mx-auto px-6 py-8">

    <a href="{{ route('home') }}" class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-indigo-600 transition mb-6">
        <i class="fas fa-arrow-left"></i> Back to Home
    </a>

    <h1 class="text-2xl font-bold text-gray-900 mb-1">My Profile</h1>
    <p class="text-gray-500 text-sm mb-8">Update your account information</p>

    @if(session('success'))
        <div class="mb-4 bg-green-50 border border-green-200 text-green-700 text-sm px-4 py-3 rounded-lg">
            <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="mb-4 bg-red-50 border border-red-200 rounded-lg p-4">
            <ul class="text-sm text-red-600 list-disc list-inside space-y-1">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="flex items-center gap-4 mb-6 pb-6 border-b border-gray-100">
            <div>
                @if($user->avatar)
                    <img src="{{ asset('storage/' . $user->avatar) }}" id="avatar-preview"
                        class="w-16 h-16 rounded-full object-cover ring-2 ring-indigo-200">
                @else
                    <div id="avatar-initials" class="w-16 h-16 bg-indigo-500 rounded-full flex items-center justify-center text-white font-bold text-2xl">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                    <img id="avatar-preview" class="w-16 h-16 rounded-full object-cover ring-2 ring-indigo-200 hidden">
                @endif
            </div>
            <div>
                <p class="font-semibold text-gray-800 text-lg">{{ $user->name }}</p>
                <p class="text-sm text-gray-400">{{ $user->email }}</p>
                <span class="text-xs px-2 py-0.5 rounded-full font-medium mt-1 inline-block bg-purple-100 text-purple-700">Client</span>
            </div>
        </div>

        <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
            @csrf @method('PUT')
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Profile Photo</label>
                    <div class="flex items-center gap-3">
                        <label for="avatar-input" class="cursor-pointer bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm px-4 py-2 rounded-lg transition">
                            <i class="fas fa-camera mr-1"></i> Choose Photo
                        </label>
                        <span class="text-xs text-gray-400" id="avatar-filename">Max 2MB. JPG, PNG.</span>
                    </div>
                    <input type="file" id="avatar-input" name="avatar" accept="image/*" class="hidden" onchange="previewAvatar(this)">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}"
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}"
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div class="pt-4 border-t border-gray-100">
                    <p class="text-sm font-medium text-gray-700 mb-3">Change Password <span class="text-gray-400 font-normal">(leave blank to keep current)</span></p>
                    <div class="space-y-3">
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Current Password</label>
                            <input type="password" name="current_password"
                                class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">New Password</label>
                            <input type="password" name="password"
                                class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Confirm New Password</label>
                            <input type="password" name="password_confirmation"
                                class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                    </div>
                </div>
            </div>
            <div class="mt-6 flex gap-3">
                <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded-lg text-sm hover:bg-indigo-700 transition font-medium">
                    Save Changes
                </button>
                <a href="{{ route('home') }}" class="bg-gray-100 text-gray-600 px-6 py-2 rounded-lg text-sm hover:bg-gray-200 transition">
                    Cancel
                </a>
            </div>
        </form>
    </div>

</div>

<footer class="bg-gray-900 text-gray-400 py-8 px-6 text-center text-sm mt-16">
    <p>© {{ date('Y') }} EstateFlow. All rights reserved.</p>
</footer>

<script>
function previewAvatar(input) {
    if (!input.files || !input.files[0]) return;
    const reader = new FileReader();
    reader.onload = e => {
        const preview  = document.getElementById('avatar-preview');
        const initials = document.getElementById('avatar-initials');
        preview.src = e.target.result;
        preview.classList.remove('hidden');
        if (initials) initials.classList.add('hidden');
    };
    reader.readAsDataURL(input.files[0]);
    document.getElementById('avatar-filename').textContent = input.files[0].name;
}
</script>

</body>
</html>
