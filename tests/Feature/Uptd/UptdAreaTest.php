<?php

use App\Enums\Role;
use App\Models\Market;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

dataset('uptd pages', [
    'dashboard' => ['uptd.dashboard', 'Dashboard UPTD Pasar'],
    'input laporan' => ['uptd.reports.create', 'Input Laporan'],
    'riwayat laporan' => ['uptd.reports.index', 'Riwayat Laporan'],
]);

it('menampilkan halaman kontributor UPTD dengan menu yang sesuai aktif', function (string $routeName, string $label) {
    $contributor = User::factory()->create([
        'role' => Role::UptdPasar,
        'market_id' => Market::factory(),
    ]);

    $response = $this->actingAs($contributor)->get(route($routeName));

    $response->assertOk()
        ->assertSee('<title>'.e($label).'</title>', false);

    $html = $response->getContent();

    preg_match('/<nav[^>]*aria-label="Menu utama".*?<\/nav>/s', $html, $matches);
    $sidebar = $matches[0] ?? '';

    expect(substr_count($sidebar, 'aria-current="page"'))->toBe(1)
        ->and($sidebar)->toMatch('/aria-current="page"[^>]*>(?:(?!<\/a>).)*'.preg_quote(e($label), '/').'/s');
})->with('uptd pages');

it('menampilkan seluruh menu sidebar kontributor UPTD', function () {
    $contributor = User::factory()->create([
        'role' => Role::UptdPasar,
        'market_id' => Market::factory(),
    ]);

    $this->actingAs($contributor)
        ->get(route('uptd.dashboard'))
        ->assertOk()
        ->assertSeeInOrder([
            'Dashboard UPTD Pasar',
            'Input Laporan',
            'Riwayat Laporan',
            'Ganti Kontributor',
            'Keluar dari Sistem',
        ]);
});

it('mengarahkan tamu dari halaman UPTD ke login', function (string $routeName) {
    $this->get(route($routeName))->assertRedirect(route('login'));
})->with('uptd pages');

it('menolak peran selain kontributor UPTD dari halaman UPTD', function (string $routeName) {
    $this->actingAs(User::factory()->create())
        ->get(route($routeName))
        ->assertForbidden();
})->with('uptd pages');

it('mengarahkan kontributor UPTD tanpa pasar dari halaman UPTD ke pemilihan identitas', function (string $routeName) {
    $shared = User::factory()->create(['role' => Role::UptdPasar]);

    $this->actingAs($shared)
        ->get(route($routeName))
        ->assertRedirect(route('uptd.contributors.index'));

    $this->assertAuthenticatedAs($shared);
})->with('uptd pages');

it('mengarahkan kontributor UPTD dari halaman utama ke dashboard UPTD', function () {
    $contributor = User::factory()->create([
        'role' => Role::UptdPasar,
        'market_id' => Market::factory(),
    ]);

    $this->actingAs($contributor)
        ->get('/')
        ->assertRedirect(route('uptd.dashboard'));
});

it('mengarahkan akun bersama UPTD dari halaman utama ke pemilihan identitas', function () {
    $shared = User::factory()->create(['role' => Role::UptdPasar]);

    $this->actingAs($shared)
        ->get('/')
        ->assertRedirect(route('uptd.contributors.index'));

    $this->assertAuthenticatedAs($shared);
});

it('menautkan menu ganti kontributor ke halaman pemilihan identitas', function () {
    $contributor = User::factory()->create([
        'role' => Role::UptdPasar,
        'market_id' => Market::factory(),
    ]);

    $this->actingAs($contributor)
        ->get(route('uptd.dashboard'))
        ->assertOk()
        ->assertSee('href="'.route('uptd.contributors.index').'"', false);
});
