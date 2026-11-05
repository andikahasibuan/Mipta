<?php

use App\Enums\Role;
use App\Models\Distribution;
use App\Models\Distributor;
use App\Models\Market;
use App\Models\RecipientStore;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('menyimpan distributor baru ke database', function () {
    $this->actingAs(User::factory()->create());

    $this->post(route('admin.distributors.store'), [
        'name' => 'PT Contoh Baru',
        'wilayah' => 'Padang',
        'is_active' => '1',
    ])->assertRedirect(route('admin.distributors.index'));

    $this->assertDatabaseHas('distributors', [
        'name' => 'PT Contoh Baru',
        'wilayah' => 'Padang',
        'is_active' => true,
    ]);
});

it('menampilkan halaman edit distributor', function () {
    $this->actingAs(User::factory()->create());
    $distributor = Distributor::factory()->create(['name' => 'PT Sinar Pangan']);

    $this->get(route('admin.distributors.edit', $distributor))
        ->assertOk()
        ->assertSee('PT Sinar Pangan');
});

it('memperbarui distributor dan menyimpannya ke database', function () {
    $this->actingAs(User::factory()->create());
    $distributor = Distributor::factory()->create(['name' => 'Nama Lama']);

    $this->put(route('admin.distributors.update', $distributor), [
        'name' => 'Nama Baru',
        'wilayah' => 'Padang',
    ])->assertRedirect(route('admin.distributors.index'));

    $this->assertDatabaseHas('distributors', [
        'id' => $distributor->id,
        'name' => 'Nama Baru',
    ]);
});

it('menghapus distributor dari database', function () {
    $this->actingAs(User::factory()->create());
    $distributor = Distributor::factory()->create();

    $this->delete(route('admin.distributors.destroy', $distributor))
        ->assertRedirect(route('admin.distributors.index'));

    $this->assertDatabaseMissing('distributors', ['id' => $distributor->id]);
});

it('menolak menghapus distributor yang masih memiliki penyaluran', function () {
    $this->actingAs(User::factory()->create());
    $distributor = Distributor::factory()->create();
    Distribution::factory()->create(['distributor_id' => $distributor->id]);

    $this->from(route('admin.distributors.index'))
        ->delete(route('admin.distributors.destroy', $distributor))
        ->assertRedirect(route('admin.distributors.index'))
        ->assertSessionHasErrors('delete');

    $this->assertDatabaseHas('distributors', ['id' => $distributor->id]);
});

it('menyimpan pasar baru ke database', function () {
    $this->actingAs(User::factory()->create());

    $this->post(route('admin.markets.store'), [
        'name' => 'Pasar Baru',
        'lokasi' => 'Jln. Merdeka',
        'is_active' => '1',
    ])->assertRedirect(route('admin.markets.index'));

    $this->assertDatabaseHas('markets', [
        'name' => 'Pasar Baru',
        'lokasi' => 'Jln. Merdeka',
        'is_active' => true,
    ]);
});

it('memperbarui pasar dan menyimpannya ke database', function () {
    $this->actingAs(User::factory()->create());
    $market = Market::factory()->create(['name' => 'Nama Lama']);

    $this->put(route('admin.markets.update', $market), [
        'name' => 'Nama Baru',
        'lokasi' => 'Jln. Merdeka',
    ])->assertRedirect(route('admin.markets.index'));

    $this->assertDatabaseHas('markets', [
        'id' => $market->id,
        'name' => 'Nama Baru',
    ]);
});

it('menghapus pasar dari database', function () {
    $this->actingAs(User::factory()->create());
    $market = Market::factory()->create();

    $this->delete(route('admin.markets.destroy', $market))
        ->assertRedirect(route('admin.markets.index'));

    $this->assertDatabaseMissing('markets', ['id' => $market->id]);
});

it('menolak menghapus pasar yang masih memiliki toko penerima', function () {
    $this->actingAs(User::factory()->create());
    $market = Market::factory()->create();
    RecipientStore::factory()->create(['market_id' => $market->id]);

    $this->from(route('admin.markets.index'))
        ->delete(route('admin.markets.destroy', $market))
        ->assertRedirect(route('admin.markets.index'))
        ->assertSessionHasErrors('delete');

    $this->assertDatabaseHas('markets', ['id' => $market->id]);
});

it('menyimpan toko penerima baru ke database', function () {
    $this->actingAs(User::factory()->create());
    $market = Market::factory()->create();

    $this->post(route('admin.recipient-stores.store'), [
        'market_id' => $market->id,
        'code' => 'TKS-999',
        'name' => 'Toko Baru',
        'is_active' => '1',
    ])->assertRedirect(route('admin.recipient-stores.index'));

    $this->assertDatabaseHas('recipient_stores', [
        'market_id' => $market->id,
        'code' => 'TKS-999',
        'name' => 'Toko Baru',
        'is_active' => true,
    ]);
});

it('memperbarui toko penerima dan menyimpannya ke database', function () {
    $this->actingAs(User::factory()->create());
    $store = RecipientStore::factory()->create(['name' => 'Nama Lama']);

    $this->put(route('admin.recipient-stores.update', $store), [
        'market_id' => $store->market_id,
        'code' => $store->code,
        'name' => 'Nama Baru',
    ])->assertRedirect(route('admin.recipient-stores.index'));

    $this->assertDatabaseHas('recipient_stores', [
        'id' => $store->id,
        'name' => 'Nama Baru',
    ]);
});

it('menghapus toko penerima dari database', function () {
    $this->actingAs(User::factory()->create());
    $store = RecipientStore::factory()->create();

    $this->delete(route('admin.recipient-stores.destroy', $store))
        ->assertRedirect(route('admin.recipient-stores.index'));

    $this->assertDatabaseMissing('recipient_stores', ['id' => $store->id]);
});

it('menolak pengguna non-admin mengakses formulir tambah distributor', function () {
    $this->actingAs(User::factory()->create(['role' => Role::Distributor]))
        ->get(route('admin.distributors.create'))
        ->assertForbidden();
});
