<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $table = 'notifications';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = null;

    protected $fillable = ['user_id','user_role','message','link','is_read'];

    protected $casts = [
        'is_read' => 'boolean',
    ];
}