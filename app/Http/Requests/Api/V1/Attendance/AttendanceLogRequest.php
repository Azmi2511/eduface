<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceLogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'student_nisn' => 'required|exists:students,nisn',
            'date'         => 'required|date_format:Y-m-d',
            'time_log'     => 'required|date_format:H:i:s',
            'status'       => 'required|in:Hadir,Terlambat,Izin,Sakit,Alpha',
            'schedule_id'  => 'nullable|exists:schedules,id',
        ];
    }

    /**
     * Penjelasan untuk Scramble UI
     */
    public function bodyParameters(): array
    {
        return [
            'student_nisn' => [
                'description' => 'Nomor Induk Siswa Nasional.',
                'example' => '0012345678',
            ],
            'date' => [
                'description' => 'Tanggal absensi (YYYY-MM-DD).',
                'example' => date('Y-m-d'),
            ],
            'time_log' => [
                'description' => 'Jam absensi (HH:mm:ss).',
                'example' => '07:15:00',
            ],
            'status' => [
                'description' => 'Status kehadiran siswa.',
                'example' => 'Hadir',
            ],
        ];
    }
}