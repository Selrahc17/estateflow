<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $fillable = [
        'user_id', 'first_name', 'last_name', 'email', 'phone',
        'phone_alt', 'address', 'id_type', 'id_number', 'id_expiry',
        'hdmf_mid', 'monthly_income',
        'status', 'notes', 'interested_property_id', 'purchase_notes',
    ];

    public function interestedProperty()
    {
        return $this->belongsTo(\App\Models\Property::class, 'interested_property_id');
    }

    protected $casts = [
        'id_expiry'      => 'date',
        'monthly_income' => 'decimal:2',
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
