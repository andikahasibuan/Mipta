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
        ->assertSee('Nonaktif')
        ->assertSee('Menampilkan 2 dari 2 distributor');
});

it('menampilkan keadaan kosong saat belum ada distributor', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('admin.distributors.index'))
        ->assertOk()
        ->assertSee('Belum ada data distributor');
});

it('menampilkan lima tab pengguna dan data master tanpa tab ringkasan', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('admin.distributors.index'))
        ->assertOk()
        ->assertSeeInOrder(['Pengguna', 'Distributor', 'Pasar', 'Toko Penerima', 'Kontributor UPTD'])
        ->assertDontSee('Ringkasan')
        ->assertSee('href="'.route('admin.users.index').'"', false)
        ->assertSee('href="'.route('admin.markets.index').'"', false)
        ->assertSee('href="'.route('admin.recipient-stores.index').'"', false)
        ->assertSee('href="'.route('admin.uptd-contributors.index').'"', false);
});

it('menyorot tab aktif pada navigasi pengguna dan data master', function () {
    $response = $this->actingAs(User::factory()->create())
        ->get(route('admin.distributors.index'));

    preg_match('/<nav[^>]*aria-label="Navigasi Pengguna dan Data Master".*?<\/nav>/s', $response->getContent(), $matches);
    $tabs = $matches[0] ?? '';

    expect(substr_count($tabs, 'aria-current="page"'))->toBe(1)
        ->and($tabs)->toContain('aria-current="page"');
});

it('mencari distributor berdasarkan kode atau nama', function () {
    $admin = User::factory()->create();

    Distributor::factory()->create(['code' => 'DIST-AAA', 'name' => 'PT Sumber Makmur']);
    Distributor::factory()->create(['code' => 'DIST-BBB', 'name' => 'PT Lain Sekali']);

    $this->actingAs($admin)
        ->get(route('admin.distributors.index', ['q' => 'Sumber']))
        ->assertOk()
        ->assertSee('PT Sumber Makmur')
        ->assertDontSee('PT Lain Sekali');

    $this->actingAs($admin)
        ->get(route('admin.distributors.index', ['q' => 'DIST-BBB']))
        ->assertOk()
        ->assertSee('PT Lain Sekali')
        ->assertDontSee('PT Sumber Makmur');
});

it('memfilter distributor berdasarkan status', function () {
    $admin = User::factory()->create();

    Distributor::factory()->create(['name' => 'PT Distributor Aktif']);
    Distributor::factory()->create(['name' => 'PT Distributor Mati', 'is_active' => false]);

    $this->actingAs($admin)
        ->get(route('admin.distributors.index', ['status' => 'nonaktif']))
        ->assertOk()
        ->assertSee('PT Distributor Mati')
        ->assertDontSee('PT Distributor Aktif')
        ->assertSee('Menampilkan 1 dari 1 distributor');
});

it('menampilkan pesan kosong saat filter distributor tidak menemukan data', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('admin.distributors.index', ['q' => 'tidak-ada']))
        ->assertOk()
        ->assertSee('Tidak ada distributor yang sesuai filter')
        ->assertDontSee('Belum ada data distributor');
});

it('mempertahankan filter status pada tautan pagination', function () {
    Distributor::factory()->count(11)->create();

    $this->actingAs(User::factory()->create())
        ->get(route('admin.distributors.index', ['status' => 'aktif']))
        ->assertOk()
        ->assertSee('status=aktif', false);
});

it('membatasi halaman distributor hanya untuk admin', function () {
    $this->get(route('admin.distributors.index'))->assertRedirect(route('login'));

    $this->actingAs(User::factory()->create(['role' => Role::Distributor]))
        ->get(route('admin.distributors.index'))
        ->assertForbidden();
});
