<?php

use App\Enums\Role;
use App\Models\Distributor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('menampilkan form tambah distributor untuk admin', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('admin.distributors.create'))
        ->assertOk()
        ->assertSee('Tambah Distributor')
        ->assertSeeInOrder(['Kode Distributor', 'Nama Distributor', 'Status'])
        ->assertSee('Simpan');
});

it('menyimpan distributor baru dengan kode huruf besar', function () {
    $this->actingAs(User::factory()->create())
        ->post(route('admin.distributors.store'), [
            'code' => 'dist-100',
            'name' => 'PT Sumber Makmur',
            'is_active' => '1',
        ])
        ->assertRedirect(route('admin.distributors.index'))
        ->assertSessionHas('status', 'Distributor PT Sumber Makmur berhasil ditambahkan.');

    $this->assertDatabaseHas('distributors', [
        'code' => 'DIST-100',
        'name' => 'PT Sumber Makmur',
        'is_active' => 1,
    ]);
});

it('menolak kode distributor yang duplikat atau tidak valid', function () {
    Distributor::factory()->create(['code' => 'DIST-001']);

    $admin = User::factory()->create();

    $this->actingAs($admin)
        ->post(route('admin.distributors.store'), [
            'code' => 'dist-001',
            'name' => 'PT Duplikat',
            'is_active' => '1',
        ])
        ->assertSessionHasErrors(['code' => 'Kode distributor sudah digunakan.']);

    $this->actingAs($admin)
        ->post(route('admin.distributors.store'), [
            'code' => 'DIST 001',
            'name' => 'PT Spasi',
            'is_active' => '1',
        ])
        ->assertSessionHasErrors(['code']);
});

it('mewajibkan kode, nama, dan status distributor', function () {
    $this->actingAs(User::factory()->create())
        ->post(route('admin.distributors.store'), [])
        ->assertSessionHasErrors([
            'code' => 'Kode distributor wajib diisi.',
            'name' => 'Nama distributor wajib diisi.',
            'is_active' => 'Status wajib dipilih.',
        ]);
});

it('menampilkan form ubah distributor dengan kode tidak dapat diubah', function () {
    $distributor = Distributor::factory()->create([
        'code' => 'DIST-777',
        'name' => 'PT Awal',
        'is_active' => false,
    ]);

    $this->actingAs(User::factory()->create())
        ->get(route('admin.distributors.edit', $distributor))
        ->assertOk()
        ->assertSee('Ubah Distributor')
        ->assertSee('value="DIST-777"', false)
        ->assertSee('Kode tidak dapat diubah setelah distributor dibuat.')
        ->assertSee('value="PT Awal"', false)
        ->assertSee('Simpan Perubahan');
});

it('memperbarui nama dan status distributor tanpa mengubah kode', function () {
    $distributor = Distributor::factory()->create([
        'code' => 'DIST-500',
        'name' => 'PT Lama',
        'is_active' => true,
    ]);

    $this->actingAs(User::factory()->create())
        ->put(route('admin.distributors.update', $distributor), [
            'code' => 'DIST-999',
            'name' => 'PT Baru',
            'is_active' => '0',
        ])
        ->assertRedirect(route('admin.distributors.index'))
        ->assertSessionHas('status', 'Distributor PT Baru berhasil diperbarui.');

    $distributor->refresh();

    expect($distributor->code)->toBe('DIST-500')
        ->and($distributor->name)->toBe('PT Baru')
        ->and($distributor->is_active)->toBeFalse();
});

it('mewajibkan nama dan status saat mengubah distributor', function () {
    $distributor = Distributor::factory()->create();

    $this->actingAs(User::factory()->create())
        ->put(route('admin.distributors.update', $distributor), [])
        ->assertSessionHasErrors(['name', 'is_active']);
});

it('menampilkan tombol tambah data dan tautan edit pada daftar distributor', function () {
    $distributor = Distributor::factory()->create();

    $this->actingAs(User::factory()->create())
        ->get(route('admin.distributors.index'))
        ->assertOk()
        ->assertSee('href="'.route('admin.distributors.create').'"', false)
        ->assertSee('Tambah data')
        ->assertSee('href="'.route('admin.distributors.edit', $distributor).'"', false)
        ->assertSee('Edit');
});

it('membatasi pengelolaan distributor hanya untuk admin', function () {
    $distributor = Distributor::factory()->create();

    $this->get(route('admin.distributors.create'))->assertRedirect(route('login'));
    $this->post(route('admin.distributors.store'), [])->assertRedirect(route('login'));

    $distributorUser = User::factory()->create(['role' => Role::Distributor]);

    $this->actingAs($distributorUser)
        ->get(route('admin.distributors.create'))
        ->assertForbidden();

    $this->actingAs($distributorUser)
        ->get(route('admin.distributors.edit', $distributor))
        ->assertForbidden();

    $this->actingAs($distributorUser)
        ->put(route('admin.distributors.update', $distributor), [])
        ->assertForbidden();
});
