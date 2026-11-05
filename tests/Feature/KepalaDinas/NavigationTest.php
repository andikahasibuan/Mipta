<?php

use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

dataset('kepala dinas pages', [
    'dashboard' => ['kepala-dinas.dashboard', 'Dashboard Monitoring', 'Dashboard Kepala Dinas'],
    'distribution' => ['kepala-dinas.distribution', 'Monitoring Laporan Penyaluran', 'Monitoring Laporan Penyaluran'],
    'markets' => ['kepala-dinas.markets', 'Monitoring Kondisi Pasar', 'Monitoring Kondisi Pasar'],
    'reports' => ['kepala-dinas.reports', 'Rekapitulasi Laporan', 'Rekapitulasi Laporan'],
]);

it('menampilkan halaman kepala dinas dengan menu yang sesuai aktif', function (string $routeName, string $label, string $title) {
    $response = $this->actingAs(User::factory()->create(['role' => Role::KepalaDinas]))->get(route($routeName));

    $response->assertOk()
        ->assertSee('<title>'.e($title).'</title>', false)
        ->assertSeeInOrder(['Sistem Monitoring Distribusi', $label]);

    $html = $response->getContent();

    preg_match('/<nav[^>]*aria-label="Menu utama".*?<\/nav>/s', $html, $matches);
    $sidebar = $matches[0] ?? '';

    expect(substr_count($sidebar, 'aria-current="page"'))->toBe(1)
        ->and($sidebar)->toMatch('/aria-current="page"[^>]*>(?:(?!<\/a>).)*'.preg_quote(e($label), '/').'/s');
})->with('kepala dinas pages');

it('mengarahkan tamu ke halaman login', function (string $routeName) {
    $this->get(route($routeName))->assertRedirect(route('login'));
})->with('kepala dinas pages');

it('menolak pengguna dengan peran selain kepala dinas', function (string $routeName) {
    $this->actingAs(User::factory()->create(['role' => Role::Admin]))
        ->get(route($routeName))
        ->assertForbidden();
})->with('kepala dinas pages');

it('hanya menampilkan menu kepala dinas, bukan menu admin', function () {
    $response = $this->actingAs(User::factory()->create(['role' => Role::KepalaDinas]))
        ->get(route('kepala-dinas.dashboard'));

    $response->assertOk()
        ->assertSee('Dashboard Monitoring')
        ->assertSee('Monitoring Laporan Penyaluran')
        ->assertSee('Monitoring Kondisi Pasar')
        ->assertSee('Rekapitulasi Laporan')
        ->assertDontSee('Pengguna & Data Master')
        ->assertDontSee('Pengawasan & Tindak Lanjut')
        ->assertDontSee('Riwayat Aktivitas Sistem')
        ->assertDontSee('Manajemen Profil');
});

it('menampilkan ikon pada setiap menu sidebar kepala dinas', function () {
    $response = $this->actingAs(User::factory()->create(['role' => Role::KepalaDinas]))
        ->get(route('kepala-dinas.dashboard'));

    $response->assertOk();

    foreach (['squares-2x2', 'document-text', 'building-storefront', 'arrow-down-tray'] as $icon) {
        $response->assertSee('data-icon="'.$icon.'"', false);
    }
});
