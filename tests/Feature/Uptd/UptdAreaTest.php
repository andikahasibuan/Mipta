<?php

use App\Enums\Role;
use App\Models\Market;
use App\Models\UptdContributor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

dataset('uptd pages', [
    'dashboard' => ['uptd.dashboard', 'Dashboard', 'Dashboard'],
    'input laporan' => ['uptd.reports.create', 'Input Laporan', 'Laporan'],
    'riwayat laporan' => ['uptd.reports.index', 'Riwayat Laporan', 'Riwayat'],
]);

it('menampilkan halaman kontributor UPTD dengan menu yang sesuai aktif', function (string $routeName, string $title, string $sidebarLabel) {
    $contributor = UptdContributor::factory()->create(['market_id' => Market::factory()]);

    $response = $this->actingAs(sharedUptdAccount())
        ->withSession(uptdContributorSession($contributor))
        ->get(route($routeName));

    $response->assertOk()
        ->assertSee('<title>'.e($title).'</title>', false);

    $html = $response->getContent();

    preg_match('/<nav[^>]*aria-label="Menu utama".*?<\/nav>/s', $html, $matches);
    $sidebar = $matches[0] ?? '';

    expect(substr_count($sidebar, 'aria-current="page"'))->toBe(1)
        ->and($sidebar)->toMatch('/aria-current="page"[^>]*>(?:(?!<\/a>).)*'.preg_quote(e($sidebarLabel), '/').'/s');
})->with('uptd pages');

it('menampilkan seluruh menu sidebar kontributor UPTD', function () {
    $contributor = UptdContributor::factory()->create(['market_id' => Market::factory()]);

    $this->actingAs(sharedUptdAccount())
        ->withSession(uptdContributorSession($contributor))
        ->get(route('uptd.dashboard'))
        ->assertOk()
        ->assertSeeInOrder([
            'Dashboard',
            'Laporan',
            'Riwayat',
            'Ganti Kontributor',
            'Logout',
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

it('mengarahkan akun bersama UPTD tanpa identitas terpilih dari halaman UPTD ke pemilihan identitas', function (string $routeName) {
    $shared = sharedUptdAccount();

    $this->actingAs($shared)
        ->get(route($routeName))
        ->assertRedirect(route('uptd.contributors.index'));

    $this->assertAuthenticatedAs($shared);
})->with('uptd pages');

it('mengarahkan akun UPTD yang tidak terkait ke dashboard dari halaman utama', function () {
    $contributor = User::factory()->create([
        'role' => Role::UptdPasar,
        'market_id' => Market::factory(),
    ]);

    $this->actingAs($contributor)
        ->get('/')
        ->assertRedirect(route('uptd.dashboard'));
});

it('mengarahkan akun bersama UPTD dari halaman utama ke pemilihan identitas', function () {
    $shared = sharedUptdAccount();

    $this->actingAs($shared)
        ->get('/')
        ->assertRedirect(route('uptd.contributors.index'));

    $this->assertAuthenticatedAs($shared);
});

it('menautkan menu ganti kontributor ke halaman pemilihan identitas', function () {
    $contributor = UptdContributor::factory()->create(['market_id' => Market::factory()]);

    $this->actingAs(sharedUptdAccount())
        ->withSession(uptdContributorSession($contributor))
        ->get(route('uptd.dashboard'))
        ->assertOk()
        ->assertSee('href="'.route('uptd.contributors.index').'"', false);
});
