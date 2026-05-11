<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $fillable = [
        'from_user_id', 'to_user_id', 'reservation_id', 'message', 'read_at', 'attachment', 'attachment_type',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    public function fromUser()
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }

    public function toUser()
    {
        return $this->belongsTo(User::class, 'to_user_id');
    }

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

    public function isRead(): bool
    {
        return $this->read_at !== null;
    }
}
