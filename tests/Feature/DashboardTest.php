<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('menampilkan dashboard admin dengan sidebar MIPTA', function () {
    $response = $this->actingAs(User::factory()->create())
        ->get(route('admin.dashboard'));

    $response->assertOk()
        ->assertSee('<title>Dashboard</title>', false)
        ->assertSee('MIPTA')
        ->assertSeeInOrder([
            'Dashboard',
            'Pengguna',
            'Data Master',
            'Penyaluran',
            'Kondisi Pasar',
            'Rekap Laporan',
            'Logout',
        ]);
});

it('menandai menu dashboard sebagai menu aktif', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('bg-[#FDBA12]/40 font-semibold text-white', false);
});

it('mengarahkan halaman utama ke login untuk tamu', function () {
    $this->get('/')->assertRedirect(route('login'));
});

it('mengarahkan halaman utama ke dashboard admin untuk pengguna yang sudah login', function () {
    $this->actingAs(User::factory()->create())
        ->get('/')
        ->assertRedirect(route('admin.dashboard'));
});
