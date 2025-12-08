<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    protected $table = 'schedules';
    protected $fillable = ['class_id','subject_id','teacher_nip','day_of_week','start_time','end_time'];
}
