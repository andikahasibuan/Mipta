<?php

namespace App\Exports;

use App\Models\FieldDistributionReport;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SupervisionExport implements FromCollection, WithColumnFormatting, WithColumnWidths, WithEvents, WithHeadings, WithMapping, WithTitle
{
    private const HEADER_ROW = 1;

    private const LAST_COLUMN = 'F';

    private const LAST_DATA_ROW_START = 2;

    private int $rowNumber = 0;

    /**
     * @param  Collection<int, FieldDistributionReport>  $reports
     */
    public function __construct(
        private readonly Collection $reports
    ) {}

    public function collection(): Collection
    {
        return $this->reports;
    }

    /**
     * @return array<string, float|int>
     */
    public function columnWidths(): array
    {
        return [
            'A' => 6,
            'B' => 16,
            'C' => 26,
            'D' => 26,
            'E' => 50,
            'F' => 18,
        ];
    }

    public function title(): string
    {
        return 'Laporan Pengawasan';
    }

    /**
     * @return array<string, string>
     */
    public function columnFormats(): array
    {
        return [
            'B' => 'dd/mm/yyyy',
        ];
    }

    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        return [
            'NO',
            'Tanggal Laporan',
            'Identitas Kontributor',
            'Pasar',
            'Keterangan',
            'Kondisi',
        ];
    }

    /**
     * @param  FieldDistributionReport  $report
     * @return array<int, mixed>
     */
    public function map($report): array
    {
        $this->rowNumber++;

        return [
            $this->rowNumber,
            ExcelDate::PHPToExcel($report->report_date),
            filled($report->contributor?->name) ? Str::title($report->contributor->name) : '-',
            filled($report->market?->name) ? Str::title($report->market->name) : '-',
            $report->description ?: '-',
            $report->condition->label(),
        ];
    }

    /**
     * @return array<class-string, callable>
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event): void {
                $this->styleTable($event->sheet->getDelegate());
            },
        ];
    }

    private function styleTable(Worksheet $sheet): void
    {
        $lastRow = max(self::HEADER_ROW, $sheet->getHighestRow());
        $tableRange = 'A'.self::HEADER_ROW.':'.self::LAST_COLUMN.$lastRow;

        $sheet->getStyle($tableRange)->getFont()->getColor()->setRGB('314158');
        $sheet->getStyle($tableRange)->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN)
            ->getColor()->setRGB('CBD5E1');

        $headerRange = 'A'.self::HEADER_ROW.':'.self::LAST_COLUMN.self::HEADER_ROW;
        $header = $sheet->getStyle($headerRange);
        $header->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('E2E8F0');
        $header->getFont()->setBold(true)->getColor()->setRGB('314158');
        $header->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER)
            ->setWrapText(true);
        $sheet->getRowDimension(self::HEADER_ROW)->setRowHeight(32);

        if ($lastRow < self::LAST_DATA_ROW_START) {
            return;
        }

        $sheet->getStyle('A'.self::LAST_DATA_ROW_START.':'.self::LAST_COLUMN.$lastRow)
            ->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

        foreach (['A', 'F'] as $column) {
            $sheet->getStyle($column.self::LAST_DATA_ROW_START.':'.$column.$lastRow)
                ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        $sheet->getStyle('B'.self::LAST_DATA_ROW_START.':B'.$lastRow)
            ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

        $sheet->getStyle('E'.self::LAST_DATA_ROW_START.':E'.$lastRow)
            ->getAlignment()->setWrapText(true);
    }
}
