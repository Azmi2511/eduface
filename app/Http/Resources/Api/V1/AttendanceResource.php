<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'attendance' => [
                'id' => $this->id,
                'status' => $this->status,
                'date' => \Carbon\Carbon::parse($this->date)->format('d M Y'),
                'time' => $this->time_log,
            ],
            'student' => [
                'nisn' => $this->student_nisn,
                'name' => $this->student->user->full_name ?? 'Siswa',
            ],
            'schedule' => [
                'subject' => $this->schedule->subject->subject_name ?? 'Masuk Sekolah',
                'teacher' => $this->schedule->teacher->user->full_name ?? 'Wali Kelas',
                'class'   => $this->student->class->class_name ?? '-',
            ],
            'device_name' => $this->device->device_name ?? 'Manual / Web',
        ];
    }
}