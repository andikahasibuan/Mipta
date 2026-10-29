<?php

use App\Enums\Role;
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
