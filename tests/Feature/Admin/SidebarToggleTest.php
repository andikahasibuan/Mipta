<?php

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

it('menandai label menu dan teks bantuan agar dapat disembunyikan saat sidebar ciut', function () {
    $admin = User::factory()->create();

    $response = $this->actingAs($admin)->get(route('admin.dashboard'));

    $response->assertOk();

    $html = $response->getContent();

    preg_match('/<aside[^>]*id="app-sidebar".*?<\/aside>/s', $html, $matches);
    $sidebar = $matches[0] ?? '';

    expect($sidebar)->toContain('data-sidebar-help-text');

    foreach (['Dashboard', 'Data Pengguna', 'Penyaluran', 'Kondisi Pasar', 'Monitoring', 'Rekap Laporan', 'Logout'] as $label) {
        expect($sidebar)->toMatch('/data-sidebar-label[^>]*>\s*'.preg_quote($label, '/').'\s*</');
    }
});
