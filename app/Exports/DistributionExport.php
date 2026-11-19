<?php

namespace App\Exports;

use App\Models\Distribution;
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

class DistributionExport implements FromCollection, WithColumnFormatting, WithColumnWidths, WithEvents, WithHeadings, WithMapping, WithTitle
{
    private const HEADER_ROW = 1;

    private const LAST_COLUMN = 'F';

    private const LAST_DATA_ROW_START = 2;

    private int $rowNumber = 0;

    /**
     * @param  Collection<int, Distribution>  $distributions
     */
    public function __construct(
        private readonly Collection $distributions
    ) {}

    public function collection(): Collection
    {
        return $this->distributions;
    }

    /**
     * @return array<string, float|int>
     */
    public function columnWidths(): array
    {
        return [
            'A' => 6,
            'B' => 22,
            'C' => 30,
            'D' => 20,
            'E' => 45,
            'F' => 20,
        ];
    }

    /**
     * @return array<string, string>
     */
    public function columnFormats(): array
    {
        return [
            'B' => 'dd/mm/yyyy',
            'D' => '#,##0',
        ];
    }

    public function title(): string
    {
        return 'Laporan Penyaluran';
    }

    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        return [
            'NO',
            'Tanggal Penyaluran',
            'Nama Toko',
            "Jumlah Disalurkan\n(Karton)",
            'Alamat',
            'Nomor NIB',
        ];
    }

    /**
     * @param  Distribution  $distribution
     * @return array<int, mixed>
     */
    public function map($distribution): array
    {
        $this->rowNumber++;

        return [
            $this->rowNumber,
            ExcelDate::PHPToExcel($distribution->distribution_date),
            filled($distribution->store?->name) ? Str::title($distribution->store->name) : '-',
            $distribution->cartons,
            filled($distribution->store?->alamat_lengkap) ? Str::title($distribution->store->alamat_lengkap) : '-',
            $distribution->store?->nib ?: '-',
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

        $sheet->getStyle('B'.self::HEADER_ROW)->getAlignment()->setWrapText(false);

        if ($lastRow < self::LAST_DATA_ROW_START) {
            return;
        }

        $sheet->getStyle('A'.self::LAST_DATA_ROW_START.':'.self::LAST_COLUMN.$lastRow)
            ->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

        foreach (['A', 'D'] as $column) {
            $sheet->getStyle($column.self::LAST_DATA_ROW_START.':'.$column.$lastRow)
                ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        $sheet->getStyle('B'.self::LAST_DATA_ROW_START.':B'.$lastRow)
            ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

        $sheet->getStyle('E'.self::LAST_DATA_ROW_START.':E'.$lastRow)
            ->getAlignment()->setWrapText(true);
    }
}
