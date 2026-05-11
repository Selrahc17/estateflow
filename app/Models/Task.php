<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $fillable = [
        'project_id', 'title', 'description', 'assigned_to', 'assigned_by',
        'start_date', 'due_date', 'completed_date', 'priority', 'status',
        'estimated_hours', 'actual_hours', 'notes',
    ];

    protected $casts = [
        'start_date'     => 'date',
        'due_date'       => 'date',
        'completed_date' => 'date',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}
