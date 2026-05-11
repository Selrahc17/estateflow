<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Staff extends Model
{
    protected $table = 'staff';

    protected $fillable = [
        'user_id', 'company_name', 'contact_person', 'email', 'phone',
        'phone_alt', 'address', 'license_number', 'tax_id',
        'type', 'specialization', 'status', 'notes',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function projects()
    {
        return $this->hasMany(Project::class, 'staff_id');
    }

    public function resources()
    {
        return $this->hasMany(Resource::class, 'supplier_id');
    }
}
