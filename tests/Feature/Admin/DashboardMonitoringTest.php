<?php

use App\Enums\Role;
use App\Models\Distribution;
use App\Models\Distributor;
use App\Models\Market;
use App\Models\MarketMonitoring;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('menampilkan sapaan pada dashboard monitoring', function () {
    $admin = User::factory()->create(['name' => 'Iqbal Shyhab']);

    $this->actingAs($admin)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('Selamat datang, Iqbal Shyhab')
        ->assertSee('Pantau penyaluran Minyakita secara terintegrasi')
        ->assertDontSee('Unduh Rekap Bulanan');
});

it('menampilkan kartu statistik dari database', function () {
    Distributor::factory()->create(['is_active' => true]);
    Distributor::factory()->create(['is_active' => false]);
    $markets = Market::factory()->count(2)->create();
    $uptd = User::factory()->create(['role' => Role::UptdPasar]);

    foreach ($markets as $market) {
        MarketMonitoring::factory()->for($market)->create([
            'uptd_user_id' => $uptd->id,
            'monitored_at' => now()->toDateString(),
        ]);
    }

    $this->actingAs(User::factory()->create())
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSeeInOrder([
            'Total Laporan', '0', 'Belum ada data laporan',
            'Total Penyaluran', '0',
            'Distributor Aktif', '1', '1 dari 2 distributor aktif',
            'Pasar Terpantau', '2', '100% wilayah terpantau',
        ]);
});

it('menghitung total laporan dan penyaluran dari data penyaluran', function () {
    $firstDistributor = Distributor::factory()->create(['name' => 'PT Alpha Niaga']);
    $secondDistributor = Distributor::factory()->create(['name' => 'PT Beta Pangan']);
    $firstMarket = Market::factory()->create();
    $secondMarket = Market::factory()->create();
    $uptd = User::factory()->create(['role' => Role::UptdPasar]);

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

    $this->actingAs(User::factory()->create())
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSeeInOrder(['Total Laporan', '2', '+100,0% dari bulan lalu'])
        ->assertSeeInOrder(['Total Penyaluran', '430'])
        ->assertSeeInOrder(['PT Alpha Niaga', '250'])
        ->assertSeeInOrder(['PT Beta Pangan', '180']);
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
        ->assertSeeInOrder(['Total Penyaluran', '250']);

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
        ->assertSee('Belum ada data laporan')
        ->assertSee('Belum ada data distributor')
        ->assertSee('Belum ada data pasar');
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
        ->assertSee('value="'.now()->format('Y-m').'" selected', false)
        ->assertSeeInOrder(['for="filter-month"', 'M6.75 3v2.25M17.25 3v2.25', 'id="filter-month"'], false)
        ->assertSeeInOrder(['for="filter-market"', 'M19.5 10.5c0 7.142-7.5 11.25', 'id="filter-market"'], false)
        ->assertSeeInOrder(['for="filter-distributor"', 'M15.75 6a3.75 3.75 0 1 1-7.5 0', 'id="filter-distributor"'], false)
        ->assertSeeInOrder(['type="submit"', 'M12 3c2.755 0 5.455.232 8.083.678', 'Terapkan Filter'], false);
});

it('menerapkan filter distributor pada kartu statistik', function () {
    $distributor = Distributor::factory()->create(['name' => 'PT Filter Distributor']);
    Distributor::factory()->create();

    $this->actingAs(User::factory()->create())
        ->get(route('admin.dashboard', ['distributor' => $distributor->id]))
        ->assertOk()
        ->assertSee('PT Filter Distributor')
        ->assertSee('value="'.$distributor->id.'" selected', false)
        ->assertSeeInOrder(['Distributor Aktif', '1', 'Seluruhnya aktif']);
});

it('mempertahankan cakupan pasar global saat filter pasar diterapkan', function () {
    $markets = Market::factory()->count(2)->create();
    $uptd = User::factory()->create(['role' => Role::UptdPasar]);

    MarketMonitoring::factory()->for($markets[0])->create([
        'uptd_user_id' => $uptd->id,
        'monitored_at' => now()->toDateString(),
    ]);

    $this->actingAs(User::factory()->create())
        ->get(route('admin.dashboard', ['market' => $markets[1]->id]))
        ->assertOk()
        ->assertSee('value="'.$markets[1]->id.'" selected', false)
        ->assertSeeInOrder(['Pasar Terpantau', '1', '50% wilayah terpantau']);
});

it('menghitung cakupan pasar berdasarkan bulan yang dipilih', function () {
    $markets = Market::factory()->count(3)->create();
    $uptd = User::factory()->create(['role' => Role::UptdPasar]);
    $currentMonth = now()->startOfMonth();
    $previousMonth = now()->subMonthNoOverflow()->startOfMonth();

    foreach ([$markets[0], $markets[1]] as $market) {
        MarketMonitoring::factory()->for($market)->create([
            'uptd_user_id' => $uptd->id,
            'monitored_at' => $currentMonth->toDateString(),
        ]);
    }

    MarketMonitoring::factory()->for($markets[2])->create([
        'uptd_user_id' => $uptd->id,
        'monitored_at' => $previousMonth->toDateString(),
    ]);

    $this->actingAs(User::factory()->create())
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSeeInOrder(['Pasar Terpantau', '2', '67% wilayah terpantau']);

    $this->get(route('admin.dashboard', ['month' => $previousMonth->format('Y-m')]))
        ->assertOk()
        ->assertSeeInOrder(['Pasar Terpantau', '1', '33% wilayah terpantau']);
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

it('tidak menghitung laporan draft pada dashboard admin', function () {
    $distributor = Distributor::factory()->create();
    $market = Market::factory()->create();

    Distribution::factory()->create([
        'distributor_id' => $distributor->id,
        'market_id' => $market->id,
        'distribution_date' => now()->startOfMonth()->toDateString(),
        'cartons' => 100,
    ]);

    Distribution::factory()->draft()->create([
        'distributor_id' => $distributor->id,
        'market_id' => $market->id,
        'distribution_date' => now()->startOfMonth()->toDateString(),
        'cartons' => 4321,
    ]);

    $this->actingAs(User::factory()->create())
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSeeInOrder(['Total Laporan', '1'])
        ->assertSeeInOrder(['Total Penyaluran', '100'])
        ->assertDontSee('4.321');
});
