<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    protected $fillable = [
        'username',
        'email',
        'password',
        'full_name',
        'phone',
        'dob',
        'gender',
        'profile_picture',
        'role',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'dob' => 'date',
    ];

    // RELATIONS
    public function teacher()
    {
        return $this->hasOne(Teacher::class);
    }

    public function student()
    {
        return $this->hasOne(Student::class);
    }

    public function parentProfile()
    {
        return $this->hasOne(ParentProfile::class, 'user_id');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }
}
