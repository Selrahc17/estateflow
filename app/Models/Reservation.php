<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    protected $fillable = [
        'property_id', 'client_id', 'agent_id', 'reservation_date',
        'expiry_date', 'reservation_fee', 'status', 'notes',
        'pagibig_status', 'pagibig_reference', 'cancelled_at', 'data_wiped_at',
    ];

    public const PAGIBIG_STATUSES = [
        'not_applied' => 'Not Applied',
        'applied'     => 'Applied',
        'verified'    => 'Verified',
        'approved'    => 'Approved',
        'released'    => 'Released',
    ];

    protected $casts = [
        'reservation_date' => 'date',
        'expiry_date'      => 'date',
        'reservation_fee'  => 'decimal:2',
        'cancelled_at'     => 'datetime',
        'data_wiped_at'    => 'datetime',
    ];

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function documents()
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    public function siteViewingSchedules()
    {
        return $this->hasMany(SiteViewingSchedule::class);
    }

    // Auto-set cancelled_at when status changes to cancelled
    protected static function booted()
    {
        static::updating(function ($reservation) {
            if ($reservation->isDirty('status') && $reservation->status === 'cancelled' && !$reservation->cancelled_at) {
                $reservation->cancelled_at = now();
            }
        });
    }

    public function isPastGracePeriod(): bool
    {
        if (!$this->cancelled_at) return false;
        return $this->cancelled_at->addDays(config('retention.grace_period_days', 7))->isPast();
    }

    public function gracePeriodEndsAt(): ?\Carbon\Carbon
    {
        if (!$this->cancelled_at) return null;
        return $this->cancelled_at->addDays(config('retention.grace_period_days', 7));
    }
}
