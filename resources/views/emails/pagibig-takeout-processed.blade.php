@component('mail::message')
# Pag-IBIG Takeout Processed ✅

Hi **{{ $reservation->client->first_name }}**,

Your Pag-IBIG loan proceeds for **{{ $reservation->property->title }}** have been **released**.

@component('mail::panel')
**Property:** {{ $reservation->property->title }}
**Takeout Amount:** ₱{{ number_format($reservation->pagibig_takeout_amount, 2) }}
**Takeout Date:** {{ \Carbon\Carbon::parse($reservation->pagibig_takeout_at)->format('F j, Y') }}
@endcomponent

The loan amount has been remitted to the developer. The next step is the start of your **monthly amortization** payments to Pag-IBIG (HDMF).

You will receive another notification once your amortization schedule begins.

@component('mail::button', ['url' => url('/client/reservations'), 'color' => 'indigo'])
View My Reservations
@endcomponent

Thanks,
**The EstateFlow Team**
@endcomponent
