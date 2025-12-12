<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    protected $fillable = [
        'message',
        'attachment_file',
        'attachment_link',
        'sent_at',
        'recipient_id',
    ];

    public function recipient()
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class, 'ann_id');
    }
}