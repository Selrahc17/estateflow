@component('mail::message')
# Your Personal Data Has Been Deleted

Hi **{{ $clientFirstName }}**,

This is to confirm that your personal information associated with your cancelled reservation has been permanently deleted from our system as part of our data retention policy.

@component('mail::panel')
**Property:** {{ $propertyTitle }}
**Reservation Cancelled:** {{ $cancellationDate }}
**Data Deleted On:** {{ $wipedDate }}
@endcomponent

The following personal data has been removed from our records:

- Full name
- Email address
- Mobile number
- Home address
- Employment details
- Co-borrower details (if any)
- Uploaded documents and files

This action is irreversible. If you wish to make a new reservation in the future, you will need to register again.

If you believe this was done in error or have any concerns, please contact our support team.

Thanks,
**The EstateFlow Team**
@endcomponent
