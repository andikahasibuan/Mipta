<?php

use App\Enums\Role;
use App\Exports\DistributionExport;
use App\Exports\SupervisionExport;
use App\Models\Distribution;
use App\Models\FieldDistributionReport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

uses(RefreshDatabase::class);

dataset('report exports', [
    'admin penyaluran excel' => ['admin.distribution.export.excel', Role::Admin],
    'admin penyaluran pdf' => ['admin.distribution.export.pdf', Role::Admin],
    'admin pengawasan excel' => ['admin.supervision.export.excel', Role::Admin],
    'admin pengawasan pdf' => ['admin.supervision.export.pdf', Role::Admin],
    'kepala dinas penyaluran excel' => ['kepala-dinas.distribution.export.excel', Role::KepalaDinas],
    'kepala dinas penyaluran pdf' => ['kepala-dinas.distribution.export.pdf', Role::KepalaDinas],
    'kepala dinas pengawasan excel' => ['kepala-dinas.supervision.export.excel', Role::KepalaDinas],
    'kepala dinas pengawasan pdf' => ['kepala-dinas.supervision.export.pdf', Role::KepalaDinas],
]);

it('mengunduh laporan sebagai berkas', function (string $routeName, Role $role) {
    Distribution::factory()->create();
    FieldDistributionReport::factory()->create();

    $this->actingAs(User::factory()->create(['role' => $role]))
        ->get(route($routeName))
        ->assertOk()
        ->assertDownload();
})->with('report exports');

it('menyimpan tanggal sebagai tanggal asli excel dengan format dd/mm/yyyy', function () {
    $distribution = Distribution::factory()->create(['distribution_date' => '2026-05-20']);
    $distributionExport = new DistributionExport(collect([$distribution]));
    $distributionRow = $distributionExport->map($distribution);

    expect($distributionExport->columnFormats()['B'])->toBe('dd/mm/yyyy')
        ->and($distributionRow[1])->toBe(ExcelDate::PHPToExcel($distribution->distribution_date));

    $report = FieldDistributionReport::factory()->create(['report_date' => '2026-05-20']);
    $supervisionExport = new SupervisionExport(collect([$report]));
    $supervisionRow = $supervisionExport->map($report);

    expect($supervisionExport->columnFormats()['B'])->toBe('dd/mm/yyyy')
        ->and($supervisionRow[1])->toBe(ExcelDate::PHPToExcel($report->report_date));
});

it('menampilkan tanggal pada pdf dengan format dd/mm/yyyy', function () {
    $distribution = Distribution::factory()->create(['distribution_date' => '2026-11-01']);
    $distributionHtml = view('exports.distribution-pdf', [
        'distributions' => collect([$distribution]),
        'month_label' => 'November 2026',
    ])->render();

    expect($distributionHtml)->toContain('01/11/2026');

    $report = FieldDistributionReport::factory()->create(['report_date' => '2026-11-01']);
    $supervisionHtml = view('exports.supervision-pdf', [
        'reports' => collect([$report]),
        'month_label' => 'November 2026',
    ])->render();

    expect($supervisionHtml)->toContain('01/11/2026');
});
