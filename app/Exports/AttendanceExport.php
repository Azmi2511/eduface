<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use Carbon\Carbon;

class AttendanceExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithEvents
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
            ['LAPORAN ABSENSI HARIAN'],
            [strtoupper(Carbon::now()->translatedFormat('l, d F Y'))],
            [],
            [
                'NO',
                'WAKTU',
                'NISN',
                'NAMA SISWA',
                'KELAS',
                'STATUS KEHADIRAN',
            ]
        ];
    }

    public function map($log): array
    {
        static $row_number = 0;
        $row_number++;

        return [
            $row_number,
            Carbon::parse($log->date)->format('H:i') . ' WIB',
            $log->student_nisn,
            optional(optional($log->student)->user)->full_name ?? '-',
            optional(optional($log->student)->class)->class_name ?? '-',
            strtoupper($log->status),
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastRow = $sheet->getHighestRow();
                $lastCol = 'F';

                $sheet->setShowGridlines(false);

                $sheet->mergeCells('A1:F1');
                $sheet->mergeCells('A2:F2');

                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 18, 'color' => ['argb' => 'FF1A202C']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                $sheet->getStyle('A2')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 12, 'color' => ['argb' => 'FF718096']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                $headerStyle = [
                    'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF2B6CB0']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    'borders' => ['bottom' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['argb' => 'FF2B6CB0']]],
                ];
                $sheet->getStyle('A4:F4')->applyFromArray($headerStyle);
                $sheet->getRowDimension(4)->setRowHeight(30);

                if ($lastRow >= 5) {
                    $sheet->getStyle('A5:C' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle('F5:F' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle('A5:F' . $lastRow)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
                    $sheet->getStyle('A5:F' . $lastRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->setColor(new Color('FFE2E8F0'));

                    for ($row = 5; $row <= $lastRow; $row++) {
                        if ($row % 2 == 0) {
                            $sheet->getStyle('A' . $row . ':F' . $row)->getFill()
                                ->setFillType(Fill::FILL_SOLID)
                                ->getStartColor()->setARGB('FFF7FAFC');
                        }

                        $statusCell = 'F' . $row;
                        $statusVal = $sheet->getCell($statusCell)->getValue();
                        
                        $bg = 'FFFFFFFF';
                        $text = 'FF000000';

                        if (str_contains($statusVal, 'HADIR')) {
                            $bg = 'FFC6F6D5'; $text = 'FF22543D'; // Light Green & Dark Green
                        } elseif (str_contains($statusVal, 'TERLAMBAT')) {
                            $bg = 'FFFEEBC8'; $text = 'FF7B341E'; // Light Orange & Dark Brown
                        } elseif (str_contains($statusVal, 'ALPHA') || str_contains($statusVal, 'ABSEN')) {
                            $bg = 'FFFED7D7'; $text = 'FF822727'; // Light Red & Dark Red
                        }

                        $sheet->getStyle($statusCell)->applyFromArray([
                            'font' => ['bold' => true, 'color' => ['argb' => $text]],
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $bg]],
                        ]);
                    }
                }
                
                $sheet->getColumnDimension('A')->setWidth(8);
                $sheet->getColumnDimension('F')->setWidth(20);
            },
        ];
    }
}