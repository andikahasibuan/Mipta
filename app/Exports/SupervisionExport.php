<?php

namespace App\Exports;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SupervisionExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    private int $rowNumber = 0;

    /**
     * @param  Collection<int, \App\Models\FieldDistributionReport>  $reports
     */
    public function __construct(
        private readonly Collection $reports
    ) {}

    public function collection(): Collection
    {
        return $this->reports;
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
     * @param  \App\Models\FieldDistributionReport  $report
     * @return array<int, mixed>
     */
    public function map($report): array
    {
        $this->rowNumber++;

        return [
            $this->rowNumber,
            Carbon::parse($report->report_date)->format('d/m/Y'),
            filled($report->contributor?->name) ? Str::title($report->contributor->name) : '-',
            filled($report->market?->name) ? Str::title($report->market->name) : '-',
            $report->description ?: '-',
            $report->condition->label(),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}