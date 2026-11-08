<?php

use App\Enums\FieldCondition;
use App\Enums\Role;
use App\Models\Distribution;
use App\Models\Distributor;
use App\Models\FieldDistributionReport;
use App\Models\Market;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('mengarahkan tamu dari halaman rekap ke halaman login', function () {
    $this->get(route('admin.reports.index'))->assertRedirect(route('login'));
});

it('menampilkan ringkasan periode bulan berjalan secara default', function () {
    $market = Market::factory()->create();
    $uptd = User::factory()->create(['role' => Role::UptdPasar]);

    Distribution::factory()->count(2)->create([
        'market_id' => $market->id,
        'uptd_user_id' => $uptd->id,
        'distribution_date' => now()->startOfMonth()->toDateString(),
        'cartons' => 120,
    ]);

    FieldDistributionReport::factory()->create([
        'market_id' => $market->id,
        'uptd_user_id' => $uptd->id,
        'report_date' => now()->toDateString(),
        'condition' => FieldCondition::Normal,
    ]);

    FieldDistributionReport::factory()->create([
        'market_id' => $market->id,
        'uptd_user_id' => $uptd->id,
        'report_date' => now()->toDateString(),
        'condition' => FieldCondition::PerluPerhatian,
    ]);

    $this->actingAs(User::factory()->create())
        ->get(route('admin.reports.index'))
        ->assertOk()
        ->assertViewHas('summary', function (array $summary): bool {
            return $summary['distribution_reports'] === 2
                && $summary['distribution_cartons'] === 240
                && $summary['condition_reports'] === 2
                && $summary['needs_attention'] === 1;
        })
        ->assertSee('Ekspor Data')
        ->assertSee('value="'.now()->startOfMonth()->toDateString().'"', false)
        ->assertSee('value="'.now()->endOfMonth()->toDateString().'"', false);
});

it('mengelompokkan rekap penyaluran per pasar beserta jumlah distributor unik', function () {
    $firstMarket = Market::factory()->create(['name' => 'Pasar Raya Padang']);
    $secondMarket = Market::factory()->create(['name' => 'Pasar Bandar Buat']);
    $firstDistributor = Distributor::factory()->create();
    $secondDistributor = Distributor::factory()->create();
    $uptd = User::factory()->create(['role' => Role::UptdPasar]);
    $date = now()->toDateString();

    foreach ([
        [$firstMarket, $firstDistributor, 100],
        [$firstMarket, $secondDistributor, 50],
        [$firstMarket, $firstDistributor, 25],
        [$secondMarket, $secondDistributor, 40],
    ] as [$market, $distributor, $cartons]) {
        Distribution::factory()->create([
            'market_id' => $market->id,
            'distributor_id' => $distributor->id,
            'uptd_user_id' => $uptd->id,
            'distribution_date' => $date,
            'cartons' => $cartons,
        ]);
    }

    $response = $this->actingAs(User::factory()->create())
        ->get(route('admin.reports.index'));

    $response->assertOk()
        ->assertSee('Rekap Penyaluran per Pasar')
        ->assertSee('Pasar Raya Padang')
        ->assertSee('Pasar Bandar Buat');

    $recap = $response->viewData('distributionRecap');
    $firstRow = $recap->firstWhere('market_id', $firstMarket->id);
    $secondRow = $recap->firstWhere('market_id', $secondMarket->id);

    expect($recap)->toHaveCount(2)
        ->and((int) $firstRow->reports_count)->toBe(3)
        ->and((int) $firstRow->cartons_sum)->toBe(175)
        ->and((int) $firstRow->distributors_count)->toBe(2)
        ->and((int) $secondRow->reports_count)->toBe(1)
        ->and((int) $secondRow->cartons_sum)->toBe(40)
        ->and((int) $secondRow->distributors_count)->toBe(1);
});

it('menampilkan rekap kondisi pasar pada tab kedua', function () {
    $market = Market::factory()->create(['name' => 'Pasar Raya Padang']);
    $contributor = User::factory()->create(['name' => 'Andi Pratama', 'role' => Role::UptdPasar]);

    FieldDistributionReport::factory()->create([
        'market_id' => $market->id,
        'uptd_user_id' => $contributor->id,
        'report_date' => now()->subDay()->toDateString(),
        'description' => 'Stok Minyakita menipis di beberapa kios',
        'condition' => FieldCondition::PerluPerhatian,
    ]);

    $this->actingAs(User::factory()->create())
        ->get(route('admin.reports.index', ['tab' => 'kondisi']))
        ->assertOk()
        ->assertViewHas('activeTab', 'kondisi')
        ->assertSee('Rekap Kondisi Pasar')
        ->assertSee('Andi Pratama')
        ->assertSee('Pasar Raya Padang')
        ->assertSee('Stok Minyakita menipis di beberapa kios')
        ->assertSee('Perlu Perhatian')
        ->assertSee(now()->subDay()->format('d/m/Y'));
});

it('memfilter ringkasan berdasarkan rentang tanggal yang dipilih', function () {
    $market = Market::factory()->create();
    $uptd = User::factory()->create(['role' => Role::UptdPasar]);

    Distribution::factory()->create([
        'market_id' => $market->id,
        'uptd_user_id' => $uptd->id,
        'distribution_date' => '2026-01-10',
        'cartons' => 80,
    ]);

    Distribution::factory()->create([
        'market_id' => $market->id,
        'uptd_user_id' => $uptd->id,
        'distribution_date' => '2026-02-10',
        'cartons' => 20,
    ]);

    FieldDistributionReport::factory()->create([
        'market_id' => $market->id,
        'uptd_user_id' => $uptd->id,
        'report_date' => '2026-01-15',
        'condition' => FieldCondition::PerluPerhatian,
    ]);

    FieldDistributionReport::factory()->create([
        'market_id' => $market->id,
        'uptd_user_id' => $uptd->id,
        'report_date' => '2026-02-15',
        'condition' => FieldCondition::Normal,
    ]);

    $this->actingAs(User::factory()->create())
        ->get(route('admin.reports.index', ['start_date' => '2026-01-01', 'end_date' => '2026-01-31']))
        ->assertOk()
        ->assertViewHas('summary', function (array $summary): bool {
            return $summary['distribution_reports'] === 1
                && $summary['distribution_cartons'] === 80
                && $summary['condition_reports'] === 1
                && $summary['needs_attention'] === 1;
        })
        ->assertSee('value="2026-01-01"', false)
        ->assertSee('value="2026-01-31"', false);
});

it('menormalkan tanggal terbalik dan mengabaikan tanggal tidak valid', function () {
    $this->actingAs(User::factory()->create());

    $this->get(route('admin.reports.index', ['start_date' => '2026-03-31', 'end_date' => '2026-03-01']))
        ->assertOk()
        ->assertViewHas('filters', function (array $filters): bool {
            return $filters['start_date'] === '2026-03-01'
                && $filters['end_date'] === '2026-03-31';
        });

    $this->get(route('admin.reports.index', ['start_date' => '2026-02-30', 'end_date' => 'bukan-tanggal']))
        ->assertOk()
        ->assertViewHas('filters', function (array $filters): bool {
            return $filters['start_date'] === now()->startOfMonth()->toDateString()
                && $filters['end_date'] === now()->endOfMonth()->toDateString();
        });
});

it('kembali ke tab penyaluran saat tab tidak dikenal', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('admin.reports.index', ['tab' => 'bukan-tab']))
        ->assertOk()
        ->assertViewHas('activeTab', 'penyaluran')
        ->assertViewHas('distributionRecap', fn ($recap): bool => $recap !== null);
});

it('membatasi rekap kondisi pasar menjadi lima baris per halaman', function () {
    $market = Market::factory()->create();
    $uptd = User::factory()->create(['role' => Role::UptdPasar]);

    FieldDistributionReport::factory()->count(6)->create([
        'market_id' => $market->id,
        'uptd_user_id' => $uptd->id,
        'report_date' => now()->toDateString(),
        'condition' => FieldCondition::Normal,
    ]);

    $response = $this->actingAs(User::factory()->create())
        ->get(route('admin.reports.index', ['tab' => 'kondisi']));

    $response->assertOk();

    expect($response->viewData('conditionReports')->count())->toBe(5)
        ->and($response->viewData('conditionReports')->total())->toBe(6)
        ->and($response->viewData('conditionReports')->hasPages())->toBeTrue();

    $this->get(route('admin.reports.index', ['tab' => 'kondisi', 'kondisi_page' => 2]))
        ->assertOk()
        ->assertViewHas('conditionReports', function ($reports): bool {
            return $reports->count() === 1
                && $reports->currentPage() === 2
                && $reports->total() === 6;
        });
});

it('mempertahankan periode saat berpindah tab', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('admin.reports.index', ['start_date' => '2026-01-01', 'end_date' => '2026-01-31']))
        ->assertOk()
        ->assertSee(route('admin.reports.index', [
            'start_date' => '2026-01-01',
            'end_date' => '2026-01-31',
            'tab' => 'kondisi',
        ]));
});

it('menampilkan keadaan kosong saat periode belum memiliki data', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('admin.reports.index'))
        ->assertOk()
        ->assertSee('Belum ada data penyaluran pada periode ini');

    $this->get(route('admin.reports.index', ['tab' => 'kondisi']))
        ->assertOk()
        ->assertSee('Belum ada laporan kondisi pasar pada periode ini');
});
