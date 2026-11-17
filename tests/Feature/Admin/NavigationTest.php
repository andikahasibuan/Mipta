<?php

use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

dataset('admin pages', [
    'dashboard' => ['admin.dashboard', 'Dashboard', 'Dashboard'],
    'users' => ['admin.users.index', 'Pengguna', 'Data Pengguna'],
    'distribution' => ['admin.distribution.index', 'Penyaluran', 'Penyaluran'],
    'supervision' => ['admin.supervision.index', 'Pengawasan', 'Pengawasan'],
]);

it('menampilkan halaman admin dengan menu yang sesuai aktif', function (string $routeName, string $label, string $title) {
    $response = $this->actingAs(User::factory()->create())->get(route($routeName));

    $response->assertOk()
        ->assertSee('<title>'.e($title).'</title>', false)
        ->assertSee($label);

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

    foreach (['squares-2x2', 'users', 'table-cells', 'document-text', 'shield-check'] as $icon) {
        $response->assertSee('data-icon="'.$icon.'"', false);
    }
});

dataset('user tab pages', [
    'admin' => ['admin.users.index', [], 'Admin'],
    'kepala dinas' => ['admin.users.index', ['role' => 'kepala_dinas'], 'Kepala Dinas'],
    'uptd' => ['admin.users.index', ['role' => 'uptd_pasar'], 'UPTD'],
    'distributor' => ['admin.distributors.index', [], 'Distributor'],
    'uptd contributor' => ['admin.users.uptd-contributors.index', [], 'Kontributor UPTD'],
]);

it('menjaga konsistensi tab pengguna dan sorotan sidebar', function (string $routeName, array $query, string $tabLabel) {
    $response = $this->actingAs(User::factory()->create())->get(route($routeName, $query));

    $response->assertOk();

    $html = $response->getContent();

    preg_match('/<nav[^>]*aria-label="Navigasi Pengguna".*?<\/nav>/s', $html, $tabMatches);
    $tabsNav = $tabMatches[0] ?? '';

    foreach (['Admin', 'Kepala Dinas', 'Distributor', 'Kontributor UPTD', 'UPTD'] as $label) {
        expect($tabsNav)->toContain($label);
    }

    expect(substr_count($tabsNav, 'aria-current="page"'))->toBe(1)
        ->and($tabsNav)->toMatch('/aria-current="page"[^>]*>\s*'.preg_quote($tabLabel, '/').'\s*<\/a>/s');

    preg_match('/<nav[^>]*aria-label="Menu utama".*?<\/nav>/s', $html, $sidebarMatches);
    $sidebar = $sidebarMatches[0] ?? '';

    expect(substr_count($sidebar, 'aria-current="page"'))->toBe(1)
        ->and($sidebar)->toContain('Pengguna');
})->with('user tab pages');

dataset('master data tab pages', [
    'pasar' => ['admin.markets.index', 'Pasar'],
    'toko penerima' => ['admin.recipient-stores.index', 'Toko Penerima'],
]);

it('menjaga konsistensi tab data master dan sorotan sidebar', function (string $routeName, string $tabLabel) {
    $response = $this->actingAs(User::factory()->create())->get(route($routeName));

    $response->assertOk();

    $html = $response->getContent();

    preg_match('/<nav[^>]*aria-label="Navigasi Data Master".*?<\/nav>/s', $html, $tabMatches);
    $tabsNav = $tabMatches[0] ?? '';

    foreach (['Pasar', 'Toko Penerima'] as $label) {
        expect($tabsNav)->toContain($label);
    }

    expect($tabsNav)->not->toContain('Kontributor UPTD');

    expect(substr_count($tabsNav, 'aria-current="page"'))->toBe(1)
        ->and($tabsNav)->toMatch('/aria-current="page"[^>]*>\s*'.preg_quote($tabLabel, '/').'\s*<\/a>/s');

    preg_match('/<nav[^>]*aria-label="Menu utama".*?<\/nav>/s', $html, $sidebarMatches);
    $sidebar = $sidebarMatches[0] ?? '';

    expect(substr_count($sidebar, 'aria-current="page"'))->toBe(1)
        ->and($sidebar)->toContain('Data Master');
})->with('master data tab pages');
