<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class PagibigAmortizationSchedule extends Model
{
    protected $fillable = [
        'reservation_id', 'month_number', 'due_date',
        'amount_due', 'amount_paid', 'status',
        'receipt_number', 'reference_number', 'paid_at', 'notes',
    ];

    protected $casts = [
        'due_date'   => 'date',
        'paid_at'    => 'date',
        'amount_due' => 'decimal:2',
        'amount_paid'=> 'decimal:2',
    ];

    public const STATUS_COLORS = [
        'upcoming'       => 'bg-gray-100 text-gray-600',
        'due'            => 'bg-yellow-100 text-yellow-700',
        'paid'           => 'bg-green-100 text-green-700',
        'partially_paid' => 'bg-blue-100 text-blue-700',
        'overdue'        => 'bg-red-100 text-red-700',
    ];

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

    public function syncStatus(): void
    {
        $paid = (float) $this->amount_paid;
        $due  = (float) $this->amount_due;

        if ($paid >= $due) {
            $status = 'paid';
        } elseif ($paid > 0) {
            $status = 'partially_paid';
        } elseif ($this->due_date->isPast()) {
            $status = 'overdue';
        } elseif ($this->due_date->lte(Carbon::today()->addDays(7))) {
            $status = 'due';
        } else {
            $status = 'upcoming';
        }

        $this->update([
            'status'  => $status,
            'paid_at' => $paid >= $due ? ($this->paid_at ?? now()->toDateString()) : null,
        ]);
    }
}
