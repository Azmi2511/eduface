<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    protected $fillable = [
        'user_id',
        'nisn',
        'class_id',
        'parent_id',
        'photo_path',
        'face_registered'
    ];

    protected $casts = [
        'face_registered' => 'boolean',
        'face_registered_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function class()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id', 'id');
    }

    public function parent()
    {
        return $this->belongsTo(ParentProfile::class, 'parent_id');
    }

    public function attendanceLogs()
    {
        // attendance_logs references students.nisn
        return $this->hasMany(AttendanceLog::class, 'student_nisn', 'nisn');
    }

    public function permissions()
    {
        return $this->hasMany(Permission::class);
    }
}
