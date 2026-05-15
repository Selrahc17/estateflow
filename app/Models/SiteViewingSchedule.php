<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteViewingSchedule extends Model
{
    protected $fillable = [
        'reservation_id', 'client_id', 'preferred_date',
        'preferred_time', 'notes', 'status', 'confirmed_by', 'confirmed_at',
    ];

    protected $casts = [
        'preferred_date' => 'date',
        'confirmed_at'   => 'datetime',
    ];

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function confirmedBy()
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }
}
