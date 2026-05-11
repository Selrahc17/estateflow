<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password — EstateFlow</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50 min-h-screen flex flex-col">

{{-- Navbar --}}
<nav class="bg-white shadow-sm px-6 py-4 flex items-center justify-between">
    <a href="{{ route('home') }}" class="flex items-center gap-2">
        <div class="w-8 h-8 bg-indigo-600 rounded-lg flex items-center justify-center">
            <i class="fas fa-building text-white text-sm"></i>
        </div>
        <span class="font-bold text-gray-900 text-lg">EstateFlow</span>
    </a>
    <a href="{{ route('login') }}" class="flex items-center gap-2 text-sm text-gray-500 hover:text-indigo-600 transition">
        <i class="fas fa-arrow-left"></i> Back to Login
    </a>
</nav>

<div class="flex-1 flex items-center justify-center py-12 px-4">
    <div class="max-w-md w-full">

        <div class="text-center mb-8">
            <div class="w-16 h-16 bg-indigo-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-lock text-indigo-600 text-2xl"></i>
            </div>
            <h2 class="text-2xl font-bold text-gray-900">Forgot your password?</h2>
            <p class="text-sm text-gray-500 mt-2">No worries. Enter your email and we'll send you a reset link.</p>
        </div>

        {{-- Status --}}
        @if(session('status'))
            <div class="mb-4 bg-green-50 border border-green-200 text-green-700 text-sm px-4 py-3 rounded-xl flex items-start gap-2">
                <i class="fas fa-check-circle mt-0.5"></i>
                <span>{{ session('status') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-4 bg-blue-50 border border-blue-200 text-blue-700 text-sm px-4 py-3 rounded-xl flex items-start gap-2">
                <i class="fas fa-info-circle mt-0.5"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        @if($errors->any())
            <div class="mb-4 bg-red-50 border border-red-200 rounded-xl p-4">
                <ul class="text-sm text-red-600 space-y-1 list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="bg-white rounded-2xl shadow-sm p-8">
            <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                    <input type="email" name="email" value="{{ old('email') }}" required
                        placeholder="you@example.com"
                        class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <button type="submit"
                    class="w-full bg-indigo-600 text-white py-3 rounded-xl font-semibold hover:bg-indigo-700 transition text-sm">
                    <i class="fas fa-paper-plane mr-2"></i>Send Reset Link
                </button>
            </form>

            <p class="text-center text-sm text-gray-500 mt-6">
                Remember your password?
                <a href="{{ route('login') }}" class="text-indigo-600 hover:underline font-medium">Sign in</a>
            </p>
        </div>

        {{-- Info box --}}
        <div class="mt-4 bg-blue-50 border border-blue-100 rounded-xl p-4 text-xs text-blue-700">
            <i class="fas fa-info-circle mr-1"></i>
            If an account exists with that email, you'll receive a reset link within a few minutes. Check your spam folder if you don't see it.
        </div>
    </div>
</div>

</body>
</html>
