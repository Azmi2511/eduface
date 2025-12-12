<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceLog extends Model
{
    protected $fillable = [
        'student_id',
        'device_id',
        'schedule_id',
        'date',
        'time_log',
        'status'
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function device()
    {
        return $this->belongsTo(Device::class);
    }

    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }
}