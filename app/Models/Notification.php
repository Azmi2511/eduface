<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $table = 'notifications';

    public $timestamps = false; 

    protected $guarded = ['id'];

    protected $fillable = [
        'user_id',
        'message',
        'ann_id',
        'is_read',
        'created_at'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'is_read'    => 'integer',
    ];
}