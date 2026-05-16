<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DataWiped extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $clientFirstName,
        public string $propertyTitle,
        public string $cancellationDate,
        public string $wipedDate
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Your Personal Data Has Been Deleted — EstateFlow');
    }

    public function content(): Content
    {
        return new Content(markdown: 'emails.data-wiped');
    }
}
