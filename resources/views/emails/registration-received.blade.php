@component('mail::message')
# Welcome to EstateFlow! 🏡

Hi **{{ $user->name }}**,

Thank you for registering with **EstateFlow**. We have successfully received your registration request.

@component('mail::panel')
**Name:** {{ $user->name }}
**Email:** {{ $user->email }}
**Status:** Pending Admin Approval ⏳
@endcomponent

## What happens next?

1. **Admin Review** — Our admin team will review your registration details.
2. **Account Activation** — Once approved, your account will be activated.
3. **Email Notification** — You will receive an email confirming your approval so you can log in.

> Please note that account activation may take up to **1–2 business days**. You will be notified via email once your account is ready.

If you did not register for an EstateFlow account or believe this was a mistake, please ignore this email or contact our support team.

@component('mail::button', ['url' => url('/login'), 'color' => 'indigo'])
Go to Login Page
@endcomponent

Thanks,
**The EstateFlow Team**
@endcomponent
