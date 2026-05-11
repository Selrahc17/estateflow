<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AIPrediction extends Model
{
    protected $table = 'ai_predictions';

    protected $fillable = [
        'predictable_type', 'predictable_id', 'prediction_type',
        'predicted_value', 'confidence_score', 'prediction_data',
        'input_features', 'model_version', 'created_by',
    ];

    protected $casts = [
        'predicted_value'  => 'decimal:2',
        'confidence_score' => 'decimal:2',
        'prediction_data'  => 'array',
        'input_features'   => 'array',
    ];

    public function predictable()
    {
        return $this->morphTo();
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getConfidenceLevelAttribute()
    {
        if ($this->confidence_score >= 80) return 'High';
        if ($this->confidence_score >= 50) return 'Medium';
        return 'Low';
    }

    public function getConfidenceColorAttribute()
    {
        if ($this->confidence_score >= 80) return 'green';
        if ($this->confidence_score >= 50) return 'yellow';
        return 'red';
    }
}
