<?php

use App\Enums\Role;
use App\Models\Market;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('menampilkan daftar pasar dari database', function () {
    Market::factory()->create([
        'name' => 'Pasar Raya Padang',
        'lokasi' => 'Jln. Merdeka',
    ]);
    Market::factory()->create([
        'name' => 'Pasar Nonaktif',
        'lokasi' => 'Jln. Pattimura',
        'is_active' => false,
    ]);

    $this->actingAs(User::factory()->create())
        ->get(route('admin.markets.index'))
        ->assertOk()
        ->assertSeeInOrder(['ID Data', 'Nama Pasar', 'Lokasi', 'Status'])
        ->assertSee('Pasar Raya Padang')
        ->assertSee('Jln. Merdeka')
        ->assertSee('Pasar Nonaktif')
        ->assertSee('Nonaktif');
});

it('menampilkan keadaan kosong saat belum ada pasar', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('admin.markets.index'))
        ->assertOk()
        ->assertSee('Belum ada data pasar');
});

it('menampilkan navigasi tab pengguna, distributor, dan pasar', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('admin.markets.index'))
        ->assertOk()
        ->assertSee('href="'.route('admin.users.index').'"', false)
        ->assertSee('href="'.route('admin.distributors.index').'"', false)
        ->assertSee('href="'.route('admin.markets.index').'"', false);
});

it('menampilkan pagination saat pasar lebih dari sepuluh', function () {
    Market::factory()->count(11)->create();

    $this->actingAs(User::factory()->create())
        ->get(route('admin.markets.index'))
        ->assertOk()
        ->assertSee('?page=2', false);
});

it('mencari pasar berdasarkan nama atau lokasi', function () {
    $admin = User::factory()->create();

    Market::factory()->create(['name' => 'Pasar Raya', 'lokasi' => 'Jln. Merdeka']);
    Market::factory()->create(['name' => 'Pasar Lubuk Buaya', 'lokasi' => 'Jln. Adinegoro']);

    $this->actingAs($admin)
        ->get(route('admin.markets.index', ['q' => 'Raya']))
        ->assertOk()
        ->assertSee('Pasar Raya')
        ->assertDontSee('Pasar Lubuk Buaya');

    $this->actingAs($admin)
        ->get(route('admin.markets.index', ['q' => 'Adinegoro']))
        ->assertOk()
        ->assertSee('Pasar Lubuk Buaya')
        ->assertDontSee('Pasar Raya');
});

it('memfilter pasar berdasarkan status', function () {
    $admin = User::factory()->create();

    Market::factory()->create(['name' => 'Pasar Aktif']);
    Market::factory()->create(['name' => 'Pasar Mati', 'is_active' => false]);

    $this->actingAs($admin)
        ->get(route('admin.markets.index', ['status' => 'nonaktif']))
        ->assertOk()
        ->assertSee('Pasar Mati')
        ->assertDontSee('Pasar Aktif');
});

it('menampilkan pesan kosong saat filter pasar tidak menemukan data', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('admin.markets.index', ['q' => 'tidak-ada']))
        ->assertOk()
        ->assertSee('Tidak ada pasar yang sesuai filter')
        ->assertDontSee('Belum ada data pasar');
});

it('mempertahankan filter status pada tautan pagination pasar', function () {
    Market::factory()->count(11)->create();

    $this->actingAs(User::factory()->create())
        ->get(route('admin.markets.index', ['status' => 'aktif']))
        ->assertOk()
        ->assertSee('status=aktif', false);
});

it('membatasi halaman pasar hanya untuk admin', function () {
    $this->get(route('admin.markets.index'))->assertRedirect(route('login'));

    $this->actingAs(User::factory()->create(['role' => Role::Distributor]))
        ->get(route('admin.markets.index'))
        ->assertForbidden();
});
