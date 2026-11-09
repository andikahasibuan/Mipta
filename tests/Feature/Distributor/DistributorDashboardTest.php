<?php

use App\Enums\Role;
use App\Models\Distribution;
use App\Models\Distributor;
use App\Models\Market;
use App\Models\RecipientStore;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Buat akun distributor yang terhubung ke satu data PT.
 *
 * @return array{0: Distributor, 1: User}
 */
function distributorAccount(string $name = 'PT Contoh Distributor'): array
{
    $distributor = Distributor::factory()->create(['name' => $name]);

    $user = User::factory()->create([
        'role' => Role::Distributor,
        'distributor_id' => $distributor->id,
    ]);

    return [$distributor, $user];
}

it('menampilkan dashboard distributor dengan identitas perusahaan', function () {
    [, $user] = distributorAccount('PT Rajawali Nusantara Indonesia');

    $this->actingAs($user)
        ->get(route('distributor.dashboard'))
        ->assertOk()
        ->assertSee('Dashboard Distributor')
        ->assertSee('Pantau laporan penyaluran milik PT Rajawali Nusantara Indonesia')
        ->assertSee('Selamat datang, PT Rajawali Nusantara Indonesia')
        ->assertSee('Periode aktif')
        ->assertSee('Total Laporan')
        ->assertSee('Jumlah Penyaluran')
        ->assertSee('Laporan Draf')
        ->assertSee('Laporan Penyaluran')
        ->assertSee('value="'.now()->format('Y-m').'" selected', false)
        ->assertDontSee('MIPTA / DASHBOARD DISTRIBUTOR')
        ->assertDontSee('Buat Laporan')
        ->assertDontSee('Lihat semua');
});

it('menghitung ringkasan hanya dari laporan terkirim milik distributor', function () {
    [$distributor, $user] = distributorAccount();
    $otherDistributor = Distributor::factory()->create();
    $market = Market::factory()->create();
    $store = RecipientStore::factory()->create(['market_id' => $market->id]);

    Distribution::factory()->create([
        'distributor_id' => $distributor->id,
        'market_id' => $market->id,
        'recipient_store_id' => $store->id,
        'distribution_date' => now()->startOfMonth()->toDateString(),
        'cartons' => 250,
    ]);

    Distribution::factory()->create([
        'distributor_id' => $distributor->id,
        'market_id' => $market->id,
        'recipient_store_id' => $store->id,
        'distribution_date' => now()->startOfMonth()->addDay()->toDateString(),
        'cartons' => 150,
    ]);

    Distribution::factory()->draft()->create([
        'distributor_id' => $distributor->id,
        'market_id' => $market->id,
        'recipient_store_id' => $store->id,
        'distribution_date' => now()->startOfMonth()->toDateString(),
        'cartons' => 333,
    ]);

    Distribution::factory()->create([
        'distributor_id' => $otherDistributor->id,
        'market_id' => $market->id,
        'distribution_date' => now()->startOfMonth()->toDateString(),
        'cartons' => 777,
    ]);

    $this->actingAs($user)
        ->get(route('distributor.dashboard'))
        ->assertOk()
        ->assertSeeInOrder(['Total Laporan', '2'])
        ->assertSeeInOrder(['Jumlah Penyaluran', '400'])
        ->assertSeeInOrder(['Laporan Draf', '1'])
        ->assertSee('250 karton')
        ->assertSee('150 karton')
        ->assertDontSee('333 karton')
        ->assertDontSee('777 karton');
});

it('memfilter ringkasan dan daftar laporan berdasarkan bulan', function () {
    [$distributor, $user] = distributorAccount();
    $market = Market::factory()->create();
    $previousMonth = now()->subMonthNoOverflow()->startOfMonth();

    Distribution::factory()->create([
        'distributor_id' => $distributor->id,
        'market_id' => $market->id,
        'distribution_date' => now()->startOfMonth()->toDateString(),
        'cartons' => 100,
    ]);

    Distribution::factory()->create([
        'distributor_id' => $distributor->id,
        'market_id' => $market->id,
        'distribution_date' => $previousMonth->toDateString(),
        'cartons' => 50,
    ]);

    Distribution::factory()->draft()->create([
        'distributor_id' => $distributor->id,
        'market_id' => $market->id,
        'distribution_date' => $previousMonth->toDateString(),
        'cartons' => 20,
    ]);

    $this->actingAs($user)
        ->get(route('distributor.dashboard'))
        ->assertOk()
        ->assertSeeInOrder(['Total Laporan', '1'])
        ->assertSeeInOrder(['Jumlah Penyaluran', '100'])
        ->assertSeeInOrder(['Laporan Draf', '0'])
        ->assertSee('100 karton')
        ->assertDontSee('50 karton');

    $this->get(route('distributor.dashboard', ['month' => $previousMonth->format('Y-m')]))
        ->assertOk()
        ->assertSeeInOrder(['Total Laporan', '1'])
        ->assertSeeInOrder(['Jumlah Penyaluran', '50'])
        ->assertSeeInOrder(['Laporan Draf', '1'])
        ->assertSee('50 karton')
        ->assertDontSee('100 karton')
        ->assertSee('value="'.$previousMonth->format('Y-m').'" selected', false);
});

it('menampilkan lima laporan terkirim per halaman dengan pagination', function () {
    [$distributor, $user] = distributorAccount();
    $market = Market::factory()->create();

    foreach (range(1, 6) as $index) {
        Distribution::factory()->create([
            'distributor_id' => $distributor->id,
            'market_id' => $market->id,
            'distribution_date' => now()->startOfMonth()->toDateString(),
            'cartons' => 10,
        ]);
    }

    $this->actingAs($user)
        ->get(route('distributor.dashboard'))
        ->assertOk()
        ->assertSee('Menampilkan 5 dari 6 laporan')
        ->assertSee('LP-006')
        ->assertDontSee('LP-001')
        ->assertSee('?page=2', false);

    $this->get(route('distributor.dashboard', ['page' => 2]))
        ->assertOk()
        ->assertSee('LP-001')
        ->assertDontSee('LP-006');
});

it('tidak menampilkan laporan draf pada tabel laporan penyaluran', function () {
    [$distributor, $user] = distributorAccount();
    $market = Market::factory()->create();

    Distribution::factory()->create([
        'distributor_id' => $distributor->id,
        'market_id' => $market->id,
        'distribution_date' => now()->startOfMonth()->toDateString(),
        'cartons' => 111,
    ]);

    Distribution::factory()->draft()->create([
        'distributor_id' => $distributor->id,
        'market_id' => $market->id,
        'distribution_date' => now()->startOfMonth()->toDateString(),
        'cartons' => 222,
    ]);

    $this->actingAs($user)
        ->get(route('distributor.dashboard'))
        ->assertOk()
        ->assertSee('111 karton')
        ->assertDontSee('222 karton');
});

it('menampilkan keadaan kosong saat belum ada laporan', function () {
    [, $user] = distributorAccount();

    $this->actingAs($user)
        ->get(route('distributor.dashboard'))
        ->assertOk()
        ->assertSee('Belum ada laporan penyaluran pada periode ini')
        ->assertSee('Tidak ada laporan draf')
        ->assertSeeInOrder(['Total Laporan', '0'])
        ->assertSeeInOrder(['Jumlah Penyaluran', '0'])
        ->assertSeeInOrder(['Laporan Draf', '0']);
});

it('kembali ke bulan berjalan saat parameter bulan tidak valid', function () {
    [, $user] = distributorAccount();

    $this->actingAs($user)
        ->get(route('distributor.dashboard', ['month' => 'bukan-bulan']))
        ->assertOk()
        ->assertSee('value="'.now()->format('Y-m').'" selected', false);
});
