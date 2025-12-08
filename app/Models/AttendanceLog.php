<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceLog extends Model
{
    protected $table = 'attendance_logs';

    protected $fillable = ['student_nisn','user_id','device_id','schedule_id','date','time_log','status'];

    protected $casts = [
        'date' => 'date',
        'time_log' => 'datetime:H:i:s',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_nisn', 'nisn');
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class, 'device_id');
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class, 'schedule_id');
    }
}
