<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    protected $table = 'system_settings';
    protected $primaryKey = 'id';
    public $incrementing = false;

    protected $fillable = [
        'school_name',
        'npsn',
        'address',
        'email',
        'phone',
        'language',
        'timezone',
        'entry_time',
        'late_limit',
        'exit_time',
        'tolerance_minutes',
        'face_rec_enabled',
        'min_accuracy',
        'notif_late',
        'notif_absent',
    ];
}
