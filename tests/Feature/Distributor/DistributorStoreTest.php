<?php

use App\Enums\Role;
use App\Models\Distributor;
use App\Models\Market;
use App\Models\RecipientStore;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Buat distributor beserta akun login-nya.
 *
 * @return array{0: Distributor, 1: User}
 */
function storeDistributorAccount(string $name = 'PT Pemilik Toko'): array
{
    $distributor = Distributor::factory()->create(['name' => $name]);

    $user = User::factory()->create([
        'role' => Role::Distributor,
        'distributor_id' => $distributor->id,
    ]);

    return [$distributor, $user];
}

it('menampilkan daftar toko milik distributor dengan subjudul', function () {
    [$distributor, $user] = storeDistributorAccount();
    [, $otherUser] = storeDistributorAccount('PT Distributor Lain');
    $market = Market::factory()->create(['name' => 'Pasar Raya Padang']);

    $store = RecipientStore::factory()->create([
        'distributor_id' => $distributor->id,
        'market_id' => $market->id,
        'name' => 'Toko Milik Sendiri',
        'alamat' => 'Jln. Pasar Raya No. 1',
    ]);

    RecipientStore::factory()->create([
        'distributor_id' => $otherUser->distributor_id,
        'market_id' => $market->id,
        'name' => 'Toko Distributor Lain',
    ]);

    $this->actingAs($user)
        ->get(route('distributor.stores.index'))
        ->assertOk()
        ->assertSee('Manajemen data toko')
        ->assertSee('Tambah data')
        ->assertSee(route('distributor.stores.create'), false)
        ->assertSeeInOrder(['Nama Toko', 'Alamat', 'NIB', 'Pasar', 'Aksi'])
        ->assertDontSee('ID Data')
        ->assertDontSee('Status')
        ->assertSee('Toko Milik Sendiri')
        ->assertSee('Jln. Pasar Raya No. 1')
        ->assertDontSee('Toko Distributor Lain')
        ->assertSee(route('distributor.stores.edit', $store), false);
});

it('menampilkan sepuluh toko per halaman dengan pagination', function () {
    [$distributor, $user] = storeDistributorAccount();
    $market = Market::factory()->create();

    foreach (range(1, 11) as $index) {
        RecipientStore::factory()->create([
            'distributor_id' => $distributor->id,
            'market_id' => $market->id,
            'name' => 'Toko '.str_pad((string) $index, 2, '0', STR_PAD_LEFT),
        ]);
    }

    $this->actingAs($user)
        ->get(route('distributor.stores.index'))
        ->assertOk()
        ->assertSee('Menampilkan 10 dari 11 toko')
        ->assertSee('?page=2', false)
        ->assertSee('Toko 11')
        ->assertDontSee('Toko 01');

    $this->get(route('distributor.stores.index', ['page' => 2]))
        ->assertOk()
        ->assertSee('Toko 01')
        ->assertDontSee('Toko 11');
});

it('menampilkan formulir tambah toko penerima', function () {
    [, $user] = storeDistributorAccount();

    $this->actingAs($user)
        ->get(route('distributor.stores.create'))
        ->assertOk()
        ->assertSee('Tambah toko penerima')
        ->assertSee('Pasar')
        ->assertSee('Nama Toko')
        ->assertSee('Alamat')
        ->assertSee('NIB')
        ->assertDontSee('Status aktif')
        ->assertSee('action="'.route('distributor.stores.store').'"', false);
});

it('menyimpan toko baru untuk distributor yang login', function () {
    [$distributor, $user] = storeDistributorAccount();
    $market = Market::factory()->create();

    $this->actingAs($user)
        ->post(route('distributor.stores.store'), [
            'market_id' => $market->id,
            'name' => 'Toko Baru',
            'alamat' => 'Jln. Baru No. 10',
            'nib' => '1234567890123',
        ])
        ->assertRedirect(route('distributor.stores.index'));

    $this->assertDatabaseHas('recipient_stores', [
        'distributor_id' => $distributor->id,
        'market_id' => $market->id,
        'name' => 'Toko Baru',
        'alamat' => 'Jln. Baru No. 10',
        'nib' => '1234567890123',
        'is_active' => true,
    ]);
});

it('mewajibkan pasar, nama, alamat, dan NIB saat menyimpan toko', function () {
    [, $user] = storeDistributorAccount();

    $this->actingAs($user)
        ->post(route('distributor.stores.store'), [
            'market_id' => '',
            'name' => '',
            'alamat' => '',
            'nib' => '',
        ])
        ->assertSessionHasErrors(['market_id', 'name', 'alamat', 'nib']);
});

it('memvalidasi format nama, alamat, dan NIB toko distributor', function () {
    [, $user] = storeDistributorAccount();
    $market = Market::factory()->create();

    $this->actingAs($user)
        ->post(route('distributor.stores.store'), [
            'market_id' => $market->id,
            'name' => 'Toko !@#',
            'alamat' => 'Jln. Merdeka @ 1',
            'nib' => '12345ABC90123',
        ])
        ->assertSessionHasErrors(['name', 'alamat', 'nib']);

    $this->assertDatabaseCount('recipient_stores', 0);
});

it('memperbarui toko milik sendiri', function () {
    [$distributor, $user] = storeDistributorAccount();
    $market = Market::factory()->create();
    $store = RecipientStore::factory()->create([
        'distributor_id' => $distributor->id,
        'market_id' => $market->id,
        'name' => 'Nama Lama',
        'alamat' => 'Alamat Lama',
        'is_active' => false,
    ]);

    $this->actingAs($user)
        ->get(route('distributor.stores.edit', $store))
        ->assertOk()
        ->assertSee('Nama Lama')
        ->assertSee('Alamat Lama');

    $this->put(route('distributor.stores.update', $store), [
        'market_id' => $market->id,
        'name' => 'Nama Baru',
        'alamat' => 'Alamat Baru',
        'nib' => '9876543210123',
    ])->assertRedirect(route('distributor.stores.index'));

    $this->assertDatabaseHas('recipient_stores', [
        'id' => $store->id,
        'distributor_id' => $distributor->id,
        'name' => 'Nama Baru',
        'alamat' => 'Alamat Baru',
        'nib' => '9876543210123',
        'is_active' => false,
    ]);
});

it('menolak mengakses toko milik distributor lain', function () {
    [, $user] = storeDistributorAccount();
    [, $otherUser] = storeDistributorAccount('PT Distributor Lain');
    $store = RecipientStore::factory()->create([
        'distributor_id' => $otherUser->distributor_id,
        'name' => 'Toko Distributor Lain',
    ]);

    $this->actingAs($user)
        ->get(route('distributor.stores.edit', $store))
        ->assertNotFound();

    $this->put(route('distributor.stores.update', $store), [
        'market_id' => $store->market_id,
        'name' => 'Diubah',
        'alamat' => 'Diubah',
    ])->assertNotFound();

    $this->assertDatabaseHas('recipient_stores', [
        'id' => $store->id,
        'name' => 'Toko Distributor Lain',
    ]);
});

it('membatasi halaman manajemen toko hanya untuk distributor', function () {
    $this->get(route('distributor.stores.index'))->assertRedirect(route('login'));

    $this->actingAs(User::factory()->create())
        ->get(route('distributor.stores.index'))
        ->assertForbidden();
});
