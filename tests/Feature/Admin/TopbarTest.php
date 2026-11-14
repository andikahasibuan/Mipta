<?php

use App\Enums\Role;
use App\Models\Market;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('menampilkan profil pengguna yang sedang login di topbar', function () {
    $admin = User::factory()->create(['name' => 'Iqbal Shyhab']);

    $this->actingAs($admin)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSeeInOrder(['IS', 'Iqbal Shyhab', 'Admin'])
        ->assertSeeInOrder(['Masuk sebagai', $admin->email, 'Keluar'])
        ->assertSee('aria-label="Menu profil"', false)
        ->assertSee('action="'.route('logout').'"', false);
});

it('menampilkan username di dropdown profil untuk kontributor tanpa email', function () {
    $contributor = User::factory()->create([
        'role' => Role::UptdPasar,
        'market_id' => Market::factory(),
        'email' => null,
    ]);

    $this->actingAs($contributor)
        ->get(route('uptd.dashboard'))
        ->assertOk()
        ->assertSeeInOrder(['Masuk sebagai', $contributor->username, 'Keluar']);
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
        ->assertSee('Belum ada notifikasi');
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
        ->assertDontSee('Belum ada notifikasi');

    $uptd = User::factory()->create([
        'role' => Role::UptdPasar,
        'market_id' => Market::factory(),
    ]);

    $this->actingAs($uptd)
        ->get(route('uptd.dashboard'))
        ->assertOk()
        ->assertDontSee('aria-label="Notifikasi"', false)
        ->assertDontSee('Belum ada notifikasi');
});
