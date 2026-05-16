<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AccountRejected extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public User $user, public ?string $reason = null) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Your Registration Request Was Not Approved — EstateFlow');
    }

    public function content(): Content
    {
        return new Content(markdown: 'emails.account-rejected');
    }
}
