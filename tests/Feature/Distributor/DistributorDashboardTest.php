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
        ->assertSee('PT Rajawali Nusantara Indonesia')
        ->assertDontSee('Periode aktif')
        ->assertSee('Total Laporan')
        ->assertSee('Jumlah Penyaluran')
        ->assertSee('Laporan Penyaluran')
        ->assertSeeInOrder(['NO', 'Tanggal Penyaluran', 'Nama Toko', 'Jumlah Disalurkan (Karton)', 'Alamat', 'Nomor NIB'])
        ->assertDontSee('>Pasar</th>', false)
        ->assertDontSee('>Keterangan</th>', false)
        ->assertDontSee('MIPTA / DASHBOARD DISTRIBUTOR')
        ->assertDontSee('Buat Laporan')
        ->assertDontSee('Lihat semua');
});

it('menghitung ringkasan hanya dari laporan terkirim milik distributor', function () {
    [$distributor, $user] = distributorAccount();
    $otherDistributor = Distributor::factory()->create();
    $market = Market::factory()->create();
    $store = RecipientStore::factory()->create([
        'market_id' => $market->id,
        'name' => 'toko dashboard',
        'alamat' => 'jl. dashboard no. 1',
        'nib' => '1234567890123',
    ]);

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

    Distribution::factory()->create([
        'distributor_id' => $otherDistributor->id,
        'market_id' => $market->id,
        'distribution_date' => now()->startOfMonth()->toDateString(),
        'cartons' => 777,
    ]);

    $response = $this->actingAs($user)
        ->get(route('distributor.dashboard'))
        ->assertOk()
        ->assertSeeInOrder(['Total Laporan', '2'])
        ->assertSeeInOrder(['Jumlah Penyaluran', '400'])
        ->assertSee('Toko Dashboard')
        ->assertSee('Jl. Dashboard No. 1')
        ->assertSee('1234567890123')
        ->assertDontSee('toko dashboard');

    expect($response->viewData('reports')->pluck('cartons')->all())->toBe([150, 250]);
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

    $currentMonth = $this->actingAs($user)
        ->get(route('distributor.dashboard'))
        ->assertOk()
        ->assertSeeInOrder(['Total Laporan', '1'])
        ->assertSeeInOrder(['Jumlah Penyaluran', '100']);

    expect($currentMonth->viewData('reports')->pluck('cartons')->all())->toBe([100]);

    $previousPeriod = $this->get(route('distributor.dashboard', ['month' => $previousMonth->format('Y-m')]))
        ->assertOk()
        ->assertSeeInOrder(['Total Laporan', '1'])
        ->assertSeeInOrder(['Jumlah Penyaluran', '50']);

    expect($previousPeriod->viewData('reports')->pluck('cartons')->all())->toBe([50]);
});

it('memfilter ringkasan dan daftar laporan berdasarkan nama toko', function () {
    [$distributor, $user] = distributorAccount();
    $market = Market::factory()->create();
    $firstStore = RecipientStore::factory()->create([
        'distributor_id' => $distributor->id,
        'market_id' => $market->id,
        'name' => 'Toko Alpha',
    ]);
    $secondStore = RecipientStore::factory()->create([
        'distributor_id' => $distributor->id,
        'market_id' => $market->id,
        'name' => 'Toko Beta',
    ]);

    Distribution::factory()->create([
        'distributor_id' => $distributor->id,
        'market_id' => $market->id,
        'recipient_store_id' => $firstStore->id,
        'distribution_date' => now()->startOfMonth()->toDateString(),
        'cartons' => 100,
    ]);

    Distribution::factory()->create([
        'distributor_id' => $distributor->id,
        'market_id' => $market->id,
        'recipient_store_id' => $secondStore->id,
        'distribution_date' => now()->startOfMonth()->toDateString(),
        'cartons' => 250,
    ]);

    $this->actingAs($user)
        ->get(route('distributor.dashboard', ['q' => 'Toko Alpha']))
        ->assertOk()
        ->assertSeeInOrder(['Total Laporan', '1'])
        ->assertSeeInOrder(['Jumlah Penyaluran', '100'])
        ->assertSee('Toko Alpha')
        ->assertDontSee('Toko Beta');
});

it('menampilkan lima laporan terkirim per halaman dengan pagination', function () {
    [$distributor, $user] = distributorAccount();
    $market = Market::factory()->create();

    foreach (range(1, 6) as $index) {
        Distribution::factory()->create([
            'distributor_id' => $distributor->id,
            'market_id' => $market->id,
            'distribution_date' => now()->startOfMonth()->toDateString(),
            'cartons' => $index,
        ]);
    }

    $firstPage = $this->actingAs($user)
        ->get(route('distributor.dashboard'))
        ->assertOk()
        ->assertSee('Menampilkan 5 dari 6 laporan')
        ->assertSee('?page=2', false);

    expect($firstPage->viewData('reports')->pluck('cartons')->all())->toBe([6, 5, 4, 3, 2]);

    $secondPage = $this->get(route('distributor.dashboard', ['page' => 2]))
        ->assertOk()
        ->assertSee('Menampilkan 1 dari 6 laporan');

    expect($secondPage->viewData('reports')->pluck('cartons')->all())->toBe([1]);
});

it('menampilkan keadaan kosong saat belum ada laporan', function () {
    [, $user] = distributorAccount();

    $this->actingAs($user)
        ->get(route('distributor.dashboard'))
        ->assertOk()
        ->assertSee('Belum ada laporan penyaluran pada periode ini')
        ->assertSeeInOrder(['Total Laporan', '0'])
        ->assertSeeInOrder(['Jumlah Penyaluran', '0']);
});

it('kembali ke bulan berjalan saat parameter bulan tidak valid', function () {
    [, $user] = distributorAccount();

    $this->actingAs($user)
        ->get(route('distributor.dashboard', ['month' => 'bukan-bulan']))
        ->assertOk()
        ->assertViewHas('summary', fn (array $summary): bool => $summary['month'] === now()->format('Y-m'));
});
