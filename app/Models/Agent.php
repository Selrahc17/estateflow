<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Agent extends Model
{
    protected $fillable = [
        'user_id', 'agent_code', 'first_name', 'last_name', 'email', 'phone',
        'license_number', 'address', 'commission_rate', 'status', 'notes',
    ];

    protected static function booted(): void
    {
        static::creating(function ($agent) {
            if (empty($agent->agent_code)) {
                $last = static::orderByDesc('id')->first();
                $next = $last ? ((int) substr($last->agent_code, 4)) + 1 : 1;
                $agent->agent_code = 'AGT-' . str_pad($next, 3, '0', STR_PAD_LEFT);
            }
        });
    }

    protected $casts = [
        'commission_rate' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }
}
