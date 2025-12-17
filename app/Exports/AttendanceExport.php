<?php

namespace App\Exports;

use App\Models\AttendanceLog;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Maatwebsite\Excel\Concerns\Exportable;
use Carbon\Carbon;

class AttendanceExport implements FromQuery, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    use Exportable;
    protected $query;

    public function __construct($query)
    {
        $this->query = $query;
    }

    public function query()
    {
        return $this->query;
    }

    public function headings(): array
    {
        return [
            'TANGGAL',
            'WAKTU',
            'NISN',
            'NAMA SISWA',
            'KELAS',
            'STATUS',
        ];
    }

    public function map($log): array
    {
        return [
            Carbon::parse($log->date)->format('d-m-Y'),
            $log->time_log,
            $log->student_nisn,
            $log->student->user->full_name ?? '-',
            $log->student->class->class_name ?? '-',
            $log->status,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 12],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            'A' => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]],
            'B' => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]],
            'C' => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]],
            'F' => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]],
        ];
    }
}