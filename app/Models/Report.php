<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    protected $table = 'reports';
    protected $fillable = ['report_name','report_type','period_start','period_end','status'];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
    ];
}
