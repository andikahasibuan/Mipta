<?php

use App\Enums\Role;
use App\Models\Distributor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('menampilkan daftar distributor dari database', function () {
    Distributor::factory()->create([
        'code' => 'DIST-001',
        'name' => 'PT Contoh Distributor',
    ]);
    Distributor::factory()->create([
        'code' => 'DIST-002',
        'name' => 'PT Distributor Nonaktif',
        'is_active' => false,
    ]);

    $this->actingAs(User::factory()->create())
        ->get(route('admin.distributors.index'))
        ->assertOk()
        ->assertSeeInOrder(['Kode', 'Nama', 'Status'])
        ->assertSee('DIST-001')
        ->assertSee('PT Contoh Distributor')
        ->assertSee('DIST-002')
        ->assertSee('Nonaktif');
});

it('menampilkan keadaan kosong saat belum ada distributor', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('admin.distributors.index'))
        ->assertOk()
        ->assertSee('Belum ada data distributor');
});

it('menampilkan navigasi tab ringkasan dan distributor', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('admin.distributors.index'))
        ->assertOk()
        ->assertSee('href="'.route('admin.users.index').'"', false)
        ->assertSee('href="'.route('admin.distributors.index').'"', false);
});

it('menyorot menu pengguna dan data master saat membuka daftar distributor', function () {
    $response = $this->actingAs(User::factory()->create())
        ->get(route('admin.distributors.index'));

    $html = $response->getContent();

    preg_match('/<nav[^>]*aria-label="Menu utama".*?<\/nav>/s', $html, $matches);
    $sidebar = $matches[0] ?? '';

    expect(substr_count($sidebar, 'aria-current="page"'))->toBe(1)
        ->and($sidebar)->toContain('Pengguna &amp; Data Master');
});

it('membatasi halaman distributor hanya untuk admin', function () {
    $this->get(route('admin.distributors.index'))->assertRedirect(route('login'));

    $this->actingAs(User::factory()->create(['role' => Role::Distributor]))
        ->get(route('admin.distributors.index'))
        ->assertForbidden();
});
