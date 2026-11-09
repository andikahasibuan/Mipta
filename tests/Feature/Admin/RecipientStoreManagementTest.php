<?php

use App\Enums\Role;
use App\Models\Distribution;
use App\Models\Distributor;
use App\Models\Market;
use App\Models\RecipientStore;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('menampilkan daftar toko penerima beserta pasarnya', function () {
    $market = Market::factory()->create(['name' => 'Pasar Raya Padang']);

    RecipientStore::factory()->create([
        'market_id' => $market->id,
        'name' => 'Toko Berkah',
    ]);
    RecipientStore::factory()->create([
        'market_id' => $market->id,
        'name' => 'Toko Nonaktif',
        'is_active' => false,
    ]);

    $this->actingAs(User::factory()->create())
        ->get(route('admin.recipient-stores.index'))
        ->assertOk()
        ->assertSeeInOrder(['ID', 'Nama Toko', 'Alamat', 'NIB', 'Pasar', 'Distributor', 'Status', 'Aksi'])
        ->assertSee('TKS-001')
        ->assertSee('Toko Berkah')
        ->assertSee('Pasar Raya Padang')
        ->assertSee('TKS-002')
        ->assertSee('Nonaktif');
});

it('menampilkan toko yang dibuat distributor pada halaman toko penerima admin', function () {
    $distributor = Distributor::factory()->create(['name' => 'PT Amanah Niaga Mandiri']);
    $market = Market::factory()->create(['name' => 'Pasar Raya Padang']);

    RecipientStore::factory()->create([
        'distributor_id' => $distributor->id,
        'market_id' => $market->id,
        'name' => 'Toko Distributor',
        'alamat' => 'Jln. Merdeka No. 1',
    ]);

    $this->actingAs(User::factory()->create())
        ->get(route('admin.recipient-stores.index'))
        ->assertOk()
        ->assertSee('Toko Distributor')
        ->assertSee('Jln. Merdeka No. 1')
        ->assertSee('PT Amanah Niaga Mandiri');
});

it('memfilter toko berdasarkan distributor', function () {
    $firstDistributor = Distributor::factory()->create(['name' => 'PT Distributor Satu']);
    $secondDistributor = Distributor::factory()->create(['name' => 'PT Distributor Dua']);
    $market = Market::factory()->create();

    RecipientStore::factory()->create([
        'distributor_id' => $firstDistributor->id,
        'market_id' => $market->id,
        'name' => 'Toko Distributor Satu',
    ]);

    RecipientStore::factory()->create([
        'distributor_id' => $secondDistributor->id,
        'market_id' => $market->id,
        'name' => 'Toko Distributor Dua',
    ]);

    $this->actingAs(User::factory()->create())
        ->get(route('admin.recipient-stores.index', ['distributor' => $firstDistributor->id]))
        ->assertOk()
        ->assertSee('Toko Distributor Satu')
        ->assertDontSee('Toko Distributor Dua');
});

it('menyimpan alamat dan distributor saat admin menambah toko', function () {
    $distributor = Distributor::factory()->create();
    $market = Market::factory()->create();

    $this->actingAs(User::factory()->create())
        ->post(route('admin.recipient-stores.store'), [
            'market_id' => $market->id,
            'distributor_id' => $distributor->id,
            'name' => 'Toko Baru Admin',
            'alamat' => 'Jln. Baru No. 1',
            'nib' => '1234567890123',
            'is_active' => '1',
        ])
        ->assertRedirect(route('admin.recipient-stores.index'));

    $this->assertDatabaseHas('recipient_stores', [
        'distributor_id' => $distributor->id,
        'market_id' => $market->id,
        'name' => 'Toko Baru Admin',
        'alamat' => 'Jln. Baru No. 1',
    ]);
});

it('memperbarui alamat dan distributor toko dari admin', function () {
    $distributor = Distributor::factory()->create();
    $market = Market::factory()->create();
    $store = RecipientStore::factory()->create(['market_id' => $market->id]);

    $this->actingAs(User::factory()->create())
        ->put(route('admin.recipient-stores.update', $store), [
            'market_id' => $market->id,
            'distributor_id' => $distributor->id,
            'name' => $store->name,
            'alamat' => 'Alamat Baru',
            'nib' => '9876543210123',
            'is_active' => '1',
        ])
        ->assertRedirect(route('admin.recipient-stores.index'));

    $this->assertDatabaseHas('recipient_stores', [
        'id' => $store->id,
        'distributor_id' => $distributor->id,
        'alamat' => 'Alamat Baru',
        'nib' => '9876543210123',
    ]);
});

it('mewajibkan NIB saat admin menambah toko', function () {
    $market = Market::factory()->create();

    $this->actingAs(User::factory()->create())
        ->post(route('admin.recipient-stores.store'), [
            'market_id' => $market->id,
            'name' => 'Toko Tanpa NIB',
            'alamat' => 'Jln. Merdeka No. 1',
        ])
        ->assertSessionHasErrors('nib');
});

it('memvalidasi format nama, alamat, dan NIB toko dari admin', function () {
    $market = Market::factory()->create();

    $this->actingAs(User::factory()->create())
        ->post(route('admin.recipient-stores.store'), [
            'market_id' => $market->id,
            'name' => 'Toko !@#',
            'alamat' => 'Jln. Merdeka @ 1',
            'nib' => '12345ABC90123',
        ])
        ->assertSessionHasErrors(['name', 'alamat', 'nib']);

    $this->assertDatabaseCount('recipient_stores', 0);
});

it('menghapus laporan penyaluran saat toko penerima dihapus', function () {
    $store = RecipientStore::factory()->create();
    $distribution = Distribution::factory()->create([
        'recipient_store_id' => $store->id,
        'market_id' => $store->market_id,
    ]);

    $this->actingAs(User::factory()->create())
        ->delete(route('admin.recipient-stores.destroy', $store))
        ->assertRedirect(route('admin.recipient-stores.index'));

    $this->assertDatabaseMissing('recipient_stores', ['id' => $store->id]);
    $this->assertDatabaseMissing('distributions', ['id' => $distribution->id]);
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

it('mencari toko berdasarkan ID data atau nama', function () {
    $admin = User::factory()->create();

    RecipientStore::factory()->create(['name' => 'Toko Berkah']);
    $secondStore = RecipientStore::factory()->create(['name' => 'Toko Makmur']);

    $this->actingAs($admin)
        ->get(route('admin.recipient-stores.index', ['q' => 'Berkah']))
        ->assertOk()
        ->assertSee('Toko Berkah')
        ->assertDontSee('Toko Makmur');

    $this->actingAs($admin)
        ->get(route('admin.recipient-stores.index', ['q' => $secondStore->code]))
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
