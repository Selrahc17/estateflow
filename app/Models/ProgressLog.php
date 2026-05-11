<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProgressLog extends Model
{
    protected $fillable = [
        'project_id', 'user_id', 'log_date', 'description',
        'completion_percentage', 'image_path', 'images',
        'issues', 'weather_conditions', 'workers_count', 'hours_worked',
    ];

    protected $casts = [
        'log_date' => 'date',
        'images'   => 'array',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
