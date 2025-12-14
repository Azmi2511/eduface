<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceLog extends Model
{
    // attendance_logs uses student_nisn (string) as FK to students.nisn
    protected $fillable = [
        'student_nisn',
        'device_id',
        'schedule_id',
        'date',
        'time_log',
        'status'
    ];

    protected $casts = [
        'date' => 'date',
        'time_log' => 'string',
        'status' => 'string',
    ];

    public function student()
    {
        // FK: student_nisn -> students.nisn
        return $this->belongsTo(Student::class, 'student_nisn', 'nisn');
    }

    public function device()
    {
        return $this->belongsTo(Device::class, 'device_id');
    }

    public function schedule()
    {
        return $this->belongsTo(Schedule::class, 'schedule_id');
    }
}