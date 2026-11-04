<?php

use App\Enums\Role;
use App\Models\Market;
use App\Models\RecipientStore;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('menampilkan form tambah toko penerima dengan pilihan pasar', function () {
    Market::factory()->create(['name' => 'Pasar Raya Padang']);

    $this->actingAs(User::factory()->create())
        ->get(route('admin.recipient-stores.create'))
        ->assertOk()
        ->assertSee('Tambah Toko Penerima')
        ->assertSeeInOrder(['Kode Toko', 'Nama Toko', 'Pasar', 'Status'])
        ->assertSee('Pasar Raya Padang');
});

it('menampilkan peringatan bila belum ada pasar', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('admin.recipient-stores.create'))
        ->assertOk()
        ->assertSee('Belum ada data pasar. Tambahkan pasar terlebih dahulu sebelum membuat toko penerima.');
});

it('menyimpan toko penerima baru dengan kode huruf besar', function () {
    $market = Market::factory()->create();

    $this->actingAs(User::factory()->create())
        ->post(route('admin.recipient-stores.store'), [
            'code' => 'tks-100',
            'name' => 'Toko Berkah',
            'market_id' => $market->id,
            'is_active' => '1',
        ])
        ->assertRedirect(route('admin.recipient-stores.index'))
        ->assertSessionHas('status', 'Toko Toko Berkah berhasil ditambahkan.');

    $this->assertDatabaseHas('recipient_stores', [
        'code' => 'TKS-100',
        'name' => 'Toko Berkah',
        'market_id' => $market->id,
        'is_active' => 1,
    ]);
});

it('mewajibkan kode, nama, pasar, dan status toko', function () {
    $this->actingAs(User::factory()->create())
        ->post(route('admin.recipient-stores.store'), [])
        ->assertSessionHasErrors([
            'code' => 'Kode toko wajib diisi.',
            'name' => 'Nama toko wajib diisi.',
            'market_id' => 'Pasar wajib dipilih.',
            'is_active' => 'Status wajib dipilih.',
        ]);
});

it('menolak kode duplikat dan pasar yang tidak ada', function () {
    $market = Market::factory()->create();
    RecipientStore::factory()->create(['code' => 'TKS-001', 'market_id' => $market->id]);

    $admin = User::factory()->create();

    $this->actingAs($admin)
        ->post(route('admin.recipient-stores.store'), [
            'code' => 'tks-001',
            'name' => 'Toko Duplikat',
            'market_id' => $market->id,
            'is_active' => '1',
        ])
        ->assertSessionHasErrors(['code' => 'Kode toko sudah digunakan.']);

    $this->actingAs($admin)
        ->post(route('admin.recipient-stores.store'), [
            'code' => 'TKS-999',
            'name' => 'Toko Pasar Hilang',
            'market_id' => 999999,
            'is_active' => '1',
        ])
        ->assertSessionHasErrors(['market_id' => 'Pasar yang dipilih tidak valid.']);
});

it('menampilkan form ubah toko dengan kode tidak dapat diubah', function () {
    $store = RecipientStore::factory()->create([
        'code' => 'TKS-777',
        'name' => 'Toko Awal',
    ]);

    $this->actingAs(User::factory()->create())
        ->get(route('admin.recipient-stores.edit', $store))
        ->assertOk()
        ->assertSee('Ubah Toko Penerima')
        ->assertSee('value="TKS-777"', false)
        ->assertSee('Kode tidak dapat diubah setelah toko dibuat.')
        ->assertSee('value="Toko Awal"', false);
});

it('memperbarui nama, pasar, dan status toko tanpa mengubah kode', function () {
    $oldMarket = Market::factory()->create();
    $newMarket = Market::factory()->create();
    $store = RecipientStore::factory()->create([
        'code' => 'TKS-500',
        'market_id' => $oldMarket->id,
        'is_active' => true,
    ]);

    $this->actingAs(User::factory()->create())
        ->put(route('admin.recipient-stores.update', $store), [
            'code' => 'TKS-999',
            'name' => 'Toko Pindah',
            'market_id' => $newMarket->id,
            'is_active' => '0',
        ])
        ->assertRedirect(route('admin.recipient-stores.index'));

    $store->refresh();

    expect($store->code)->toBe('TKS-500')
        ->and($store->name)->toBe('Toko Pindah')
        ->and($store->market_id)->toBe($newMarket->id)
        ->and($store->is_active)->toBeFalse();
});

it('menampilkan tombol tambah data dan tautan edit pada daftar toko', function () {
    $store = RecipientStore::factory()->create();

    $this->actingAs(User::factory()->create())
        ->get(route('admin.recipient-stores.index'))
        ->assertOk()
        ->assertSee('href="'.route('admin.recipient-stores.create').'"', false)
        ->assertSee('Tambah data')
        ->assertSee('href="'.route('admin.recipient-stores.edit', $store).'"', false);
});

it('membatasi pengelolaan toko hanya untuk admin', function () {
    $store = RecipientStore::factory()->create();

    $this->get(route('admin.recipient-stores.create'))->assertRedirect(route('login'));
    $this->post(route('admin.recipient-stores.store'), [])->assertRedirect(route('login'));

    $distributor = User::factory()->create(['role' => Role::Distributor]);

    $this->actingAs($distributor)
        ->get(route('admin.recipient-stores.create'))
        ->assertForbidden();

    $this->actingAs($distributor)
        ->put(route('admin.recipient-stores.update', $store), [])
        ->assertForbidden();
});
