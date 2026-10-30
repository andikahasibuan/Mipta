<?php

use App\Models\Distributor;
use App\Models\Market;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('menampilkan sapaan dan tombol unduh pada dashboard monitoring', function () {
    $admin = User::factory()->create(['name' => 'Iqbal Shyhab']);

    $this->actingAs($admin)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('Selamat datang, Iqbal Shyhab')
        ->assertSee('Pantau penyaluran Minyakita secara terintegrasi')
        ->assertSee('Unduh Rekap Bulanan')
        ->assertSee('aria-disabled="true"', false)
        ->assertSee('title="Akan tersedia setelah modul laporan selesai"', false);
});

it('menampilkan kartu statistik dari database', function () {
    Distributor::factory()->create(['is_active' => true]);
    Distributor::factory()->create(['is_active' => false]);
    Market::factory()->count(2)->create();

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
        ->assertSee('value="'.now()->format('Y-m').'" selected', false);
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

it('menerapkan filter pasar pada kartu statistik', function () {
    $market = Market::factory()->create();
    Market::factory()->create();

    $this->actingAs(User::factory()->create())
        ->get(route('admin.dashboard', ['market' => $market->id]))
        ->assertOk()
        ->assertSee('value="'.$market->id.'" selected', false)
        ->assertSeeInOrder(['Pasar Terpantau', '1', '100% wilayah terpantau']);
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
