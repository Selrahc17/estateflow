<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    protected $fillable = [
        'documentable_type', 'documentable_id', 'title', 'file_path',
        'file_name', 'file_type', 'file_size', 'document_type',
        'description', 'expiry_date', 'is_verified', 'verified_by',
        'checklist_key', 'checklist_status', 'rejection_reason', 'not_applicable_reason',
    ];

    public const CHECKLIST_STATUSES = [
        'submitted'       => ['label' => 'Under Review',        'color' => 'yellow'],
        'approved'        => ['label' => 'Approved',            'color' => 'green'],
        'rejected'        => ['label' => 'Rejected — Resubmit', 'color' => 'red'],
        'resubmitted'     => ['label' => 'Resubmitted',         'color' => 'blue'],
        'not_applicable'  => ['label' => 'Not Applicable',      'color' => 'gray'],
    ];

    protected $casts = [
        'is_verified'  => 'boolean',
        'expiry_date'  => 'date',
    ];

    public function isExpired(): bool
    {
        return $this->expiry_date && $this->expiry_date->isPast();
    }

    public function isExpiringSoon(): bool
    {
        return $this->expiry_date
            && !$this->expiry_date->isPast()
            && $this->expiry_date->diffInDays(now()) <= 30;
    }

    public function documentable()
    {
        return $this->morphTo();
    }

    public function verifiedBy()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function getFileSizeFormattedAttribute()
    {
        $bytes = $this->file_size;
        if ($bytes >= 1048576) return round($bytes / 1048576, 2) . ' MB';
        if ($bytes >= 1024) return round($bytes / 1024, 2) . ' KB';
        return $bytes . ' B';
    }
}
