@component('mail::message')
# Account Approved 🎉

Hi **{{ $user->name }}**,

Great news! Your registration request for **EstateFlow** has been reviewed and **approved** by our admin team.

@if($hasReservation)
@component('mail::panel')
🏡 **Your reservation has been automatically created!**
Based on the property you selected during registration, a **pending reservation** has been set up for you. You can view it as soon as you log in — no need to browse again.
@endcomponent

You can now log in and go directly to **My Reservations** to review your reservation details, complete your documents, and track your transaction.

@component('mail::button', ['url' => url('/client/reservations'), 'color' => 'green'])
View My Reservation
@endcomponent

@else
You can now log in to your account and start exploring available properties, track your reservations, and manage your documents.

@component('mail::button', ['url' => url('/login'), 'color' => 'green'])
Log In to EstateFlow
@endcomponent
@endif

@component('mail::panel')
**Email:** {{ $user->email }}
**Account Status:** Active ✅
@endcomponent

If you have any questions or need assistance, feel free to contact our support team.

Thanks,
**The EstateFlow Team**
@endcomponent
