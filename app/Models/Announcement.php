<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    protected $table = 'announcements';

    // table uses `created_at` and `update_at` (note: update_at not updated_at)
    const UPDATED_AT = 'update_at';
    const CREATED_AT = 'created_at';

    protected $fillable = ['message','attachment_file','attachment_link','datetime_send','recipient'];

    protected $casts = [
        'datetime_send' => 'datetime',
    ];
}
