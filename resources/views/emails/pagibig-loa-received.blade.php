@component('mail::message')
# Pag-IBIG Loan Approved 🎉

Hi **{{ $reservation->client->first_name }}**,

Great news! Your Pag-IBIG housing loan for **{{ $reservation->property->title }}** has been **approved** by HDMF.

@component('mail::panel')
**Property:** {{ $reservation->property->title }}
**LOA Number:** {{ $reservation->pagibig_loa_number }}
**Approval Date:** {{ now()->format('F j, Y') }}
@endcomponent

Your Letter of Approval (LOA) has been received. The next step is the **Takeout** — our finance team will process the release of your loan proceeds from Pag-IBIG.

You will be notified once the takeout has been processed.

@component('mail::button', ['url' => url('/client/reservations'), 'color' => 'indigo'])
View My Reservations
@endcomponent

Thanks,
**The EstateFlow Team**
@endcomponent
