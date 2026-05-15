<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    protected $fillable = [
        'name', 'email', 'phone', 'source', 'status',
        'assigned_agent_id', 'interested_property_id', 'converted_client_id',
        'budget_min', 'budget_max', 'preferred_location', 'notes', 'last_contacted_at',
    ];

    protected $casts = [
        'last_contacted_at' => 'datetime',
        'budget_min'        => 'decimal:2',
        'budget_max'        => 'decimal:2',
    ];

    public function assignedAgent()
    {
        return $this->belongsTo(Agent::class, 'assigned_agent_id');
    }

    public function interestedProperty()
    {
        return $this->belongsTo(Property::class, 'interested_property_id');
    }

    public function convertedClient()
    {
        return $this->belongsTo(Client::class, 'converted_client_id');
    }
}
