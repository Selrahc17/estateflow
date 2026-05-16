@component('mail::message')
# Registration Request Not Approved

Hi **{{ $user->name }}**,

Thank you for your interest in **EstateFlow**. After reviewing your registration request, we regret to inform you that your account has **not been approved** at this time.

@if($reason)
@component('mail::panel')
**Reason:** {{ $reason }}
@endcomponent
@endif

**What you can do next:**

- Review the reason above and ensure all your information is accurate
- Re-submit a new registration request at [EstateFlow Registration]({{ url('/register') }})
- Contact our support team if you believe this decision was made in error

@component('mail::button', ['url' => url('/register'), 'color' => 'red'])
Re-Submit Registration
@endcomponent

We appreciate your understanding and hope to assist you in the future.

Thanks,
**The EstateFlow Team**
@endcomponent
