<?php

use App\Enums\Role;
use App\Models\Market;
use App\Models\RecipientStore;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('menampilkan daftar toko penerima beserta pasarnya', function () {
    $market = Market::factory()->create(['name' => 'Pasar Raya Padang']);

    RecipientStore::factory()->create([
        'market_id' => $market->id,
        'code' => 'TKS-001',
        'name' => 'Toko Berkah',
    ]);
    RecipientStore::factory()->create([
        'market_id' => $market->id,
        'code' => 'TKS-002',
        'name' => 'Toko Nonaktif',
        'is_active' => false,
    ]);

    $this->actingAs(User::factory()->create())
        ->get(route('admin.recipient-stores.index'))
        ->assertOk()
        ->assertSeeInOrder(['Kode', 'Nama', 'Pasar', 'Status'])
        ->assertSee('TKS-001')
        ->assertSee('Toko Berkah')
        ->assertSee('Pasar Raya Padang')
        ->assertSee('TKS-002')
        ->assertSee('Nonaktif');
});

it('menampilkan keadaan kosong saat belum ada toko penerima', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('admin.recipient-stores.index'))
        ->assertOk()
        ->assertSee('Belum ada data toko penerima');
});

it('menampilkan navigasi tab hingga toko penerima', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('admin.recipient-stores.index'))
        ->assertOk()
        ->assertSee('href="'.route('admin.users.index').'"', false)
        ->assertSee('href="'.route('admin.distributors.index').'"', false)
        ->assertSee('href="'.route('admin.markets.index').'"', false)
        ->assertSee('href="'.route('admin.recipient-stores.index').'"', false);
});

it('menampilkan pagination saat toko lebih dari sepuluh', function () {
    RecipientStore::factory()->count(11)->create();

    $this->actingAs(User::factory()->create())
        ->get(route('admin.recipient-stores.index'))
        ->assertOk()
        ->assertSee('?page=2', false);
});

it('mencari toko berdasarkan kode atau nama', function () {
    $admin = User::factory()->create();

    RecipientStore::factory()->create(['code' => 'TKS-AAA', 'name' => 'Toko Berkah']);
    RecipientStore::factory()->create(['code' => 'TKS-BBB', 'name' => 'Toko Makmur']);

    $this->actingAs($admin)
        ->get(route('admin.recipient-stores.index', ['q' => 'Berkah']))
        ->assertOk()
        ->assertSee('Toko Berkah')
        ->assertDontSee('Toko Makmur');

    $this->actingAs($admin)
        ->get(route('admin.recipient-stores.index', ['q' => 'TKS-BBB']))
        ->assertOk()
        ->assertSee('Toko Makmur')
        ->assertDontSee('Toko Berkah');
});

it('memfilter toko berdasarkan pasar dan status', function () {
    $admin = User::factory()->create();
    $firstMarket = Market::factory()->create(['name' => 'Pasar Satu']);
    $secondMarket = Market::factory()->create(['name' => 'Pasar Dua']);

    RecipientStore::factory()->create([
        'name' => 'Toko Pasar Satu',
        'market_id' => $firstMarket->id,
    ]);
    RecipientStore::factory()->create([
        'name' => 'Toko Pasar Dua',
        'market_id' => $secondMarket->id,
        'is_active' => false,
    ]);

    $this->actingAs($admin)
        ->get(route('admin.recipient-stores.index', ['market' => $firstMarket->id]))
        ->assertOk()
        ->assertSee('Toko Pasar Satu')
        ->assertDontSee('Toko Pasar Dua');

    $this->actingAs($admin)
        ->get(route('admin.recipient-stores.index', ['status' => 'nonaktif']))
        ->assertOk()
        ->assertSee('Toko Pasar Dua')
        ->assertDontSee('Toko Pasar Satu');
});

it('menampilkan pesan kosong saat filter toko tidak menemukan data', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('admin.recipient-stores.index', ['q' => 'tidak-ada']))
        ->assertOk()
        ->assertSee('Tidak ada toko penerima yang sesuai filter')
        ->assertDontSee('Belum ada data toko penerima');
});

it('mempertahankan filter pasar pada tautan pagination toko', function () {
    $market = Market::factory()->create();
    RecipientStore::factory()->count(11)->create(['market_id' => $market->id]);

    $this->actingAs(User::factory()->create())
        ->get(route('admin.recipient-stores.index', ['market' => $market->id]))
        ->assertOk()
        ->assertSee('market='.$market->id, false);
});

it('membatasi halaman toko penerima hanya untuk admin', function () {
    $this->get(route('admin.recipient-stores.index'))->assertRedirect(route('login'));

    $this->actingAs(User::factory()->create(['role' => Role::Distributor]))
        ->get(route('admin.recipient-stores.index'))
        ->assertForbidden();
});
