<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $fillable = [
        'name', 'description', 'property_id', 'client_id', 'staff_id',
        'start_date', 'estimated_completion_date', 'actual_completion_date',
        'budget', 'actual_cost', 'status', 'completion_percentage', 'notes',
    ];

    protected $casts = [
        'start_date'                  => 'date',
        'estimated_completion_date'   => 'date',
        'actual_completion_date'      => 'date',
        'budget'                      => 'decimal:2',
        'actual_cost'                 => 'decimal:2',
    ];

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function staff()
    {
        return $this->belongsTo(Staff::class, 'staff_id');
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function milestones()
    {
        return $this->hasMany(Milestone::class);
    }

    public function budgets()
    {
        return $this->hasMany(Budget::class);
    }

    public function progressLogs()
    {
        return $this->hasMany(ProgressLog::class);
    }

    public function resources()
    {
        return $this->hasMany(Resource::class);
    }

    public function issues()
    {
        return $this->hasMany(ProjectIssue::class);
    }

    public function totalImpactDays()
    {
        return $this->issues()->whereIn('status', ['open', 'in_progress'])->sum('impact_days');
    }
}
