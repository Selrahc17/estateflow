<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgentCommission extends Model
{
    protected $fillable = [
        'reservation_id', 'agent_id', 'property_price',
        'commission_rate', 'commission_amount', 'status',
        'approved_at', 'paid_at', 'or_number', 'notes', 'created_by',
    ];

    protected $casts = [
        'property_price'    => 'decimal:2',
        'commission_rate'   => 'decimal:2',
        'commission_amount' => 'decimal:2',
        'approved_at'       => 'date',
        'paid_at'           => 'date',
    ];

    public const STATUS_COLORS = [
        'pending'   => 'bg-yellow-100 text-yellow-700',
        'approved'  => 'bg-blue-100 text-blue-700',
        'paid'      => 'bg-green-100 text-green-700',
        'cancelled' => 'bg-red-100 text-red-700',
    ];

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
