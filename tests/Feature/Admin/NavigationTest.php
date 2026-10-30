<?php

use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

dataset('admin pages', [
    'dashboard' => ['admin.dashboard', 'Dashboard Monitoring', 'Dashboard Admin'],
    'users' => ['admin.users.index', 'Pengguna & Data Master', 'Pengguna & Data Master'],
    'distribution' => ['admin.distribution.index', 'Laporan Penyaluran', 'Laporan Penyaluran'],
    'monitoring' => ['admin.monitoring.index', 'Monitoring & Analisis', 'Monitoring & Analisis'],
    'supervision' => ['admin.supervision.index', 'Pengawasan & Tindak Lanjut', 'Pengawasan & Tindak Lanjut'],
    'reports' => ['admin.reports.index', 'Rekap & Ekspor Laporan', 'Rekap & Ekspor Laporan'],
    'activity' => ['admin.activity.index', 'Riwayat Aktivitas Sistem', 'Riwayat Aktivitas Sistem'],
    'profile' => ['admin.profile.edit', 'Manajemen Profil', 'Manajemen Profil'],
]);

it('menampilkan halaman admin dengan menu yang sesuai aktif', function (string $routeName, string $label, string $title) {
    $response = $this->actingAs(User::factory()->create())->get(route($routeName));

    $response->assertOk()
        ->assertSee('<title>'.e($title).'</title>', false)
        ->assertSeeInOrder(['Sistem Monitoring Distribusi', $label]);

    $html = $response->getContent();

    preg_match('/<nav[^>]*aria-label="Menu utama".*?<\/nav>/s', $html, $matches);
    $sidebar = $matches[0] ?? '';

    expect(substr_count($sidebar, 'aria-current="page"'))->toBe(1)
        ->and($sidebar)->toMatch('/aria-current="page"[^>]*>(?:(?!<\/a>).)*'.preg_quote(e($label), '/').'/s');
})->with('admin pages');

it('mengarahkan tamu ke halaman login', function (string $routeName) {
    $this->get(route($routeName))->assertRedirect(route('login'));
})->with('admin pages');

it('menolak pengguna dengan peran selain admin', function (string $routeName) {
    $this->actingAs(User::factory()->create(['role' => Role::Distributor]))
        ->get(route($routeName))
        ->assertForbidden();
})->with('admin pages');

it('menampilkan ikon pada setiap menu sidebar', function () {
    $response = $this->actingAs(User::factory()->create())->get(route('admin.dashboard'));

    $response->assertOk();

    foreach (['squares-2x2', 'users', 'document-text', 'chart-bar', 'shield-check', 'arrow-down-tray', 'clock', 'user-circle'] as $icon) {
        $response->assertSee('data-icon="'.$icon.'"', false);
    }
});
