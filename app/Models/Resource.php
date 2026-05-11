<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Resource extends Model
{
    protected $fillable = [
        'project_id', 'name', 'type', 'description', 'unit',
        'quantity', 'unit_price', 'currency', 'supplier_id',
        'delivery_date', 'status', 'notes',
    ];

    protected $casts = [
        'delivery_date' => 'date',
        'quantity'      => 'decimal:2',
        'unit_price'    => 'decimal:2',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function getTotalCostAttribute()
    {
        return $this->quantity * $this->unit_price;
    }
}
