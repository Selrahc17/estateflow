<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AccountApproved extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public User $user, public bool $hasReservation = false) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Your Account Has Been Approved — EstateFlow');
    }

    public function content(): Content
    {
        return new Content(markdown: 'emails.account-approved');
    }
}
