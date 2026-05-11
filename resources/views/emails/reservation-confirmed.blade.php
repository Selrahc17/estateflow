@component('mail::message')
# Reservation Confirmed 🎉

Hi **{{ $reservation->client->first_name }}**,

Great news! Your reservation for **{{ $reservation->property->title }}** has been confirmed by our team.

@component('mail::panel')
**Property:** {{ $reservation->property->title }}
**Location:** {{ $reservation->property->location ?? 'N/A' }}
**Reservation Date:** {{ $reservation->reservation_date->format('F j, Y') }}
**Reservation Fee:** ₱{{ number_format($reservation->reservation_fee, 2) }}
**Status:** Confirmed ✅
@endcomponent

You can log in to your EstateFlow account to view your reservation details, track payments, and download related documents.

@component('mail::button', ['url' => url('/client/reservations'), 'color' => 'indigo'])
View My Reservations
@endcomponent

If you have any questions, feel free to reach out to your assigned agent{{ $reservation->agent ? ' (' . $reservation->agent->full_name . ')' : '' }} or contact our support team.

Thanks,
**The EstateFlow Team**
@endcomponent
