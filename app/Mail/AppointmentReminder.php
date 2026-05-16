<?php

namespace App\Mail;

use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AppointmentReminder extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Reservation $reservation, public int $daysLeft) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: "Reminder: Your Appointment is in {$this->daysLeft} Day(s) — EstateFlow");
    }

    public function content(): Content
    {
        return new Content(markdown: 'emails.appointment-reminder');
    }
}
