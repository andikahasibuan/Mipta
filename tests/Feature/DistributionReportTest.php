<?php

use App\Enums\ReportStatus;
use App\Models\DistributionReport;
use App\Models\Distributor;
use App\Models\Market;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

it('membuat laporan penyaluran beserta distributor dan pasar dari factory', function () {
    $report = DistributionReport::factory()->create([
        'report_date' => '2026-05-28',
        'cartons' => 480,
        'status' => ReportStatus::Terverifikasi,
    ]);

    expect($report->exists)->toBeTrue()
        ->and($report->code)->toStartWith('LP-')
        ->and($report->distributor)->toBeInstanceOf(Distributor::class)
        ->and($report->market)->toBeInstanceOf(Market::class)
        ->and($report->report_date)->toBeInstanceOf(Carbon::class)
        ->and($report->report_date->toDateString())->toBe('2026-05-28')
        ->and($report->cartons)->toBe(480)
        ->and($report->status)->toBe(ReportStatus::Terverifikasi);
});

it('menghubungkan distributor dan pasar ke banyak laporan', function () {
    $distributor = Distributor::factory()->create();
    $market = Market::factory()->create();

    $reports = DistributionReport::factory()->count(2)->create([
        'distributor_id' => $distributor->id,
        'market_id' => $market->id,
    ]);

    expect($distributor->reports()->count())->toBe(2)
        ->and($market->reports()->count())->toBe(2)
        ->and($distributor->reports->pluck('id')->all())->toEqual($reports->pluck('id')->all());
});

it('menyimpan status default sebagai menunggu', function () {
    $report = DistributionReport::create([
        'code' => 'LP-2026-0009',
        'distributor_id' => Distributor::factory()->create()->id,
        'market_id' => Market::factory()->create()->id,
        'report_date' => '2026-05-28',
        'cartons' => 120,
    ]);

    expect($report->fresh()->status)->toBe(ReportStatus::Menunggu);
});

it('menolak kode laporan yang duplikat', function () {
    DistributionReport::factory()->create(['code' => 'LP-2026-0001']);

    DistributionReport::factory()->create(['code' => 'LP-2026-0001']);
})->throws(QueryException::class);
