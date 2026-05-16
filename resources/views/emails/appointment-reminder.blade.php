@component('mail::message')
# Appointment Reminder ⏰

Hi **{{ $reservation->client->first_name }}**,

@if($daysLeft === 1)
This is a **final reminder** — your property viewing appointment is **tomorrow!**
@else
This is a friendly reminder that your property viewing appointment is in **{{ $daysLeft }} days**.
@endif

@component('mail::panel')
**Property:** {{ $reservation->property->title }}
**Location:** {{ $reservation->property->location ?? 'N/A' }}
**Appointment Date:** {{ $reservation->reservation_date->format('F j, Y') }}
**Assigned Agent:** {{ $reservation->agent ? $reservation->agent->agent_code : 'To be assigned' }}
@endcomponent

## Reminders before your appointment:

- Bring a **valid government-issued ID**
- Arrive **on time** at the property location
- Prepare any **questions** you have about the property
- After the viewing, if you wish to proceed, you will need to submit your **Proof of Payment** for the reservation fee

@component('mail::button', ['url' => url('/client/reservations'), 'color' => 'indigo'])
View My Reservation
@endcomponent

Thanks,
**The EstateFlow Team**
@endcomponent
