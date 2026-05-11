<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Milestone extends Model
{
    protected $fillable = [
        'project_id', 'name', 'description', 'target_date',
        'actual_date', 'is_completed', 'completion_percentage', 'notes',
    ];

    protected $casts = [
        'target_date'  => 'date',
        'actual_date'  => 'date',
        'is_completed' => 'boolean',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
