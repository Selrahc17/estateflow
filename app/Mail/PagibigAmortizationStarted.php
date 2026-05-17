<?php

namespace App\Mail;

use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PagibigAmortizationStarted extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Reservation $reservation) {}

    public function build()
    {
        return $this->subject('Monthly Amortization Active — Pag-IBIG')
                    ->view('emails.pagibig-amortization-started');
    }
}
