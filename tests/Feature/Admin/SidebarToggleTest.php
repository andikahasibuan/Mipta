<?php

use App\Enums\Role;
use App\Models\Market;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('menampilkan tombol untuk menyembunyikan sidebar', function () {
    $admin = User::factory()->create();

    $response = $this->actingAs($admin)->get(route('admin.dashboard'));

    $response->assertOk()
        ->assertSee('data-sidebar-toggle', false)
        ->assertSee('aria-controls="app-sidebar"', false)
        ->assertSee('aria-expanded="true"', false)
        ->assertSee('aria-label="Sembunyikan menu"', false)
        ->assertSee('id="app-sidebar"', false);
});

it('menandai label menu agar dapat disembunyikan saat sidebar ciut', function () {
    $admin = User::factory()->create();

    $response = $this->actingAs($admin)->get(route('admin.dashboard'));

    $response->assertOk();

    $html = $response->getContent();

    preg_match('/<aside[^>]*id="app-sidebar".*?<\/aside>/s', $html, $matches);
    $sidebar = $matches[0] ?? '';

    foreach (['Dashboard', 'Data Pengguna', 'Penyaluran', 'Kondisi Pasar', 'Monitoring', 'Rekap Laporan', 'Logout'] as $label) {
        expect($sidebar)->toMatch('/data-sidebar-label[^>]*>\s*'.preg_quote($label, '/').'\s*</');
    }
});

it('menyembunyikan pusat bantuan dari sidebar admin', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertDontSee('Pusat Bantuan')
        ->assertDontSee('Hubungi Admin');
});

it('menampilkan pusat bantuan untuk distributor, kepala dinas, dan uptd', function () {
    $distributor = User::factory()->distributor()->create();

    $this->actingAs($distributor)
        ->get(route('distributor.dashboard'))
        ->assertOk()
        ->assertSee('Pusat Bantuan')
        ->assertSee('Hubungi Admin');

    $kepalaDinas = User::factory()->create(['role' => Role::KepalaDinas]);

    $this->actingAs($kepalaDinas)
        ->get(route('kepala-dinas.dashboard'))
        ->assertOk()
        ->assertSee('Pusat Bantuan');

    $uptd = User::factory()->create([
        'role' => Role::UptdPasar,
        'market_id' => Market::factory(),
    ]);

    $this->actingAs($uptd)
        ->get(route('uptd.dashboard'))
        ->assertOk()
        ->assertSee('Pusat Bantuan');
});
