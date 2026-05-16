@component('mail::message')
# Appointment Scheduled 📅

Hi **{{ $reservation->client->first_name }}**,

Your property viewing appointment has been successfully scheduled. Here are your appointment details:

@component('mail::panel')
**Property:** {{ $reservation->property->title }}
**Location:** {{ $reservation->property->location ?? 'N/A' }}
**Appointment Date:** {{ $reservation->reservation_date->format('F j, Y') }}
**Assigned Agent:** {{ $reservation->agent ? $reservation->agent->agent_code : 'To be assigned' }}
**Status:** Pending Viewing 🕐
@endcomponent

## What to expect on your appointment day:

1. **Arrive on time** at the property location
2. **Bring a valid ID** for verification
3. **Tour the property** with your assigned agent
4. **Ask questions** — this is your chance to inspect everything
5. **After the viewing**, if you decide to proceed, you will be asked to submit your **Proof of Payment** for the reservation fee

> You will receive reminder emails **3 days**, **2 days**, and **1 day** before your appointment.

@component('mail::button', ['url' => url('/client/reservations'), 'color' => 'indigo'])
View My Reservation
@endcomponent

If you need to reschedule or have questions, please contact your agent or our support team.

Thanks,
**The EstateFlow Team**
@endcomponent
