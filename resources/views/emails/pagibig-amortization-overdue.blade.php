@component('mail::message')
# Overdue Pag-IBIG Amortization ⚠️

Hi **{{ $schedule->reservation->client->first_name }}**,

Your Pag-IBIG monthly amortization payment is **overdue**. Please pay directly to Pag-IBIG (HDMF) immediately to avoid penalties on your housing loan.

@component('mail::panel')
**Property:** {{ $schedule->reservation->property->title ?? '—' }}
**Month #:** {{ $schedule->month_number }}
**Amount Due:** ₱{{ number_format($schedule->amount_due, 2) }}
**Due Date:** {{ $schedule->due_date->format('F j, Y') }}
@endcomponent

**Where to pay:**
- Virtual Pag-IBIG: pagibig.gov.ph
- Any Pag-IBIG branch
- Accredited payment centers (SM, Bayad Center, etc.)

@component('mail::button', ['url' => url('/client/reservations'), 'color' => 'red'])
View Amortization Schedule
@endcomponent

Thanks,
**The EstateFlow Team**
@endcomponent
