<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password — EstateFlow</title>
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
            <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-key text-green-600 text-2xl"></i>
            </div>
            <h2 class="text-2xl font-bold text-gray-900">Set new password</h2>
            <p class="text-sm text-gray-500 mt-2">Choose a strong password for your account.</p>
        </div>

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
            <form method="POST" action="{{ route('password.update') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                    <input type="email" name="email" value="{{ $email ?? old('email') }}" required
                        class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-gray-50"
                        readonly>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                    <div class="relative">
                        <input type="password" name="password" id="password" required
                            placeholder="Min 8 characters"
                            class="w-full px-4 py-2.5 pr-10 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <button type="button" onclick="togglePw('password','eye1')"
                            class="absolute right-3 top-2.5 text-gray-400 hover:text-gray-600">
                            <i class="fas fa-eye" id="eye1"></i>
                        </button>
                    </div>
                    {{-- Strength bar --}}
                    <div class="mt-2">
                        <div class="flex gap-1 mb-1">
                            <div class="h-1.5 flex-1 rounded-full bg-gray-200" id="bar1"></div>
                            <div class="h-1.5 flex-1 rounded-full bg-gray-200" id="bar2"></div>
                            <div class="h-1.5 flex-1 rounded-full bg-gray-200" id="bar3"></div>
                            <div class="h-1.5 flex-1 rounded-full bg-gray-200" id="bar4"></div>
                        </div>
                        <p class="text-xs font-medium" id="strength-label"></p>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password</label>
                    <div class="relative">
                        <input type="password" name="password_confirmation" id="password_confirmation" required
                            placeholder="Repeat new password"
                            class="w-full px-4 py-2.5 pr-10 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <button type="button" onclick="togglePw('password_confirmation','eye2')"
                            class="absolute right-3 top-2.5 text-gray-400 hover:text-gray-600">
                            <i class="fas fa-eye" id="eye2"></i>
                        </button>
                    </div>
                    <p class="text-xs mt-1 hidden" id="confirm-msg"></p>
                </div>

                <button type="submit"
                    class="w-full bg-indigo-600 text-white py-3 rounded-xl font-semibold hover:bg-indigo-700 transition text-sm">
                    <i class="fas fa-check mr-2"></i>Reset Password
                </button>
            </form>
        </div>
    </div>
</div>

<script>
function togglePw(id, iconId) {
    const f = document.getElementById(id);
    const i = document.getElementById(iconId);
    f.type = f.type === 'password' ? 'text' : 'password';
    i.classList.toggle('fa-eye');
    i.classList.toggle('fa-eye-slash');
}

document.getElementById('password').addEventListener('input', function () {
    const v = this.value;
    const checks = [v.length >= 8, /[A-Z]/.test(v), /[0-9]/.test(v), /[@$!%*#?&]/.test(v)];
    const score  = checks.filter(Boolean).length;
    const colors = ['bg-red-400','bg-orange-400','bg-yellow-400','bg-green-500'];
    const labels = ['','Weak','Fair','Good','Strong'];
    const lColors = ['','text-red-500','text-orange-500','text-yellow-600','text-green-600'];

    ['bar1','bar2','bar3','bar4'].forEach((b, i) => {
        const el = document.getElementById(b);
        el.className = 'h-1.5 flex-1 rounded-full transition-all ' + (i < score ? colors[score-1] : 'bg-gray-200');
    });

    const lbl = document.getElementById('strength-label');
    lbl.textContent = v.length ? labels[score] : '';
    lbl.className   = 'text-xs font-medium ' + (v.length ? lColors[score] : '');

    checkConfirm();
});

function checkConfirm() {
    const pw  = document.getElementById('password').value;
    const cpw = document.getElementById('password_confirmation').value;
    const msg = document.getElementById('confirm-msg');
    if (!cpw) { msg.classList.add('hidden'); return; }
    if (pw === cpw) {
        msg.textContent = '✓ Passwords match.';
        msg.className   = 'text-xs mt-1 text-green-600';
    } else {
        msg.textContent = '✗ Passwords do not match.';
        msg.className   = 'text-xs mt-1 text-red-500';
    }
    msg.classList.remove('hidden');
}
document.getElementById('password_confirmation').addEventListener('input', checkConfirm);
</script>

</body>
</html>
