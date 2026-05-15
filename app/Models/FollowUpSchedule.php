<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FollowUpSchedule extends Model
{
    protected $fillable = [
        'client_id', 'agent_id', 'reservation_id',
        'follow_up_date', 'follow_up_time', 'type',
        'notes', 'status', 'completed_at',
    ];

    protected $casts = [
        'follow_up_date' => 'date',
        'completed_at'   => 'datetime',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }
}
