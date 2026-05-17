@component('mail::message')
# Overdue Installment ⚠️

Hi **{{ $schedule->reservation->client->first_name }}**,

Your equity installment payment is **overdue**. Please settle this immediately to avoid penalties.

@component('mail::panel')
**Property:** {{ $schedule->reservation->property->title ?? '—' }}
**Installment #:** {{ $schedule->installment_number }}
**Amount Due:** ₱{{ number_format($schedule->amount_due, 2) }}
**Due Date:** {{ $schedule->due_date->format('F j, Y') }}
**Balance:** ₱{{ number_format(max(0, (float)$schedule->amount_due - (float)$schedule->amount_paid), 2) }}
@endcomponent

Please log in to your EstateFlow account to view your payment schedule and coordinate with our finance team.

@component('mail::button', ['url' => url('/client/reservations'), 'color' => 'red'])
View My Schedule
@endcomponent

Thanks,
**The EstateFlow Team**
@endcomponent
