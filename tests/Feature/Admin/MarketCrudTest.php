<?php

use App\Enums\Role;
use App\Models\Market;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('menampilkan form tambah pasar untuk admin', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('admin.markets.create'))
        ->assertOk()
        ->assertSee('Tambah Pasar')
        ->assertSeeInOrder(['Kode Pasar', 'Nama Pasar', 'Status'])
        ->assertSee('Simpan');
});

it('menyimpan pasar baru dengan kode huruf besar', function () {
    $this->actingAs(User::factory()->create())
        ->post(route('admin.markets.store'), [
            'code' => 'psr-100',
            'name' => 'Pasar Raya Padang',
            'is_active' => '1',
        ])
        ->assertRedirect(route('admin.markets.index'))
        ->assertSessionHas('status', 'Pasar Pasar Raya Padang berhasil ditambahkan.');

    $this->assertDatabaseHas('markets', [
        'code' => 'PSR-100',
        'name' => 'Pasar Raya Padang',
        'is_active' => 1,
    ]);
});

it('menolak kode pasar yang duplikat atau tidak valid', function () {
    Market::factory()->create(['code' => 'PSR-001']);

    $admin = User::factory()->create();

    $this->actingAs($admin)
        ->post(route('admin.markets.store'), [
            'code' => 'psr-001',
            'name' => 'Pasar Duplikat',
            'is_active' => '1',
        ])
        ->assertSessionHasErrors(['code' => 'Kode pasar sudah digunakan.']);

    $this->actingAs($admin)
        ->post(route('admin.markets.store'), [
            'code' => 'PSR 001',
            'name' => 'Pasar Spasi',
            'is_active' => '1',
        ])
        ->assertSessionHasErrors(['code']);
});

it('mewajibkan kode, nama, dan status pasar', function () {
    $this->actingAs(User::factory()->create())
        ->post(route('admin.markets.store'), [])
        ->assertSessionHasErrors([
            'code' => 'Kode pasar wajib diisi.',
            'name' => 'Nama pasar wajib diisi.',
            'is_active' => 'Status wajib dipilih.',
        ]);
});

it('menampilkan form ubah pasar dengan kode tidak dapat diubah', function () {
    $market = Market::factory()->create([
        'code' => 'PSR-777',
        'name' => 'Pasar Awal',
        'is_active' => false,
    ]);

    $this->actingAs(User::factory()->create())
        ->get(route('admin.markets.edit', $market))
        ->assertOk()
        ->assertSee('Ubah Pasar')
        ->assertSee('value="PSR-777"', false)
        ->assertSee('Kode tidak dapat diubah setelah pasar dibuat.')
        ->assertSee('value="Pasar Awal"', false);
});

it('memperbarui nama dan status pasar tanpa mengubah kode', function () {
    $market = Market::factory()->create([
        'code' => 'PSR-500',
        'name' => 'Pasar Lama',
        'is_active' => true,
    ]);

    $this->actingAs(User::factory()->create())
        ->put(route('admin.markets.update', $market), [
            'code' => 'PSR-999',
            'name' => 'Pasar Baru',
            'is_active' => '0',
        ])
        ->assertRedirect(route('admin.markets.index'));

    $market->refresh();

    expect($market->code)->toBe('PSR-500')
        ->and($market->name)->toBe('Pasar Baru')
        ->and($market->is_active)->toBeFalse();
});

it('menampilkan tombol tambah data dan tautan edit pada daftar pasar', function () {
    $market = Market::factory()->create();

    $this->actingAs(User::factory()->create())
        ->get(route('admin.markets.index'))
        ->assertOk()
        ->assertSee('href="'.route('admin.markets.create').'"', false)
        ->assertSee('Tambah data')
        ->assertSee('href="'.route('admin.markets.edit', $market).'"', false);
});

it('membatasi pengelolaan pasar hanya untuk admin', function () {
    $market = Market::factory()->create();

    $this->get(route('admin.markets.create'))->assertRedirect(route('login'));
    $this->post(route('admin.markets.store'), [])->assertRedirect(route('login'));

    $distributor = User::factory()->create(['role' => Role::Distributor]);

    $this->actingAs($distributor)
        ->get(route('admin.markets.create'))
        ->assertForbidden();

    $this->actingAs($distributor)
        ->put(route('admin.markets.update', $market), [])
        ->assertForbidden();
});
