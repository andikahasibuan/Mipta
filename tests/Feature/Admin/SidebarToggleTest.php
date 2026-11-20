<?php

use App\Enums\Role;
use App\Models\UptdContributor;
use App\Models\User;
use App\Services\HelpCenterService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    config([
        'help.admin.whatsapp' => '081234567890',
        'help.admin.service_hours' => 'Senin - Jumat, 08.00 - 16.00 WIB',
        'help.admin.whatsapp_message' => 'Halo Admin MIPTA, saya butuh bantuan.',
    ]);
});

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

    foreach (['Dashboard', 'Pengguna', 'Data Master', 'Penyaluran', 'Pengawasan', 'Logout'] as $label) {
        expect($sidebar)->toMatch('/data-sidebar-label[^>]*>\s*'.preg_quote($label, '/').'\s*</');
    }
});

it('menyembunyikan pusat bantuan dari sidebar admin', function () {
    $helpUrl = app(HelpCenterService::class)->whatsappUrl();

    $this->actingAs(User::factory()->create())
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertDontSee('Pusat Bantuan')
        ->assertDontSee('href="'.$helpUrl.'"', false);
});

it('mengarahkan pusat bantuan ke whatsapp untuk distributor, kepala dinas, dan uptd', function () {
    $helpUrl = app(HelpCenterService::class)->whatsappUrl();

    $distributor = User::factory()->distributor()->create();

    $this->actingAs($distributor)
        ->get(route('distributor.dashboard'))
        ->assertOk()
        ->assertSee('Pusat Bantuan')
        ->assertSee('href="'.$helpUrl.'"', false);

    $kepalaDinas = User::factory()->create(['role' => Role::KepalaDinas]);

    $this->actingAs($kepalaDinas)
        ->get(route('kepala-dinas.dashboard'))
        ->assertOk()
        ->assertSee('Pusat Bantuan')
        ->assertSee('href="'.$helpUrl.'"', false);

    $shared = User::factory()->create(['role' => Role::UptdPasar]);
    $contributor = UptdContributor::factory()->create();

    $this->actingAs($shared)
        ->withSession(uptdContributorSession($contributor))
        ->get(route('uptd.dashboard'))
        ->assertOk()
        ->assertSee('Pusat Bantuan')
        ->assertSee('href="'.$helpUrl.'"', false);
});
