<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — EstateFlow</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50 min-h-screen flex flex-col">

{{-- Top Nav --}}
<nav class="bg-white shadow-sm px-6 py-4 flex items-center justify-between">
    <a href="{{ route('home') }}" class="flex items-center gap-2">
        <div class="w-8 h-8 rounded-lg flex items-center justify-center overflow-hidden">
            <img src="/estateflow/public/logo.png" alt="EstateFlow" class="w-8 h-8 object-contain">
        </div>
        <span class="font-bold text-gray-900 text-lg">EstateFlow</span>
    </a>
    <a href="{{ route('home') }}" class="flex items-center gap-2 text-sm text-gray-500 hover:text-indigo-600 transition">
        <i class="fas fa-arrow-left"></i> Back to Homepage
    </a>
</nav>

<div class="flex-1 flex items-center justify-center py-12 px-4">
    <div class="max-w-md w-full">

        <div class="text-center mb-8">
            <h2 class="text-2xl font-bold text-gray-900">Welcome back</h2>
            <p class="text-sm text-gray-500 mt-1">Sign in to your EstateFlow account</p>
        </div>

        {{-- Status message (e.g. after registration) --}}
        @if(session('status'))
            <div class="mb-4 bg-green-50 border border-green-200 text-green-700 text-sm px-4 py-3 rounded-xl flex items-start gap-2">
                <i class="fas fa-check-circle mt-0.5"></i>
                <span>{{ session('status') }}</span>
            </div>
        @endif

        {{-- Errors --}}
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
            <form method="POST" action="{{ route('login') }}" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                    <input type="email" name="email" value="{{ old('email') }}" placeholder="you@example.com" required
                        class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <div class="relative">
                        <input type="password" name="password" id="password" placeholder="Your password" required
                            class="w-full px-4 py-2.5 pr-10 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <button type="button" onclick="togglePw()"
                            class="absolute right-3 top-2.5 text-gray-400 hover:text-gray-600">
                            <i class="fas fa-eye" id="pw-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="flex items-center justify-between text-sm">
                    <label class="flex items-center gap-2 text-gray-600">
                        <input type="checkbox" name="remember" class="rounded border-gray-300 text-indigo-600">
                        Remember me
                    </label>
                    <a href="{{ route('password.request') }}" class="text-indigo-600 hover:underline">Forgot password?</a>
                </div>

                <button type="submit"
                    class="w-full bg-indigo-600 text-white py-3 rounded-xl font-semibold hover:bg-indigo-700 transition text-sm">
                    Sign In
                </button>
            </form>

            <p class="text-center text-sm text-gray-500 mt-6">
                Don't have an account?
                <a href="{{ route('register') }}" class="text-indigo-600 hover:underline font-medium">Register here</a>
            </p>

            <div class="mt-6 pt-6 border-t border-gray-100">
                <p class="text-center text-xs text-gray-400 mb-3">Just browsing? No account needed.</p>
                <a href="{{ route('home.browse') }}"
                    class="flex items-center justify-center gap-2 w-full py-2.5 rounded-xl border-2 border-indigo-200 text-indigo-600 text-sm font-semibold hover:bg-indigo-50 hover:border-indigo-400 transition">
                    <i class="fas fa-building"></i> Browse Properties as Guest
                </a>
            </div>
        </div>
    </div>
</div>

<script>
function togglePw() {
    const f = document.getElementById('password');
    const i = document.getElementById('pw-eye');
    f.type = f.type === 'password' ? 'text' : 'password';
    i.classList.toggle('fa-eye');
    i.classList.toggle('fa-eye-slash');
}
</script>

</body>
</html>
