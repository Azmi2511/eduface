<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    protected $fillable = [
        'class_id',
        'subject_id',
        'teacher_id',
        'day',
        'day_of_week',
        'start_time',
        'end_time',
    ];

    public function class()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function attendanceLogs()
    {
        return $this->hasMany(AttendanceLog::class);
    }

    public function getDayAttribute()
    {
        if (array_key_exists('day', $this->attributes)) {
            return $this->attributes['day'];
        }

        return $this->day_of_week;
    }
}
