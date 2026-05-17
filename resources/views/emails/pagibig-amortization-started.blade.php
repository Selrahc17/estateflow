@component('mail::message')
# Monthly Amortization Active 🏠

Hi **{{ $reservation->client->first_name }}**,

Your monthly Pag-IBIG amortization for **{{ $reservation->property->title }}** is now **active**.

@component('mail::panel')
**Property:** {{ $reservation->property->title }}
**Monthly Amortization:** ₱{{ number_format($reservation->pagibig_monthly_amortization, 2) }}
**Start Date:** {{ \Carbon\Carbon::parse($reservation->pagibig_amortization_start)->format('F j, Y') }}
@endcomponent

**Important:** Your monthly amortization payments must be paid **directly to Pag-IBIG (HDMF)** — not to EstateFlow. You can pay via:
- Virtual Pag-IBIG (viртуальный pagibig.gov.ph)
- Any Pag-IBIG branch
- Accredited payment centers (SM, Bayad Center, etc.)

You can view your full amortization schedule in your EstateFlow account.

@component('mail::button', ['url' => url('/client/reservations'), 'color' => 'indigo'])
View Amortization Schedule
@endcomponent

Thanks,
**The EstateFlow Team**
@endcomponent
