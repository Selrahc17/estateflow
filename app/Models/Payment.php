<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'reservation_id', 'client_id', 'agent_id', 'payment_type',
        'amount', 'currency', 'payment_method', 'reference_number',
        'receipt_number', 'payment_date', 'description', 'status',
        'proof_image', 'payment_schedule_id',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount'       => 'decimal:2',
    ];

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function paymentSchedule()
    {
        return $this->belongsTo(PaymentSchedule::class);
    }

    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }
}
