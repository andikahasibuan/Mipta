<?php

use App\Enums\Role;
use App\Models\UptdContributor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('menampilkan profil bulat pengguna yang sedang login di topbar', function () {
    $admin = User::factory()->create([
        'name' => 'Iqbal Shyhab',
        'username' => 'iqbal.shyhab',
    ]);

    $response = $this->actingAs($admin)
        ->get(route('admin.dashboard'));

    $response->assertOk()
        ->assertSeeInOrder(['Masuk sebagai', $admin->email, 'Logout'])
        ->assertSee('aria-label="Menu profil"', false)
        ->assertSee('action="'.route('logout').'"', false);

    preg_match('/<summary[^>]*aria-label="Menu profil".*?<\/summary>/s', $response->getContent(), $matches);
    $profileSummary = $matches[0] ?? '';

    expect($profileSummary)
        ->toContain('IS')
        ->not->toContain($admin->username);
});

it('menampilkan username kontributor UPTD yang dipilih di topbar', function () {
    $shared = User::factory()->create([
        'role' => Role::UptdPasar,
        'email' => null,
        'username' => 'uptd.bersama',
    ]);
    $contributor = UptdContributor::factory()->create([
        'username' => 'uptd.alai',
    ]);

    $response = $this->actingAs($shared)
        ->withSession(uptdContributorSession($contributor))
        ->get(route('uptd.dashboard'));

    $response->assertOk()
        ->assertSeeInOrder(['Masuk sebagai', $contributor->username, 'Logout'])
        ->assertSee($contributor->username)
        ->assertDontSee($shared->username)
        ->assertSee('aria-label="Menu profil"', false);

    preg_match('/<summary[^>]*aria-label="Menu profil".*?<\/summary>/s', $response->getContent(), $matches);
    $profileSummary = $matches[0] ?? '';

    expect($profileSummary)
        ->toContain($contributor->initials())
        ->not->toContain($contributor->username)
        ->not->toContain($shared->username);
});

it('mengambil inisial dari dua kata pertama nama', function (string $name, string $initials) {
    expect((new User(['name' => $name]))->initials())->toBe($initials);
})->with([
    'dua kata' => ['Iqbal Shyhab', 'IS'],
    'satu kata' => ['Administrator', 'A'],
    'tiga kata' => ['Badan Urusan Logistik', 'BU'],
]);

it('memakai label peran yang sesuai', function (Role $role, string $label) {
    expect($role->label())->toBe($label);
})->with([
    [Role::Admin, 'Admin'],
    [Role::Distributor, 'Distributor'],
    [Role::KepalaDinas, 'Kepala Dinas'],
    [Role::UptdPasar, 'UPTD Pasar'],
]);

it('menampilkan ikon notifikasi', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('aria-label="Notifikasi"', false)
        ->assertSee('Notifikasi')
        ->assertSee('Belum ada notifikasi')
        ->assertSee('Lihat semua pemberitahuan')
        ->assertSee(route('notifications.index'), false);
});

it('menampilkan ikon notifikasi untuk kepala dinas', function () {
    $kepalaDinas = User::factory()->create(['role' => Role::KepalaDinas]);

    $this->actingAs($kepalaDinas)
        ->get(route('kepala-dinas.dashboard'))
        ->assertOk()
        ->assertSee('aria-label="Notifikasi"', false)
        ->assertSee('Belum ada notifikasi');
});

it('menyembunyikan ikon notifikasi dari distributor dan kontributor uptd', function () {
    $distributor = User::factory()->distributor()->create();

    $this->actingAs($distributor)
        ->get(route('distributor.dashboard'))
        ->assertOk()
        ->assertDontSee('aria-label="Notifikasi"', false)
        ->assertDontSee('Belum ada notifikasi')
        ->assertDontSee('Lihat semua pemberitahuan');

    $shared = User::factory()->create(['role' => Role::UptdPasar]);
    $contributor = UptdContributor::factory()->create();

    $this->actingAs($shared)
        ->withSession(uptdContributorSession($contributor))
        ->get(route('uptd.dashboard'))
        ->assertOk()
        ->assertDontSee('aria-label="Notifikasi"', false)
        ->assertDontSee('Belum ada notifikasi')
        ->assertDontSee('Lihat semua pemberitahuan');
});
