<?php

use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

dataset('distributor pages', [
    'dashboard' => ['distributor.dashboard', 'Dashboard Monitoring'],
    'stores' => ['distributor.stores.index', 'Manajemen Data Toko'],
    'distribution report' => ['distributor.distribution-reports.create', 'Laporan Penyaluran'],
    'report history' => ['distributor.report-history.index', 'Riwayat Laporan'],
    'profile' => ['distributor.profile.edit', 'Manajemen Profil'],
]);

it('menampilkan halaman distributor dengan menu yang sesuai aktif', function (string $routeName, string $label) {
    $response = $this->actingAs(User::factory()->distributor()->create())
        ->get(route($routeName));

    $response->assertOk()
        ->assertSee('<title>'.e($label).'</title>', false);

    $html = $response->getContent();

    preg_match('/<nav[^>]*aria-label="Menu utama".*?<\/nav>/s', $html, $matches);
    $sidebar = $matches[0] ?? '';

    expect(substr_count($sidebar, 'aria-current="page"'))->toBe(1)
        ->and($sidebar)->toMatch('/aria-current="page"[^>]*>(?:(?!<\/a>).)*'.preg_quote(e($label), '/').'/s');
})->with('distributor pages');

it('menampilkan seluruh menu sidebar distributor', function () {
    $this->actingAs(User::factory()->distributor()->create())
        ->get(route('distributor.dashboard'))
        ->assertOk()
        ->assertSeeInOrder([
            'Dashboard Monitoring',
            'Manajemen Data Toko',
            'Laporan Penyaluran',
            'Riwayat Laporan',
            'Manajemen Profil',
            'Keluar dari Sistem',
        ]);
});

it('mengarahkan tamu dari halaman distributor ke login', function (string $routeName) {
    $this->get(route($routeName))->assertRedirect(route('login'));
})->with('distributor pages');

it('menolak peran selain distributor dari halaman distributor', function (string $routeName) {
    $this->actingAs(User::factory()->create())
        ->get(route($routeName))
        ->assertForbidden();
})->with('distributor pages');

it('menolak distributor tanpa relasi PT dari halaman distributor', function (string $routeName) {
    $this->actingAs(User::factory()->create(['role' => Role::Distributor]))
        ->get(route($routeName))
        ->assertForbidden();
})->with('distributor pages');

it('mengakhiri sesi distributor tanpa PT saat membuka halaman utama', function () {
    $this->actingAs(User::factory()->create(['role' => Role::Distributor]))
        ->get('/')
        ->assertRedirect(route('login'))
        ->assertSessionHasErrors('identifier');

    $this->assertGuest();
});

dataset('non distributor areas', [
    'admin dashboard' => ['admin.dashboard'],
    'admin users' => ['admin.users.index'],
    'kepala dinas dashboard' => ['kepala-dinas.dashboard'],
]);

it('menolak distributor dari area peran lain', function (string $routeName) {
    $this->actingAs(User::factory()->distributor()->create())
        ->get(route($routeName))
        ->assertForbidden();
})->with('non distributor areas');

it('mengarahkan distributor dari halaman utama ke dashboard distributor', function () {
    $this->actingAs(User::factory()->distributor()->create())
        ->get('/')
        ->assertRedirect(route('distributor.dashboard'));
});
