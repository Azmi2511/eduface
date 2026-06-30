<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'nisn',
        'class_id',
        'parent_id',
        'relationship',
        'photo_path',
        'face_registered'
    ];

    protected $casts = [
        'face_registered' => 'boolean',
        'face_registered_at' => 'datetime',
        'face_descriptor' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function schoolClass()
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
