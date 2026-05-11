<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Budget extends Model
{
    protected $fillable = [
        'project_id', 'category', 'description', 'estimated_amount',
        'actual_amount', 'currency', 'budget_date', 'status', 'notes',
    ];

    protected $casts = [
        'budget_date'      => 'date',
        'estimated_amount' => 'decimal:2',
        'actual_amount'    => 'decimal:2',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function getVarianceAttribute()
    {
        return $this->estimated_amount - $this->actual_amount;
    }
}
