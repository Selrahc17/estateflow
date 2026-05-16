<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register — EstateFlow</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .field-valid   { border-color: #10b981 !important; }
        .field-invalid { border-color: #ef4444 !important; }
        .hint-pass { color: #10b981; }
        .hint-fail { color: #9ca3af; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen flex flex-col">

{{-- Top Nav --}}
<nav class="bg-white shadow-sm px-6 py-4 flex items-center justify-between">
    <a href="{{ route('home') }}" class="flex items-center gap-2">
        <div class="w-8 h-8 rounded-lg flex items-center justify-center overflow-hidden">
            <img src="{{ asset('logo.png') }}" alt="EstateFlow" class="w-8 h-8 object-contain">
        </div>
        <span class="font-bold text-gray-900 text-lg">EstateFlow</span>
    </a>
    <a href="{{ route('home') }}" class="flex items-center gap-2 text-sm text-gray-500 hover:text-indigo-600 transition">
        <i class="fas fa-arrow-left"></i> Back to Homepage
    </a>
</nav>

<div class="flex-1 flex items-center justify-center py-12 px-4">
<div class="max-w-md w-full">

    {{-- Header --}}
    <div class="text-center mb-8">
        <h2 class="text-2xl font-bold text-gray-900">Create your account</h2>
        <p class="text-sm text-gray-500 mt-1">Join EstateFlow and find your dream property</p>
    </div>

    {{-- Server-side errors --}}
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
        <form method="POST" action="{{ route('register') }}" id="register-form" class="space-y-5" novalidate>
            @csrf

            {{-- First Name & Last Name --}}
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        First Name <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input type="text" name="first_name" id="first_name" value="{{ old('first_name') }}"
                            placeholder="Juan"
                            class="w-full px-4 py-2.5 pr-10 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 transition @error('first_name') field-invalid @enderror">
                        <span class="absolute right-3 top-2.5 text-lg" id="first_name-icon"></span>
                    </div>
                    <p class="text-xs mt-1 hidden" id="first_name-msg"></p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Last Name <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input type="text" name="last_name" id="last_name" value="{{ old('last_name') }}"
                            placeholder="dela Cruz"
                            class="w-full px-4 py-2.5 pr-10 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 transition @error('last_name') field-invalid @enderror">
                        <span class="absolute right-3 top-2.5 text-lg" id="last_name-icon"></span>
                    </div>
                    <p class="text-xs mt-1 hidden" id="last_name-msg"></p>
                </div>
            </div>

            {{-- Email --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Email Address <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <input type="email" name="email" id="email" value="{{ old('email') }}"
                        placeholder="you@example.com"
                        class="w-full px-4 py-2.5 pr-10 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 transition @error('email') field-invalid @enderror">
                    <span class="absolute right-3 top-2.5 text-lg" id="email-icon"></span>
                </div>
                <p class="text-xs mt-1 hidden" id="email-msg"></p>
            </div>

            {{-- Phone --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Phone Number <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <input type="text" name="phone" id="phone" value="{{ old('phone') }}"
                        placeholder="09XXXXXXXXX"
                        maxlength="11"
                        class="w-full px-4 py-2.5 pr-10 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 transition @error('phone') field-invalid @enderror">
                    <span class="absolute right-3 top-2.5 text-lg" id="phone-icon"></span>
                </div>
                <p class="text-xs mt-1 hidden" id="phone-msg"></p>
            </div>

            {{-- Password --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Password <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <input type="password" name="password" id="password"
                        placeholder="Create a strong password"
                        class="w-full px-4 py-2.5 pr-10 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 transition @error('password') field-invalid @enderror">
                    <button type="button" onclick="togglePassword('password','eye1')"
                        class="absolute right-3 top-2.5 text-gray-400 hover:text-gray-600">
                        <i class="fas fa-eye" id="eye1"></i>
                    </button>
                </div>

                {{-- Strength bar --}}
                <div class="mt-2">
                    <div class="flex gap-1 mb-1">
                        <div class="h-1.5 flex-1 rounded-full bg-gray-200 transition-all" id="bar1"></div>
                        <div class="h-1.5 flex-1 rounded-full bg-gray-200 transition-all" id="bar2"></div>
                        <div class="h-1.5 flex-1 rounded-full bg-gray-200 transition-all" id="bar3"></div>
                        <div class="h-1.5 flex-1 rounded-full bg-gray-200 transition-all" id="bar4"></div>
                    </div>
                    <p class="text-xs font-medium" id="strength-label"></p>
                </div>

                {{-- Requirements checklist --}}
                <ul class="mt-2 space-y-1">
                    <li class="text-xs flex items-center gap-1.5 hint-fail" id="hint-length">
                        <i class="fas fa-circle text-xs w-3"></i> At least 8 characters
                    </li>
                    <li class="text-xs flex items-center gap-1.5 hint-fail" id="hint-upper">
                        <i class="fas fa-circle text-xs w-3"></i> One uppercase letter (A-Z)
                    </li>
                    <li class="text-xs flex items-center gap-1.5 hint-fail" id="hint-number">
                        <i class="fas fa-circle text-xs w-3"></i> One number (0-9)
                    </li>
                    <li class="text-xs flex items-center gap-1.5 hint-fail" id="hint-special">
                        <i class="fas fa-circle text-xs w-3"></i> One special character (@$!%*#?&)
                    </li>
                </ul>
            </div>

            {{-- Confirm Password --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Confirm Password <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <input type="password" name="password_confirmation" id="password_confirmation"
                        placeholder="Repeat your password"
                        class="w-full px-4 py-2.5 pr-10 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 transition">
                    <button type="button" onclick="togglePassword('password_confirmation','eye2')"
                        class="absolute right-3 top-2.5 text-gray-400 hover:text-gray-600">
                        <i class="fas fa-eye" id="eye2"></i>
                    </button>
                </div>
                <p class="text-xs mt-1 hidden" id="confirm-msg"></p>
            </div>

            {{-- Role hidden --}}
            <input type="hidden" name="role" value="client">

            {{-- Purchase Intent: pre-selected property --}}
            @if(isset($property) && $property)
            <div class="bg-indigo-50 border border-indigo-200 rounded-xl p-4">
                <p class="text-xs font-semibold text-indigo-700 mb-2"><i class="fas fa-home mr-1"></i> You're registering to reserve:</p>
                <div class="flex items-center gap-3">
                    @if($property->image_main)
                        <img src="{{ asset($property->image_main) }}" class="w-14 h-14 rounded-lg object-cover flex-shrink-0">
                    @else
                        <div class="w-14 h-14 bg-indigo-200 rounded-lg flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-building text-indigo-500"></i>
                        </div>
                    @endif
                    <div>
                        <p class="font-semibold text-gray-800 text-sm">{{ $property->title }}</p>
                        <p class="text-xs text-gray-500">{{ $property->location ?? '' }}</p>
                        <p class="text-xs font-bold text-indigo-600">₱{{ number_format($property->price, 0) }}</p>
                    </div>
                </div>
                <input type="hidden" name="interested_property_id" value="{{ $property->id }}">
                <div class="mt-3">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Any notes or questions about this property? <span class="text-gray-400">(optional)</span></label>
                    <textarea name="purchase_notes" rows="2" placeholder="e.g. I'm interested in a flexible payment scheme..."
                        class="w-full border border-indigo-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-white">{{ old('purchase_notes') }}</textarea>
                </div>
            </div>
            @endif

            {{-- Info --}}
            <div class="bg-blue-50 border border-blue-100 rounded-xl p-3 text-xs text-blue-700">
                <i class="fas fa-info-circle mr-1"></i>
                You are registering as a <strong>Client</strong>. For agent or contractor accounts, contact an administrator.
            </div>

            {{-- Terms --}}
            <div class="flex items-start gap-3">
                <input type="checkbox" name="terms" id="terms" value="1"
                    class="mt-0.5 w-4 h-4 text-indigo-600 rounded border-gray-300 focus:ring-indigo-500">
                <label for="terms" class="text-sm text-gray-600">
                    I agree to the
                    <a href="{{ route('terms') }}" target="_blank" class="text-indigo-600 hover:underline font-medium">Terms & Conditions</a>
                    and <a href="{{ route('privacy') }}" target="_blank" class="text-indigo-600 hover:underline font-medium">Privacy Policy</a>
                </label>
            </div>
            <p class="text-xs text-red-500 hidden" id="terms-msg">You must agree to the Terms & Conditions.</p>

            {{-- Submit --}}
            <button type="submit" id="submit-btn"
                class="w-full bg-indigo-600 text-white py-3 rounded-xl font-semibold hover:bg-indigo-700 transition text-sm disabled:opacity-50 disabled:cursor-not-allowed">
                Create Account
            </button>
        </form>

        <p class="text-center text-sm text-gray-500 mt-6">
            Already have an account?
            <a href="{{ route('login') }}" class="text-indigo-600 hover:underline font-medium">Sign in</a>
        </p>
    </div>
</div>
</div>

{{-- Terms Modal --}}
<div id="terms-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-lg w-full p-6 max-h-96 overflow-y-auto">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-bold text-gray-900 text-lg">Terms & Conditions</h3>
            <button onclick="document.getElementById('terms-modal').classList.add('hidden')"
                class="text-gray-400 hover:text-gray-600"><i class="fas fa-times text-xl"></i></button>
        </div>
        <div class="text-sm text-gray-600 space-y-3 leading-relaxed">
            <p><strong>1. Account Registration</strong><br>By registering, you agree to provide accurate and complete information. Your account is subject to admin approval before activation.</p>
            <p><strong>2. Use of Service</strong><br>EstateFlow is a real estate management platform. You agree to use it only for lawful purposes related to property transactions.</p>
            <p><strong>3. Privacy</strong><br>Your personal information will be used solely for managing your property transactions and communications with agents.</p>
            <p><strong>4. Account Security</strong><br>You are responsible for maintaining the confidentiality of your account credentials.</p>
            <p><strong>5. Reservations</strong><br>Property reservations are subject to confirmation by our agents and management team.</p>
            <p><strong>6. Termination</strong><br>We reserve the right to deactivate accounts that violate these terms.</p>
        </div>
        <button onclick="document.getElementById('terms-modal').classList.add('hidden'); document.getElementById('terms').checked = true; validateTerms();"
            class="mt-6 w-full bg-indigo-600 text-white py-2.5 rounded-xl text-sm font-medium hover:bg-indigo-700 transition">
            I Agree & Close
        </button>
    </div>
</div>

<script>
// ── Toggle password visibility ──────────────────────────────
function togglePassword(id, iconId) {
    const f = document.getElementById(id);
    const i = document.getElementById(iconId);
    f.type = f.type === 'password' ? 'text' : 'password';
    i.classList.toggle('fa-eye');
    i.classList.toggle('fa-eye-slash');
}

// ── Helper: set field state ──────────────────────────────────
function setValid(inputId, iconId, msgId, msg) {
    const input = document.getElementById(inputId);
    const icon  = document.getElementById(iconId);
    const msgEl = document.getElementById(msgId);
    input.classList.remove('field-invalid'); input.classList.add('field-valid');
    icon.innerHTML  = '<i class="fas fa-check-circle text-green-500"></i>';
    msgEl.textContent = msg;
    msgEl.className = 'text-xs mt-1 text-green-600';
    msgEl.classList.remove('hidden');
}

function setInvalid(inputId, iconId, msgId, msg) {
    const input = document.getElementById(inputId);
    const icon  = document.getElementById(iconId);
    const msgEl = document.getElementById(msgId);
    input.classList.remove('field-valid'); input.classList.add('field-invalid');
    icon.innerHTML  = '<i class="fas fa-times-circle text-red-500"></i>';
    msgEl.textContent = msg;
    msgEl.className = 'text-xs mt-1 text-red-500';
    msgEl.classList.remove('hidden');
}

function clearState(inputId, iconId, msgId) {
    document.getElementById(inputId).classList.remove('field-valid','field-invalid');
    document.getElementById(iconId).innerHTML = '';
    document.getElementById(msgId).classList.add('hidden');
}

// ── First Name ─────────────────────────────────────────────
document.getElementById('first_name').addEventListener('input', function () {
    const v = this.value.trim();
    if (!v) { clearState('first_name','first_name-icon','first_name-msg'); return; }
    v.length < 2
        ? setInvalid('first_name','first_name-icon','first_name-msg','First name must be at least 2 characters.')
        : setValid('first_name','first_name-icon','first_name-msg','Looks good!');
});

// ── Last Name ────────────────────────────────────────────────
document.getElementById('last_name').addEventListener('input', function () {
    const v = this.value.trim();
    if (!v) { clearState('last_name','last_name-icon','last_name-msg'); return; }
    v.length < 2
        ? setInvalid('last_name','last_name-icon','last_name-msg','Last name must be at least 2 characters.')
        : setValid('last_name','last_name-icon','last_name-msg','Looks good!');
});

// ── Email ───────────────────────────────────────────────────
document.getElementById('email').addEventListener('input', function () {
    const v = this.value.trim();
    if (!v) { clearState('email','email-icon','email-msg'); return; }
    const ok = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v);
    ok ? setValid('email','email-icon','email-msg','Valid email address.')
       : setInvalid('email','email-icon','email-msg','Please enter a valid email address.');
});

// ── Phone ───────────────────────────────────────────────────
document.getElementById('phone').addEventListener('input', function () {
    const v = this.value.trim();
    if (!v) { clearState('phone','phone-icon','phone-msg'); return; }
    const ok = /^(09|\+639)\d{9}$/.test(v);
    ok ? setValid('phone','phone-icon','phone-msg','Valid Philippine mobile number.')
       : setInvalid('phone','phone-icon','phone-msg','Enter a valid PH number (e.g. 09171234567).');
});

// ── Password ────────────────────────────────────────────────
document.getElementById('password').addEventListener('input', function () {
    const v = this.value;
    const checks = {
        length:  v.length >= 8,
        upper:   /[A-Z]/.test(v),
        number:  /[0-9]/.test(v),
        special: /[@$!%*#?&]/.test(v),
    };

    // Update hints
    Object.entries(checks).forEach(([key, pass]) => {
        const el = document.getElementById('hint-' + key);
        const ic = el.querySelector('i');
        if (pass) {
            el.classList.add('hint-pass'); el.classList.remove('hint-fail');
            ic.classList.replace('fa-circle','fa-check-circle');
        } else {
            el.classList.add('hint-fail'); el.classList.remove('hint-pass');
            ic.classList.replace('fa-check-circle','fa-circle');
        }
    });

    // Strength bar
    const score = Object.values(checks).filter(Boolean).length;
    const bars   = ['bar1','bar2','bar3','bar4'];
    const colors = ['bg-red-400','bg-orange-400','bg-yellow-400','bg-green-500'];
    const labels = ['','Weak','Fair','Good','Strong'];
    const labelColors = ['','text-red-500','text-orange-500','text-yellow-600','text-green-600'];

    bars.forEach((b, i) => {
        const el = document.getElementById(b);
        el.className = 'h-1.5 flex-1 rounded-full transition-all ' + (i < score ? colors[score - 1] : 'bg-gray-200');
    });

    const lbl = document.getElementById('strength-label');
    lbl.textContent  = v.length ? labels[score] : '';
    lbl.className    = 'text-xs font-medium ' + (v.length ? labelColors[score] : '');

    // Also re-check confirm
    checkConfirm();
});

// ── Confirm Password ────────────────────────────────────────
function checkConfirm() {
    const pw  = document.getElementById('password').value;
    const cpw = document.getElementById('password_confirmation').value;
    const msg = document.getElementById('confirm-msg');
    if (!cpw) { msg.classList.add('hidden'); return; }
    if (pw === cpw) {
        msg.textContent = '✓ Passwords match.';
        msg.className   = 'text-xs mt-1 text-green-600';
        document.getElementById('password_confirmation').classList.add('field-valid');
        document.getElementById('password_confirmation').classList.remove('field-invalid');
    } else {
        msg.textContent = '✗ Passwords do not match.';
        msg.className   = 'text-xs mt-1 text-red-500';
        document.getElementById('password_confirmation').classList.add('field-invalid');
        document.getElementById('password_confirmation').classList.remove('field-valid');
    }
    msg.classList.remove('hidden');
}
document.getElementById('password_confirmation').addEventListener('input', checkConfirm);

// ── Terms ───────────────────────────────────────────────────
function validateTerms() {
    const checked = document.getElementById('terms').checked;
    const msg     = document.getElementById('terms-msg');
    msg.classList.toggle('hidden', checked);
}
document.getElementById('terms').addEventListener('change', validateTerms);

// ── Form submit validation ──────────────────────────────────
document.getElementById('register-form').addEventListener('submit', function (e) {
    let valid = true;

    // Trigger all validations
    ['first_name','last_name','email','phone'].forEach(id => {
        document.getElementById(id).dispatchEvent(new Event('input'));
        if (document.getElementById(id).classList.contains('field-invalid')) valid = false;
        if (!document.getElementById(id).value.trim()) valid = false;
    });

    const pw  = document.getElementById('password').value;
    const cpw = document.getElementById('password_confirmation').value;
    const pwOk = pw.length >= 8 && /[A-Z]/.test(pw) && /[0-9]/.test(pw) && /[@$!%*#?&]/.test(pw);
    if (!pwOk || pw !== cpw) valid = false;

    if (!document.getElementById('terms').checked) {
        validateTerms();
        valid = false;
    }

    if (!valid) {
        e.preventDefault();
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
});
</script>

</body>
</html>
