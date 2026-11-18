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

class DistributionExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    private int $rowNumber = 0;

    /**
     * @param  Collection<int, \App\Models\Distribution>  $distributions
     */
    public function __construct(
        private readonly Collection $distributions
    ) {}

    public function collection(): Collection
    {
        return $this->distributions;
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
            'Jumlah Disalurkan (Karton)',
            'Alamat',
            'Nomor NIB',
        ];
    }

    /**
     * @param  \App\Models\Distribution  $distribution
     * @return array<int, mixed>
     */
    public function map($distribution): array
    {
        $this->rowNumber++;

        return [
            $this->rowNumber,
            Carbon::parse($distribution->distribution_date)->format('d/m/Y'),
            filled($distribution->store?->name) ? Str::title($distribution->store->name) : '-',
            $distribution->cartons,
            filled($distribution->store?->alamat_lengkap) ? Str::title($distribution->store->alamat_lengkap) : '-',
            $distribution->store?->nib ?: '-',
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