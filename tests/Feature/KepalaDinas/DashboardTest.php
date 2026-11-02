<?php

use App\Enums\Role;
use App\Models\Distributor;
use App\Models\Market;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('mengarahkan tamu dari dashboard kepala dinas ke halaman login', function () {
    $this->get(route('kepala-dinas.dashboard'))->assertRedirect(route('login'));
});

it('menampilkan dashboard monitoring untuk kepala dinas', function () {
    $kepalaDinas = User::factory()->create([
        'name' => 'Dr. Kepala Dinas',
        'role' => Role::KepalaDinas,
    ]);

    Distributor::factory()->count(2)->create();
    Market::factory()->create();

    $this->actingAs($kepalaDinas)
        ->get(route('kepala-dinas.dashboard'))
        ->assertOk()
        ->assertSee('<title>Dashboard Kepala Dinas</title>', false)
        ->assertSee('Selamat datang, Dr. Kepala Dinas')
        ->assertSeeInOrder([
            'Total Laporan', '0',
            'Total Penyaluran', '0',
            'Distributor Aktif', '2',
            'Pasar Terpantau', '1',
        ])
        ->assertSee('Terapkan Filter')
        ->assertSee('Perkembangan Bulanan')
        ->assertSee('id="monthly-chart"', false)
        ->assertSee('Penyaluran per Distributor')
        ->assertSee('action="'.route('kepala-dinas.dashboard').'"', false);
});

it('menerapkan filter distributor pada dashboard kepala dinas', function () {
    $distributor = Distributor::factory()->create(['name' => 'PT Filter Kepala Dinas']);
    Distributor::factory()->create();

    $this->actingAs(User::factory()->create(['role' => Role::KepalaDinas]))
        ->get(route('kepala-dinas.dashboard', ['distributor' => $distributor->id]))
        ->assertOk()
        ->assertSee('PT Filter Kepala Dinas')
        ->assertSee('value="'.$distributor->id.'" selected', false)
        ->assertSeeInOrder(['Distributor Aktif', '1', 'Seluruhnya aktif']);
});

it('menolak admin membuka dashboard kepala dinas', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('kepala-dinas.dashboard'))
        ->assertForbidden();
});

it('menolak role lain membuka dashboard kepala dinas', function (Role $role) {
    $this->actingAs(User::factory()->create(['role' => $role]))
        ->get(route('kepala-dinas.dashboard'))
        ->assertForbidden();
})->with([
    Role::Distributor,
    Role::UptdPasar,
]);
