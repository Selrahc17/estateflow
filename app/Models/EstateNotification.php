<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EstateNotification extends Model
{
    protected $table = 'notifications';

    protected $fillable = [
        'notifiable_type', 'notifiable_id', 'type',
        'data', 'read_at', 'priority', 'is_read',
    ];

    protected $casts = [
        'data'    => 'array',
        'read_at' => 'datetime',
        'is_read' => 'boolean',
    ];

    public function notifiable()
    {
        return $this->morphTo();
    }

    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function markAsRead()
    {
        $this->update(['is_read' => true, 'read_at' => now()]);
    }
}
