<?php

use App\Enums\Role;
use App\Models\Distribution;
use App\Models\Distributor;
use App\Models\FieldDistributionReport;
use App\Models\Market;
use App\Models\UptdContributor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('menampilkan dashboard monitoring tanpa tombol unduh rekap', function () {
    $admin = User::factory()->create(['name' => 'Iqbal Shyhab']);

    $this->actingAs($admin)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('Hai, Iqbal Shyhab')
        ->assertSee('Total Laporan')
        ->assertDontSee('dari bulan lalu')
        ->assertDontSee('Distributor Aktif')
        ->assertDontSee('Pasar Terpantau')
        ->assertDontSee('Unduh Rekap Bulanan');
});

it('menampilkan kartu statistik dari database', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSeeInOrder([
            'Total Laporan', '0',
            'Total Penyaluran', '0',
        ])
        ->assertDontSee('Belum ada data laporan')
        ->assertDontSee('karton pada');
});

it('menghitung total laporan dari penyaluran dan kondisi pasar', function () {
    $firstDistributor = Distributor::factory()->create(['name' => 'PT Alpha Niaga']);
    $secondDistributor = Distributor::factory()->create(['name' => 'PT Beta Pangan']);
    $firstMarket = Market::factory()->create();
    $secondMarket = Market::factory()->create();
    $uptd = User::factory()->create(['role' => Role::UptdPasar]);
    $contributor = UptdContributor::factory()->create();

    Distribution::factory()->create([
        'distributor_id' => $firstDistributor->id,
        'market_id' => $firstMarket->id,
        'uptd_user_id' => $uptd->id,
        'distribution_date' => now()->startOfMonth()->toDateString(),
        'cartons' => 250,
    ]);

    Distribution::factory()->create([
        'distributor_id' => $secondDistributor->id,
        'market_id' => $secondMarket->id,
        'uptd_user_id' => $uptd->id,
        'distribution_date' => now()->startOfMonth()->addDay()->toDateString(),
        'cartons' => 180,
    ]);

    Distribution::factory()->create([
        'distributor_id' => $firstDistributor->id,
        'market_id' => $firstMarket->id,
        'uptd_user_id' => $uptd->id,
        'distribution_date' => now()->subMonthNoOverflow()->startOfMonth()->toDateString(),
        'cartons' => 100,
    ]);

    FieldDistributionReport::factory()->create([
        'market_id' => $firstMarket->id,
        'uptd_contributor_id' => $contributor->id,
        'report_date' => now()->startOfMonth()->toDateString(),
    ]);

    FieldDistributionReport::factory()->create([
        'market_id' => $secondMarket->id,
        'uptd_contributor_id' => $contributor->id,
        'report_date' => now()->startOfMonth()->toDateString(),
    ]);

    FieldDistributionReport::factory()->create([
        'market_id' => $firstMarket->id,
        'uptd_contributor_id' => $contributor->id,
        'report_date' => now()->subMonthNoOverflow()->startOfMonth()->toDateString(),
    ]);

    $this->actingAs(User::factory()->create())
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSeeInOrder(['Total Laporan', '4'])
        ->assertSeeInOrder(['Total Penyaluran', '430'])
        ->assertViewHas('summary', fn (array $summary): bool => $summary['distribution_reports'] === 2
            && $summary['condition_reports'] === 2
            && $summary['reports'] === 4)
        ->assertDontSee('dari bulan lalu')
        ->assertSeeInOrder(['PT Alpha Niaga', '250'])
        ->assertSeeInOrder(['PT Beta Pangan', '180']);
});

it('memfilter total laporan kondisi pasar berdasarkan pasar tetapi tidak distributor', function () {
    $selectedMarket = Market::factory()->create();
    $otherMarket = Market::factory()->create();
    $selectedDistributor = Distributor::factory()->create();
    $otherDistributor = Distributor::factory()->create();
    $uptd = User::factory()->create(['role' => Role::UptdPasar]);
    $contributor = UptdContributor::factory()->create();

    Distribution::factory()->create([
        'distributor_id' => $selectedDistributor->id,
        'market_id' => $selectedMarket->id,
        'uptd_user_id' => $uptd->id,
        'distribution_date' => now()->startOfMonth()->toDateString(),
        'cartons' => 100,
    ]);

    Distribution::factory()->create([
        'distributor_id' => $otherDistributor->id,
        'market_id' => $otherMarket->id,
        'uptd_user_id' => $uptd->id,
        'distribution_date' => now()->startOfMonth()->toDateString(),
        'cartons' => 50,
    ]);

    FieldDistributionReport::factory()->create([
        'market_id' => $selectedMarket->id,
        'uptd_contributor_id' => $contributor->id,
        'report_date' => now()->startOfMonth()->toDateString(),
    ]);

    FieldDistributionReport::factory()->create([
        'market_id' => $otherMarket->id,
        'uptd_contributor_id' => $contributor->id,
        'report_date' => now()->startOfMonth()->toDateString(),
    ]);

    $this->actingAs(User::factory()->create());

    $this->get(route('admin.dashboard', ['market' => $selectedMarket->id]))
        ->assertOk()
        ->assertSeeInOrder(['Total Laporan', '2'])
        ->assertViewHas('summary', fn (array $summary): bool => $summary['distribution_reports'] === 1
            && $summary['condition_reports'] === 1
            && $summary['reports'] === 2);

    $this->get(route('admin.dashboard', ['distributor' => $selectedDistributor->id]))
        ->assertOk()
        ->assertSeeInOrder(['Total Laporan', '3'])
        ->assertViewHas('summary', fn (array $summary): bool => $summary['distribution_reports'] === 1
            && $summary['condition_reports'] === 2
            && $summary['reports'] === 3);
});

it('memfilter total penyaluran berdasarkan pasar yang dipilih', function () {
    $firstMarket = Market::factory()->create();
    $secondMarket = Market::factory()->create();
    $uptd = User::factory()->create(['role' => Role::UptdPasar]);

    Distribution::factory()->create([
        'market_id' => $firstMarket->id,
        'uptd_user_id' => $uptd->id,
        'distribution_date' => now()->startOfMonth()->toDateString(),
        'cartons' => 250,
    ]);

    Distribution::factory()->create([
        'market_id' => $secondMarket->id,
        'uptd_user_id' => $uptd->id,
        'distribution_date' => now()->startOfMonth()->toDateString(),
        'cartons' => 180,
    ]);

    $response = $this->actingAs(User::factory()->create())
        ->get(route('admin.dashboard', ['market' => $firstMarket->id]));

    $response->assertOk()
        ->assertSee('value="'.$firstMarket->id.'" selected', false)
        ->assertSeeInOrder(['Total Laporan', '1'])
        ->assertSeeInOrder(['Total Penyaluran', '250'])
        ->assertDontSee('Belum ada data pembanding');

    preg_match('/const chartData = (\{.*?\});/s', $response->getContent(), $matches);

    $chartData = json_decode($matches[1], true);

    expect(array_sum($chartData['values']))->toBe(250);
});

it('mengisi grafik bulanan dari total karton per tanggal', function () {
    $distributor = Distributor::factory()->create();
    $market = Market::factory()->create();
    $uptd = User::factory()->create(['role' => Role::UptdPasar]);
    $firstDay = now()->startOfMonth();

    foreach ([[$firstDay, 100], [$firstDay, 50], [$firstDay->copy()->addDay(), 75]] as [$date, $cartons]) {
        Distribution::factory()->create([
            'distributor_id' => $distributor->id,
            'market_id' => $market->id,
            'uptd_user_id' => $uptd->id,
            'distribution_date' => $date->toDateString(),
            'cartons' => $cartons,
        ]);
    }

    $response = $this->actingAs(User::factory()->create())
        ->get(route('admin.dashboard'));

    $response->assertOk();

    preg_match('/const chartData = (\{.*?\});/s', $response->getContent(), $matches);

    $chartData = json_decode($matches[1], true);

    expect($chartData['values'][0])->toBe(150)
        ->and($chartData['values'][1])->toBe(75)
        ->and(array_sum($chartData['values']))->toBe(225);
});

it('menampilkan keadaan kosong saat master belum berisi data', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertDontSee('Belum ada data laporan')
        ->assertDontSee('Belum ada data pasar');
});

it('mengambil pilihan filter pasar dan distributor dari database', function () {
    $distributor = Distributor::factory()->create(['name' => 'PT Contoh Distributor']);
    $market = Market::factory()->create(['name' => 'Pasar Contoh']);

    $this->actingAs(User::factory()->create())
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('PT Contoh Distributor')
        ->assertSee('Pasar Contoh')
        ->assertDontSee('PT Perusahaan Perdagangan Indonesia (PPI)')
        ->assertDontSee('Pasar Raya')
        ->assertSee('value="'.$distributor->id.'"', false)
        ->assertSee('value="'.$market->id.'"', false);
});

it('menampilkan filter dengan pilihan default bulan berjalan', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('Semua Pasar')
        ->assertSee('Semua Distributor')
        ->assertSee('Terapkan Filter')
        ->assertSee('value="'.now()->format('Y-m').'" selected', false);
});

it('menerapkan filter distributor pada kartu statistik', function () {
    $distributor = Distributor::factory()->create(['name' => 'PT Filter Distributor']);
    $otherDistributor = Distributor::factory()->create();
    $market = Market::factory()->create();
    $uptd = User::factory()->create(['role' => Role::UptdPasar]);

    Distribution::factory()->create([
        'distributor_id' => $distributor->id,
        'market_id' => $market->id,
        'uptd_user_id' => $uptd->id,
        'distribution_date' => now()->startOfMonth()->toDateString(),
        'cartons' => 100,
    ]);

    Distribution::factory()->create([
        'distributor_id' => $otherDistributor->id,
        'market_id' => $market->id,
        'uptd_user_id' => $uptd->id,
        'distribution_date' => now()->startOfMonth()->toDateString(),
        'cartons' => 250,
    ]);

    $this->actingAs(User::factory()->create())
        ->get(route('admin.dashboard', ['distributor' => $distributor->id]))
        ->assertOk()
        ->assertSee('PT Filter Distributor')
        ->assertSee('value="'.$distributor->id.'" selected', false)
        ->assertSeeInOrder(['Total Laporan', '1'])
        ->assertSeeInOrder(['Total Penyaluran', '100'])
        ->assertDontSee('250');
});

it('menempatkan filter di bawah card sapaan dan statistik', function () {
    $this->actingAs(User::factory()->create(['name' => 'Admin Filter']))
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSeeInOrder(['Hai, Admin Filter', 'Total Laporan', 'Total Penyaluran', 'Terapkan Filter']);
});

it('memfilter total laporan dan penyaluran berdasarkan bulan yang dipilih', function () {
    $market = Market::factory()->create();
    $uptd = User::factory()->create(['role' => Role::UptdPasar]);
    $previousMonth = now()->subMonthNoOverflow()->startOfMonth();

    Distribution::factory()->create([
        'market_id' => $market->id,
        'uptd_user_id' => $uptd->id,
        'distribution_date' => now()->startOfMonth()->toDateString(),
        'cartons' => 100,
    ]);

    Distribution::factory()->create([
        'market_id' => $market->id,
        'uptd_user_id' => $uptd->id,
        'distribution_date' => $previousMonth->toDateString(),
        'cartons' => 50,
    ]);

    $this->actingAs(User::factory()->create())
        ->get(route('admin.dashboard', ['month' => $previousMonth->format('Y-m')]))
        ->assertOk()
        ->assertSee('value="'.$previousMonth->format('Y-m').'" selected', false)
        ->assertSeeInOrder(['Total Laporan', '1'])
        ->assertSeeInOrder(['Total Penyaluran', '50']);
});

it('kembali ke filter default saat nilai filter tidak valid', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('admin.dashboard', ['month' => 'bukan-bulan', 'market' => '999999', 'distributor' => 'abc']))
        ->assertOk()
        ->assertSee('Semua Pasar')
        ->assertSee('Semua Distributor')
        ->assertSee('value="'.now()->format('Y-m').'" selected', false);
});

it('menampilkan panel grafik perkembangan bulanan dengan nilai nol', function () {
    $response = $this->actingAs(User::factory()->create())
        ->get(route('admin.dashboard'));

    $response->assertOk()
        ->assertSee('Perkembangan Bulanan')
        ->assertSee('id="monthly-chart"', false)
        ->assertSee("type: 'line'", false);

    preg_match('/const chartData = (\{.*?\});/s', $response->getContent(), $matches);

    $chartData = json_decode($matches[1], true);

    expect($chartData['values'])->toHaveCount(now()->daysInMonth)
        ->and(array_sum($chartData['values']))->toBe(0);
});

it('menampilkan distributor pada panel perbandingan dengan nilai nol', function () {
    Distributor::factory()->create(['name' => 'PT Panel Distributor']);

    $this->actingAs(User::factory()->create())
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('Penyaluran per Distributor')
        ->assertSeeInOrder(['PT Panel Distributor', '0']);
});
