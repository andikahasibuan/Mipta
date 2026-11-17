<?php

use App\Enums\Role;
use App\Models\UptdContributor;
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
        ->assertSee('data-sidebar-icon-collapse', false)
        ->assertSee('data-sidebar-icon-expand', false)
        ->assertSee('id="app-sidebar"', false)
        ->assertSee('id="app-content"', false);
});

it('menandai label menu agar dapat disembunyikan saat sidebar ciut', function () {
    $admin = User::factory()->create();

    $response = $this->actingAs($admin)->get(route('admin.dashboard'));

    $response->assertOk();

    $html = $response->getContent();

    preg_match('/<aside[^>]*id="app-sidebar".*?<\/aside>/s', $html, $matches);
    $sidebar = $matches[0] ?? '';

    foreach (['Dashboard', 'Pengguna', 'Data Master', 'Penyaluran', 'Pengawasan', 'Pusat Bantuan', 'Logout'] as $label) {
        expect($sidebar)->toMatch('/data-sidebar-label[^>]*>\s*'.preg_quote($label, '/').'\s*</');
    }
});

it('menampilkan pusat bantuan di sidebar admin', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('Pusat Bantuan')
        ->assertSee('href="'.route('help').'"', false);
});

it('menampilkan pusat bantuan untuk distributor, kepala dinas, dan uptd', function () {
    $distributor = User::factory()->distributor()->create();

    $this->actingAs($distributor)
        ->get(route('distributor.dashboard'))
        ->assertOk()
        ->assertSee('Pusat Bantuan')
        ->assertSee('href="'.route('help').'"', false);

    $kepalaDinas = User::factory()->create(['role' => Role::KepalaDinas]);

    $this->actingAs($kepalaDinas)
        ->get(route('kepala-dinas.dashboard'))
        ->assertOk()
        ->assertSee('Pusat Bantuan');

    $shared = User::factory()->create(['role' => Role::UptdPasar]);
    $contributor = UptdContributor::factory()->create();

    $this->actingAs($shared)
        ->withSession(uptdContributorSession($contributor))
        ->get(route('uptd.dashboard'))
        ->assertOk()
        ->assertSee('Pusat Bantuan');
});
