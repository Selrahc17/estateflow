<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectIssue extends Model
{
    protected $fillable = [
        'project_id', 'reported_by', 'type', 'severity',
        'title', 'description', 'impact_days', 'status',
        'resolved_by', 'resolved_at', 'resolution_notes',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
        'impact_days' => 'integer',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function reportedBy()
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function resolvedBy()
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }
}
