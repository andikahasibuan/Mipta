<?php

use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('mengakhiri sesi admin yang sudah dinonaktifkan', function () {
    $admin = User::factory()->inactive()->create(['role' => Role::Admin]);

    $this->actingAs($admin)
        ->get(route('admin.dashboard'))
        ->assertRedirect(route('login'))
        ->assertSessionHasErrors('identifier');

    $this->assertGuest();
});

it('mengakhiri sesi distributor yang sudah dinonaktifkan', function () {
    $distributor = User::factory()->distributor()->inactive()->create();

    $this->actingAs($distributor)
        ->get(route('distributor.dashboard'))
        ->assertRedirect(route('login'))
        ->assertSessionHasErrors('identifier');

    $this->assertGuest();
});

it('mengakhiri sesi akun bersama UPTD yang sudah dinonaktifkan', function () {
    $uptd = User::factory()->inactive()->create(['role' => Role::UptdPasar]);

    $this->actingAs($uptd)
        ->get(route('uptd.contributors.index'))
        ->assertRedirect(route('login'))
        ->assertSessionHasErrors('identifier');

    $this->assertGuest();
});

it('mengakhiri sesi pengguna nonaktif saat membuka halaman utama', function () {
    $admin = User::factory()->inactive()->create(['role' => Role::Admin]);

    $this->actingAs($admin)
        ->get('/')
        ->assertRedirect(route('login'))
        ->assertSessionHasErrors('identifier');

    $this->assertGuest();
});
