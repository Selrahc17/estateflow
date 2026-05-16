<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terms & Conditions — EstateFlow</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50 font-sans">

{{-- Navbar --}}
<nav class="bg-white shadow-sm sticky top-0 z-50">
    <div class="max-w-4xl mx-auto px-6 py-4 flex items-center justify-between">
        <a href="{{ route('home') }}" class="flex items-center gap-2">
            <img src="{{ asset('logo.png') }}" alt="EstateFlow" class="w-8 h-8 object-contain">
            <span class="font-bold text-gray-900 text-lg">EstateFlow</span>
        </a>
        <a href="{{ url()->previous() }}" class="text-sm text-gray-500 hover:text-indigo-600 transition flex items-center gap-1">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>
</nav>

<div class="max-w-4xl mx-auto px-6 py-12">

    {{-- Header --}}
    <div class="mb-10">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-10 h-10 bg-indigo-100 rounded-xl flex items-center justify-center">
                <i class="fas fa-file-contract text-indigo-600"></i>
            </div>
            <h1 class="text-3xl font-bold text-gray-900">Terms & Conditions</h1>
        </div>
        <p class="text-gray-500 text-sm">Last updated: {{ date('F d, Y') }} &nbsp;·&nbsp; Effective immediately upon registration</p>
    </div>

    <div class="bg-indigo-50 border border-indigo-200 rounded-xl p-4 mb-8 text-sm text-indigo-700">
        <i class="fas fa-info-circle mr-2"></i>
        By registering and using EstateFlow, you agree to be bound by these Terms & Conditions. Please read them carefully before creating an account.
    </div>

    <div class="space-y-8 text-gray-700 text-sm leading-relaxed">

        {{-- 1 --}}
        <section>
            <h2 class="text-lg font-bold text-gray-900 mb-3 flex items-center gap-2">
                <span class="w-7 h-7 bg-indigo-600 text-white rounded-full flex items-center justify-center text-xs font-bold">1</span>
                Account Registration & Approval
            </h2>
            <div class="pl-9 space-y-2">
                <p>Only individuals 18 years of age or older may register for an EstateFlow account.</p>
                <p>All self-registered accounts are subject to <strong>admin review and approval</strong> before access is granted. EstateFlow reserves the right to reject any registration without obligation to provide a reason.</p>
                <p>You agree to provide accurate, current, and complete information during registration and to update such information to keep it accurate.</p>
                <p>You are responsible for maintaining the confidentiality of your login credentials. Any activity under your account is your responsibility.</p>
            </div>
        </section>

        {{-- 2 --}}
        <section>
            <h2 class="text-lg font-bold text-gray-900 mb-3 flex items-center gap-2">
                <span class="w-7 h-7 bg-indigo-600 text-white rounded-full flex items-center justify-center text-xs font-bold">2</span>
                Use of the Platform
            </h2>
            <div class="pl-9 space-y-2">
                <p>EstateFlow is a real estate management platform operated by <strong>Villa Rosalina Homes Corp.</strong> It is intended solely for lawful property transactions, reservations, and related communications.</p>
                <p>You agree <strong>not</strong> to:</p>
                <ul class="list-disc list-inside space-y-1 pl-2">
                    <li>Use the platform for any fraudulent or unlawful purpose</li>
                    <li>Submit false or misleading information</li>
                    <li>Attempt to gain unauthorized access to any part of the system</li>
                    <li>Interfere with or disrupt the platform's operations</li>
                    <li>Share your account credentials with any third party</li>
                </ul>
            </div>
        </section>

        {{-- 3 --}}
        <section>
            <h2 class="text-lg font-bold text-gray-900 mb-3 flex items-center gap-2">
                <span class="w-7 h-7 bg-indigo-600 text-white rounded-full flex items-center justify-center text-xs font-bold">3</span>
                Property Reservations
            </h2>
            <div class="pl-9 space-y-2">
                <p>Submitting a reservation request does not guarantee property availability or ownership. All reservations are subject to confirmation by an authorized EstateFlow agent.</p>
                <p>Reservation fees, once paid, are subject to the terms agreed upon at the time of reservation. Refund policies are governed by the separate Reservation Agreement.</p>
                <p>EstateFlow reserves the right to cancel any reservation that violates these terms or involves fraudulent activity.</p>
            </div>
        </section>

        {{-- 4 --}}
        <section>
            <h2 class="text-lg font-bold text-gray-900 mb-3 flex items-center gap-2">
                <span class="w-7 h-7 bg-indigo-600 text-white rounded-full flex items-center justify-center text-xs font-bold">4</span>
                Data Retention & Backed-Out Transactions
            </h2>
            <div class="pl-9 space-y-2">
                <p>When a reservation is <strong>cancelled or backed out</strong>, the following data retention policy applies:</p>
                <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 my-3">
                    <p class="font-semibold text-yellow-800 mb-2"><i class="fas fa-clock mr-1"></i> 7-Day Grace Period</p>
                    <p class="text-yellow-700">After cancellation, your personal data is retained for <strong>7 calendar days</strong>. During this period, you or an agent may reactivate the reservation.</p>
                </div>
                <p>After the 7-day grace period, the following data will be <strong>permanently and irreversibly deleted</strong>:</p>
                <ul class="list-disc list-inside space-y-1 pl-2">
                    <li>Uploaded identification documents</li>
                    <li>Payment proof images</li>
                    <li>Personal contact information (phone, address)</li>
                    <li>Government ID details (type, number, expiry)</li>
                    <li>Profile avatar</li>
                </ul>
                <p class="mt-2">An <strong>anonymized audit record</strong> will be retained for legal and compliance purposes. This record contains no personally identifiable information.</p>
                <p>By registering, you acknowledge and consent to this data wipe process in the event of a backed-out transaction.</p>
            </div>
        </section>

        {{-- 5 --}}
        <section>
            <h2 class="text-lg font-bold text-gray-900 mb-3 flex items-center gap-2">
                <span class="w-7 h-7 bg-indigo-600 text-white rounded-full flex items-center justify-center text-xs font-bold">5</span>
                Account Suspension & Termination
            </h2>
            <div class="pl-9 space-y-2">
                <p>EstateFlow administrators reserve the right to <strong>suspend, deactivate, or permanently delete</strong> any account that:</p>
                <ul class="list-disc list-inside space-y-1 pl-2">
                    <li>Violates these Terms & Conditions</li>
                    <li>Provides false registration information</li>
                    <li>Engages in fraudulent or abusive behavior</li>
                    <li>Has been inactive for an extended period</li>
                </ul>
                <p>Upon termination, your access to the platform will be revoked and your personal data will be handled in accordance with our Privacy Policy.</p>
            </div>
        </section>

        {{-- 6 --}}
        <section>
            <h2 class="text-lg font-bold text-gray-900 mb-3 flex items-center gap-2">
                <span class="w-7 h-7 bg-indigo-600 text-white rounded-full flex items-center justify-center text-xs font-bold">6</span>
                Intellectual Property
            </h2>
            <div class="pl-9 space-y-2">
                <p>All content on EstateFlow — including logos, property listings, images, and software — is the property of Villa Rosalina Homes Corp. and is protected by applicable intellectual property laws.</p>
                <p>You may not reproduce, distribute, or create derivative works from any content on this platform without prior written consent.</p>
            </div>
        </section>

        {{-- 7 --}}
        <section>
            <h2 class="text-lg font-bold text-gray-900 mb-3 flex items-center gap-2">
                <span class="w-7 h-7 bg-indigo-600 text-white rounded-full flex items-center justify-center text-xs font-bold">7</span>
                Limitation of Liability
            </h2>
            <div class="pl-9 space-y-2">
                <p>EstateFlow and Villa Rosalina Homes Corp. shall not be liable for any indirect, incidental, or consequential damages arising from your use of the platform.</p>
                <p>We do not guarantee uninterrupted or error-free access to the platform and are not responsible for any loss of data due to technical failures beyond our control.</p>
            </div>
        </section>

        {{-- 8 --}}
        <section>
            <h2 class="text-lg font-bold text-gray-900 mb-3 flex items-center gap-2">
                <span class="w-7 h-7 bg-indigo-600 text-white rounded-full flex items-center justify-center text-xs font-bold">8</span>
                Changes to These Terms
            </h2>
            <div class="pl-9 space-y-2">
                <p>We reserve the right to update these Terms & Conditions at any time. Changes will be posted on this page with an updated effective date. Continued use of the platform after changes constitutes acceptance of the revised terms.</p>
            </div>
        </section>

        {{-- 9 --}}
        <section>
            <h2 class="text-lg font-bold text-gray-900 mb-3 flex items-center gap-2">
                <span class="w-7 h-7 bg-indigo-600 text-white rounded-full flex items-center justify-center text-xs font-bold">9</span>
                Governing Law
            </h2>
            <div class="pl-9">
                <p>These Terms & Conditions are governed by the laws of the <strong>Republic of the Philippines</strong>. Any disputes shall be resolved in the appropriate courts of the Philippines.</p>
            </div>
        </section>

        {{-- Contact --}}
        <section class="bg-gray-100 rounded-xl p-5">
            <h2 class="text-base font-bold text-gray-900 mb-2"><i class="fas fa-envelope mr-2 text-indigo-500"></i>Contact Us</h2>
            <p>If you have questions about these Terms & Conditions, please contact us at:</p>
            <p class="mt-2 font-medium text-indigo-600">villarosalina2026@gmail.com</p>
        </section>

    </div>

    {{-- Footer links --}}
    <div class="mt-10 pt-6 border-t border-gray-200 flex flex-wrap items-center justify-between gap-4 text-sm text-gray-500">
        <p>© {{ date('Y') }} EstateFlow · Villa Rosalina Homes Corp.</p>
        <div class="flex gap-4">
            <a href="{{ route('privacy') }}" class="text-indigo-600 hover:underline">Privacy Policy</a>
            <a href="{{ route('home') }}" class="hover:text-gray-700 transition">Home</a>
            <a href="{{ route('register') }}" class="hover:text-gray-700 transition">Register</a>
        </div>
    </div>

</div>
</body>
</html>
