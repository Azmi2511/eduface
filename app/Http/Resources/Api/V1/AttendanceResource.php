<?php
namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'           => $this->id,
            'student'      => [
                'nisn'      => $this->student_nisn,
                'full_name' => $this->student->user->full_name ?? 'N/A',
                'class'     => $this->student->class->class_name ?? 'N/A',
            ],
            'schedule'     => $this->schedule ? [
                'id'           => $this->schedule->id,
                'subject_name' => $this->schedule->subject->subject_name,
            ] : null,
            'device'       => $this->device->device_name ?? 'Manual/Admin',
            'date'         => $this->date->format('Y-m-d'),
            'time_log'     => substr($this->time_log, 0, 5),
            'status'       => $this->status,
            'created_at'   => $this->created_at->format('H:i:s'),
        ];
    }
}