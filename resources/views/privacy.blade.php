<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Privacy Policy — EstateFlow</title>
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
            <div class="w-10 h-10 bg-green-100 rounded-xl flex items-center justify-center">
                <i class="fas fa-shield-alt text-green-600"></i>
            </div>
            <h1 class="text-3xl font-bold text-gray-900">Privacy Policy</h1>
        </div>
        <p class="text-gray-500 text-sm">Last updated: {{ date('F d, Y') }} &nbsp;·&nbsp; Effective immediately upon registration</p>
    </div>

    <div class="bg-green-50 border border-green-200 rounded-xl p-4 mb-8 text-sm text-green-700">
        <i class="fas fa-shield-alt mr-2"></i>
        EstateFlow is committed to protecting your personal information. This Privacy Policy explains what data we collect, how we use it, and your rights regarding your data under the <strong>Data Privacy Act of 2012 (Republic Act No. 10173)</strong> of the Philippines.
    </div>

    <div class="space-y-8 text-gray-700 text-sm leading-relaxed">

        {{-- 1 --}}
        <section>
            <h2 class="text-lg font-bold text-gray-900 mb-3 flex items-center gap-2">
                <span class="w-7 h-7 bg-green-600 text-white rounded-full flex items-center justify-center text-xs font-bold">1</span>
                Information We Collect
            </h2>
            <div class="pl-9 space-y-3">
                <p>We collect the following personal information when you register and use EstateFlow:</p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div class="bg-white border border-gray-200 rounded-xl p-4">
                        <p class="font-semibold text-gray-800 mb-2"><i class="fas fa-user text-indigo-500 mr-1"></i> Identity Information</p>
                        <ul class="space-y-1 text-gray-600">
                            <li>Full name</li>
                            <li>Email address</li>
                            <li>Phone number</li>
                            <li>Home address</li>
                            <li>Government-issued ID (type, number, expiry)</li>
                        </ul>
                    </div>
                    <div class="bg-white border border-gray-200 rounded-xl p-4">
                        <p class="font-semibold text-gray-800 mb-2"><i class="fas fa-file-alt text-indigo-500 mr-1"></i> Transaction Information</p>
                        <ul class="space-y-1 text-gray-600">
                            <li>Reservation details</li>
                            <li>Payment records and proof images</li>
                            <li>Uploaded documents</li>
                            <li>Pag-IBIG application details</li>
                            <li>Site viewing schedules</li>
                        </ul>
                    </div>
                    <div class="bg-white border border-gray-200 rounded-xl p-4">
                        <p class="font-semibold text-gray-800 mb-2"><i class="fas fa-desktop text-indigo-500 mr-1"></i> Technical Information</p>
                        <ul class="space-y-1 text-gray-600">
                            <li>Login timestamps</li>
                            <li>Last seen activity</li>
                            <li>Profile avatar</li>
                            <li>Account status and role</li>
                        </ul>
                    </div>
                    <div class="bg-white border border-gray-200 rounded-xl p-4">
                        <p class="font-semibold text-gray-800 mb-2"><i class="fas fa-comments text-indigo-500 mr-1"></i> Communication Data</p>
                        <ul class="space-y-1 text-gray-600">
                            <li>Messages sent through the platform</li>
                            <li>Inquiry submissions</li>
                            <li>Follow-up notes from agents</li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>

        {{-- 2 --}}
        <section>
            <h2 class="text-lg font-bold text-gray-900 mb-3 flex items-center gap-2">
                <span class="w-7 h-7 bg-green-600 text-white rounded-full flex items-center justify-center text-xs font-bold">2</span>
                How We Use Your Information
            </h2>
            <div class="pl-9 space-y-2">
                <p>Your personal information is used exclusively for the following purposes:</p>
                <ul class="list-disc list-inside space-y-1 pl-2">
                    <li>Processing and managing your property reservations</li>
                    <li>Verifying your identity for transaction purposes</li>
                    <li>Communicating with you about your account and reservations</li>
                    <li>Sending email notifications (registration confirmation, approval/rejection, reservation updates)</li>
                    <li>Facilitating Pag-IBIG and payment processing</li>
                    <li>Maintaining audit logs for legal and compliance purposes</li>
                    <li>Improving the platform's features and user experience</li>
                </ul>
                <p class="mt-2">We do <strong>not</strong> sell, rent, or share your personal information with third parties for marketing purposes.</p>
            </div>
        </section>

        {{-- 3 --}}
        <section>
            <h2 class="text-lg font-bold text-gray-900 mb-3 flex items-center gap-2">
                <span class="w-7 h-7 bg-green-600 text-white rounded-full flex items-center justify-center text-xs font-bold">3</span>
                Data Retention Policy
            </h2>
            <div class="pl-9 space-y-3">
                <p>We retain your personal data only for as long as necessary to fulfill the purposes outlined in this policy, or as required by law.</p>

                <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4">
                    <p class="font-semibold text-yellow-800 mb-2"><i class="fas fa-clock mr-1"></i> Backed-Out / Cancelled Transactions — 7-Day Grace Period</p>
                    <p class="text-yellow-700 mb-2">When a reservation is cancelled, your personal data enters a <strong>7-day grace period</strong>. During this time:</p>
                    <ul class="list-disc list-inside text-yellow-700 space-y-1 pl-2">
                        <li>Your data remains intact and the reservation can be reactivated</li>
                        <li>No data is deleted during this period</li>
                    </ul>
                </div>

                <div class="bg-red-50 border border-red-200 rounded-xl p-4">
                    <p class="font-semibold text-red-800 mb-2"><i class="fas fa-trash-alt mr-1"></i> After the Grace Period — Automatic Data Wipe</p>
                    <p class="text-red-700 mb-2">After 7 days with no reactivation, the following data is <strong>permanently deleted</strong>:</p>
                    <ul class="list-disc list-inside text-red-700 space-y-1 pl-2">
                        <li>Uploaded identification documents and files</li>
                        <li>Payment proof images</li>
                        <li>Personal contact details (phone, address)</li>
                        <li>Government ID information</li>
                        <li>Profile avatar / photo</li>
                    </ul>
                </div>

                <div class="bg-green-50 border border-green-200 rounded-xl p-4">
                    <p class="font-semibold text-green-800 mb-2"><i class="fas fa-check-circle mr-1"></i> What We Keep (Anonymized)</p>
                    <p class="text-green-700">An anonymized audit record is retained for legal compliance. This record contains <strong>no personally identifiable information</strong> — only transaction reference numbers and timestamps.</p>
                </div>

                <div class="overflow-x-auto mt-2">
                    <table class="w-full text-xs border border-gray-200 rounded-xl overflow-hidden">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="text-left px-4 py-2 font-semibold text-gray-600">Data Type</th>
                                <th class="text-left px-4 py-2 font-semibold text-gray-600">Active Account</th>
                                <th class="text-left px-4 py-2 font-semibold text-gray-600">Grace Period (7 days)</th>
                                <th class="text-left px-4 py-2 font-semibold text-gray-600">After Grace Period</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr class="bg-white">
                                <td class="px-4 py-2">Personal Info</td>
                                <td class="px-4 py-2 text-green-600">✓ Retained</td>
                                <td class="px-4 py-2 text-green-600">✓ Retained</td>
                                <td class="px-4 py-2 text-red-600">✗ Deleted</td>
                            </tr>
                            <tr class="bg-gray-50">
                                <td class="px-4 py-2">Uploaded Documents</td>
                                <td class="px-4 py-2 text-green-600">✓ Retained</td>
                                <td class="px-4 py-2 text-green-600">✓ Retained</td>
                                <td class="px-4 py-2 text-red-600">✗ Deleted</td>
                            </tr>
                            <tr class="bg-white">
                                <td class="px-4 py-2">Payment Records</td>
                                <td class="px-4 py-2 text-green-600">✓ Retained</td>
                                <td class="px-4 py-2 text-green-600">✓ Retained</td>
                                <td class="px-4 py-2 text-yellow-600">~ Anonymized</td>
                            </tr>
                            <tr class="bg-gray-50">
                                <td class="px-4 py-2">Audit Logs</td>
                                <td class="px-4 py-2 text-green-600">✓ Retained</td>
                                <td class="px-4 py-2 text-green-600">✓ Retained</td>
                                <td class="px-4 py-2 text-green-600">✓ Anonymized copy kept</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        {{-- 4 --}}
        <section>
            <h2 class="text-lg font-bold text-gray-900 mb-3 flex items-center gap-2">
                <span class="w-7 h-7 bg-green-600 text-white rounded-full flex items-center justify-center text-xs font-bold">4</span>
                Data Security
            </h2>
            <div class="pl-9 space-y-2">
                <p>We implement appropriate technical and organizational measures to protect your personal data against unauthorized access, alteration, disclosure, or destruction, including:</p>
                <ul class="list-disc list-inside space-y-1 pl-2">
                    <li>Encrypted password storage (bcrypt hashing)</li>
                    <li>SSL/TLS encrypted data transmission</li>
                    <li>Role-based access control — only authorized personnel can access your data</li>
                    <li>Audit logging of all significant system actions</li>
                    <li>Secure cloud database hosted on Supabase</li>
                </ul>
            </div>
        </section>

        {{-- 5 --}}
        <section>
            <h2 class="text-lg font-bold text-gray-900 mb-3 flex items-center gap-2">
                <span class="w-7 h-7 bg-green-600 text-white rounded-full flex items-center justify-center text-xs font-bold">5</span>
                Your Rights Under RA 10173
            </h2>
            <div class="pl-9 space-y-2">
                <p>Under the <strong>Data Privacy Act of 2012</strong>, you have the following rights:</p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-2">
                    <div class="bg-white border border-gray-200 rounded-xl p-3">
                        <p class="font-semibold text-gray-800"><i class="fas fa-eye text-indigo-500 mr-1"></i> Right to Access</p>
                        <p class="text-gray-600 mt-1">Request a copy of the personal data we hold about you.</p>
                    </div>
                    <div class="bg-white border border-gray-200 rounded-xl p-3">
                        <p class="font-semibold text-gray-800"><i class="fas fa-edit text-indigo-500 mr-1"></i> Right to Rectification</p>
                        <p class="text-gray-600 mt-1">Request correction of inaccurate or incomplete data.</p>
                    </div>
                    <div class="bg-white border border-gray-200 rounded-xl p-3">
                        <p class="font-semibold text-gray-800"><i class="fas fa-trash text-indigo-500 mr-1"></i> Right to Erasure</p>
                        <p class="text-gray-600 mt-1">Request deletion of your personal data, subject to legal retention requirements.</p>
                    </div>
                    <div class="bg-white border border-gray-200 rounded-xl p-3">
                        <p class="font-semibold text-gray-800"><i class="fas fa-ban text-indigo-500 mr-1"></i> Right to Object</p>
                        <p class="text-gray-600 mt-1">Object to the processing of your personal data in certain circumstances.</p>
                    </div>
                </div>
                <p class="mt-2">To exercise any of these rights, contact us at <strong class="text-indigo-600">villarosalina2026@gmail.com</strong>.</p>
            </div>
        </section>

        {{-- 6 --}}
        <section>
            <h2 class="text-lg font-bold text-gray-900 mb-3 flex items-center gap-2">
                <span class="w-7 h-7 bg-green-600 text-white rounded-full flex items-center justify-center text-xs font-bold">6</span>
                Email Communications
            </h2>
            <div class="pl-9 space-y-2">
                <p>By registering, you consent to receive transactional emails from EstateFlow, including:</p>
                <ul class="list-disc list-inside space-y-1 pl-2">
                    <li>Registration confirmation</li>
                    <li>Account approval or rejection notifications</li>
                    <li>Reservation confirmation and status updates</li>
                </ul>
                <p>These are transactional emails and are necessary for the operation of your account. They are not marketing emails.</p>
            </div>
        </section>

        {{-- 7 --}}
        <section>
            <h2 class="text-lg font-bold text-gray-900 mb-3 flex items-center gap-2">
                <span class="w-7 h-7 bg-green-600 text-white rounded-full flex items-center justify-center text-xs font-bold">7</span>
                Changes to This Policy
            </h2>
            <div class="pl-9">
                <p>We may update this Privacy Policy from time to time. Any changes will be posted on this page with an updated effective date. We encourage you to review this policy periodically.</p>
            </div>
        </section>

        {{-- Contact --}}
        <section class="bg-gray-100 rounded-xl p-5">
            <h2 class="text-base font-bold text-gray-900 mb-2"><i class="fas fa-envelope mr-2 text-green-500"></i>Data Privacy Contact</h2>
            <p>For any privacy-related concerns or to exercise your data rights, contact our Data Protection Officer at:</p>
            <p class="mt-2 font-medium text-indigo-600">villarosalina2026@gmail.com</p>
            <p class="text-xs text-gray-500 mt-1">Villa Rosalina Homes Corp. · Philippines</p>
        </section>

    </div>

    {{-- Footer links --}}
    <div class="mt-10 pt-6 border-t border-gray-200 flex flex-wrap items-center justify-between gap-4 text-sm text-gray-500">
        <p>© {{ date('Y') }} EstateFlow · Villa Rosalina Homes Corp.</p>
        <div class="flex gap-4">
            <a href="{{ route('terms') }}" class="text-indigo-600 hover:underline">Terms & Conditions</a>
            <a href="{{ route('home') }}" class="hover:text-gray-700 transition">Home</a>
            <a href="{{ route('register') }}" class="hover:text-gray-700 transition">Register</a>
        </div>
    </div>

</div>
</body>
</html>
