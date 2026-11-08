<?php

use App\Enums\Role;
use App\Models\Market;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('hanya menampilkan akun berperan UPTD Pasar', function () {
    $admin = User::factory()->create(['name' => 'Admin MIPTA']);

    User::factory()->create([
        'name' => 'Kontributor Satu',
        'username' => 'kontributor.01',
        'role' => Role::UptdPasar,
    ]);

    User::factory()->inactive()->create([
        'name' => 'Kontributor Dua',
        'username' => 'kontributor.02',
        'role' => Role::UptdPasar,
    ]);

    User::factory()->create([
        'name' => 'Kepala Dinas Lain',
        'username' => 'kepala.lain',
        'role' => Role::KepalaDinas,
    ]);

    $this->actingAs($admin)
        ->get(route('admin.uptd-contributors.index'))
        ->assertOk()
        ->assertSeeInOrder(['Nama Kontributor', 'Pasar Terkait', 'Peran', 'Status Akun'])
        ->assertSee('Kontributor Satu')
        ->assertSee('Kontributor Dua')
        ->assertSee('Nonaktif')
        ->assertDontSee('Kepala Dinas Lain')
        ->assertSee('Menampilkan 2 dari 2 kontributor');
});

it('menampilkan pasar terkait pada kolom kontributor', function () {
    $admin = User::factory()->create();
    $market = Market::factory()->create(['name' => 'Pasar Raya Padang']);

    User::factory()->create([
        'name' => 'Kontributor Satu',
        'role' => Role::UptdPasar,
        'market_id' => $market->id,
    ]);

    $this->actingAs($admin)
        ->get(route('admin.uptd-contributors.index'))
        ->assertOk()
        ->assertSee('Pasar Raya Padang');
});

it('menampilkan keadaan kosong saat belum ada kontributor UPTD', function () {
    $this->actingAs(User::factory()->create(['role' => Role::Admin]))
        ->get(route('admin.uptd-contributors.index'))
        ->assertOk()
        ->assertSee('Belum ada data kontributor UPTD');
});

it('menampilkan navigasi tab hingga kontributor UPTD', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('admin.uptd-contributors.index'))
        ->assertOk()
        ->assertSee('href="'.route('admin.users.index').'"', false)
        ->assertSee('href="'.route('admin.distributors.index').'"', false)
        ->assertSee('href="'.route('admin.markets.index').'"', false)
        ->assertSee('href="'.route('admin.recipient-stores.index').'"', false)
        ->assertSee('href="'.route('admin.uptd-contributors.index').'"', false);
});

it('mencari kontributor berdasarkan nama, username, atau email', function () {
    $admin = User::factory()->create(['name' => 'Admin MIPTA']);

    User::factory()->create([
        'name' => 'Kontributor Pasar Raya',
        'username' => 'kontributor.raya',
        'email' => 'raya@mipta.test',
        'role' => Role::UptdPasar,
    ]);

    User::factory()->create([
        'name' => 'Kontributor Lubuk',
        'username' => 'kontributor.lubuk',
        'email' => 'lubuk@mipta.test',
        'role' => Role::UptdPasar,
    ]);

    $this->actingAs($admin)
        ->get(route('admin.uptd-contributors.index', ['q' => 'Raya']))
        ->assertOk()
        ->assertSee('Kontributor Pasar Raya')
        ->assertDontSee('Kontributor Lubuk');

    $this->actingAs($admin)
        ->get(route('admin.uptd-contributors.index', ['q' => 'lubuk@mipta.test']))
        ->assertOk()
        ->assertSee('Kontributor Lubuk')
        ->assertDontSee('Kontributor Pasar Raya');
});

it('memfilter kontributor berdasarkan status', function () {
    $admin = User::factory()->create();

    User::factory()->create([
        'name' => 'Kontributor Aktif',
        'role' => Role::UptdPasar,
    ]);

    User::factory()->inactive()->create([
        'name' => 'Kontributor Nonaktif',
        'role' => Role::UptdPasar,
    ]);

    $this->actingAs($admin)
        ->get(route('admin.uptd-contributors.index', ['status' => 'nonaktif']))
        ->assertOk()
        ->assertSee('Kontributor Nonaktif')
        ->assertDontSee('Kontributor Aktif')
        ->assertSee('Menampilkan 1 dari 1 kontributor');
});

it('menampilkan pesan kosong saat filter kontributor tidak menemukan data', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('admin.uptd-contributors.index', ['q' => 'tidak-ada']))
        ->assertOk()
        ->assertSee('Tidak ada kontributor UPTD yang sesuai filter')
        ->assertDontSee('Belum ada data kontributor UPTD');
});

it('menampilkan tombol tambah data pada tab kontributor', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('admin.uptd-contributors.index'))
        ->assertOk()
        ->assertSee('Tambah data')
        ->assertSee(route('admin.uptd-contributors.create'), false);
});

it('membatasi lima kontributor per halaman', function () {
    $admin = User::factory()->create(['name' => 'Admin MIPTA']);

    collect(range(1, 6))->each(fn (int $number): User => User::factory()->create([
        'name' => 'Kontributor 0'.$number,
        'username' => 'kontributor.0'.$number,
        'role' => Role::UptdPasar,
    ]));

    $this->actingAs($admin)
        ->get(route('admin.uptd-contributors.index'))
        ->assertOk()
        ->assertDontSee('Kontributor 06')
        ->assertSee('Menampilkan 5 dari 6 kontributor');
});

it('membatasi halaman kontributor hanya untuk admin', function () {
    $this->get(route('admin.uptd-contributors.index'))->assertRedirect(route('login'));

    $this->actingAs(User::factory()->create(['role' => Role::UptdPasar]))
        ->get(route('admin.uptd-contributors.index'))
        ->assertForbidden();
});

it('menolak mengelola akun yang bukan kontributor UPTD lewat halaman kontributor', function () {
    $admin = User::factory()->create();
    $kepalaDinas = User::factory()->create(['role' => Role::KepalaDinas]);

    $this->actingAs($admin)
        ->get(route('admin.uptd-contributors.edit', $kepalaDinas))
        ->assertNotFound();

    $this->delete(route('admin.uptd-contributors.destroy', $kepalaDinas))
        ->assertNotFound();

    $this->assertDatabaseHas('users', ['id' => $kepalaDinas->id]);
});
