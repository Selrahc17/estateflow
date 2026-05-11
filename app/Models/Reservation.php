<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    protected $fillable = [
        'property_id', 'client_id', 'agent_id', 'reservation_date',
        'expiry_date', 'reservation_fee', 'status', 'notes',
        'pagibig_status', 'pagibig_reference',
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
}
