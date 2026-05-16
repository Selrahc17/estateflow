<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class PaymentSchedule extends Model
{
    protected $fillable = [
        'reservation_id', 'installment_number', 'due_date',
        'amount_due', 'amount_paid', 'status',
        'receipt_number', 'receipt_path', 'proof_path',
        'proof_uploaded_at', 'paid_at', 'notes',
    ];

    protected $casts = [
        'due_date'           => 'date',
        'amount_due'         => 'decimal:2',
        'amount_paid'        => 'decimal:2',
        'proof_uploaded_at'  => 'datetime',
        'paid_at'            => 'datetime',
    ];

    public const STATUSES = [
        'upcoming'      => 'Upcoming',
        'due'           => 'Due',
        'paid'          => 'Paid',
        'partially_paid'=> 'Partially Paid',
        'overdue'       => 'Overdue',
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

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function getBalanceAttribute(): float
    {
        // Use DB computed value if loaded, otherwise calculate
        return isset($this->attributes['balance'])
            ? (float) $this->attributes['balance']
            : max(0, (float) $this->amount_due - (float) $this->amount_paid);
    }

    // Recompute status based on dates and amounts
    public function syncStatus(): void
    {
        $paid    = (float) $this->amount_paid;
        $due     = (float) $this->amount_due;
        $dueDate = $this->due_date;

        if ($paid >= $due) {
            $status = 'paid';
        } elseif ($paid > 0) {
            $status = 'partially_paid';
        } elseif ($dueDate->isPast()) {
            $status = 'overdue';
        } elseif ($dueDate->isToday() || $dueDate->lte(Carbon::today()->addDays(7))) {
            $status = 'due';
        } else {
            $status = 'upcoming';
        }

        $this->update([
            'status'  => $status,
            'paid_at' => $paid >= $due ? ($this->paid_at ?? now()) : null,
        ]);
    }
}
