<?php

namespace App\Mail;

use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PagibigLoaReceived extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Reservation $reservation) {}

    public function build()
    {
        return $this->subject('Pag-IBIG Loan Approved — Letter of Approval Received')
                    ->view('emails.pagibig-loa-received');
    }
}
