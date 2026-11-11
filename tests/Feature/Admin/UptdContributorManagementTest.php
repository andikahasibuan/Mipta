<?php

use App\Enums\Role;
use App\Models\Market;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('hanya menampilkan kontributor UPTD yang terkait ke pasar', function () {
    $admin = User::factory()->create(['name' => 'Admin MIPTA']);

    User::factory()->create([
        'name' => 'Kontributor Satu',
        'username' => 'kontributor.01',
        'role' => Role::UptdPasar,
        'market_id' => Market::factory(),
    ]);

    User::factory()->inactive()->create([
        'name' => 'Kontributor Dua',
        'username' => 'kontributor.02',
        'role' => Role::UptdPasar,
        'market_id' => Market::factory(),
    ]);

    User::factory()->create([
        'name' => 'Kontributor Bersama',
        'username' => 'kontributor.bersama',
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
        ->assertDontSee('Kontributor Bersama')
        ->assertDontSee('Kepala Dinas Lain')
        ->assertSee('Menampilkan 2 dari 2 kontributor');
});

it('menyimpan kontributor UPTD dengan nama akun generik dan username bernomor', function () {
    $admin = User::factory()->create();
    $market = Market::factory()->create();

    $this->actingAs($admin)
        ->post(route('admin.uptd-contributors.store'), [
            'name' => 'Kontributor UPTD 01',
            'username' => 'kontributor.uptd.01',
            'password' => 'password123',
            'market_id' => $market->id,
            'is_active' => '1',
        ])
        ->assertRedirect(route('admin.uptd-contributors.index'));

    $this->assertDatabaseHas('users', [
        'name' => 'Kontributor UPTD 01',
        'username' => 'kontributor.uptd.01',
        'email' => null,
        'role' => Role::UptdPasar->value,
        'market_id' => $market->id,
    ]);
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

it('mencari kontributor berdasarkan nama atau username', function () {
    $admin = User::factory()->create(['name' => 'Admin MIPTA']);

    User::factory()->create([
        'name' => 'Kontributor Pasar Raya',
        'username' => 'kontributor.raya',
        'role' => Role::UptdPasar,
        'market_id' => Market::factory(),
    ]);

    User::factory()->create([
        'name' => 'Kontributor Lubuk',
        'username' => 'kontributor.lubuk',
        'role' => Role::UptdPasar,
        'market_id' => Market::factory(),
    ]);

    $this->actingAs($admin)
        ->get(route('admin.uptd-contributors.index', ['q' => 'Raya']))
        ->assertOk()
        ->assertSee('Kontributor Pasar Raya')
        ->assertDontSee('Kontributor Lubuk');

    $this->actingAs($admin)
        ->get(route('admin.uptd-contributors.index', ['q' => 'kontributor.lubuk']))
        ->assertOk()
        ->assertSee('Kontributor Lubuk')
        ->assertDontSee('Kontributor Pasar Raya');
});

it('memfilter kontributor berdasarkan status', function () {
    $admin = User::factory()->create();

    User::factory()->create([
        'name' => 'Kontributor Aktif',
        'role' => Role::UptdPasar,
        'market_id' => Market::factory(),
    ]);

    User::factory()->inactive()->create([
        'name' => 'Kontributor Nonaktif',
        'role' => Role::UptdPasar,
        'market_id' => Market::factory(),
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
        'market_id' => Market::factory(),
    ]));

    $this->actingAs($admin)
        ->get(route('admin.uptd-contributors.index'))
        ->assertOk()
        ->assertSee('Kontributor 06')
        ->assertDontSee('Kontributor 01')
        ->assertSee('Menampilkan 5 dari 6 kontributor');
});

it('membatasi halaman kontributor hanya untuk admin', function () {
    $this->get(route('admin.uptd-contributors.index'))->assertRedirect(route('login'));

    $this->actingAs(User::factory()->create(['role' => Role::UptdPasar]))
        ->get(route('admin.uptd-contributors.index'))
        ->assertForbidden();
});

it('menolak mengelola akun bersama lewat tab kontributor UPTD', function () {
    $admin = User::factory()->create();
    $shared = User::factory()->create(['role' => Role::UptdPasar]);

    $this->actingAs($admin)
        ->get(route('admin.uptd-contributors.edit', $shared))
        ->assertNotFound();

    $this->delete(route('admin.uptd-contributors.destroy', $shared))
        ->assertNotFound();

    $this->assertDatabaseHas('users', ['id' => $shared->id]);
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

it('menampilkan pesan untuk menambah pasar baru saat semua pasar sudah memiliki kontributor', function () {
    $admin = User::factory()->create();
    $market = Market::factory()->create();

    User::factory()->create([
        'role' => Role::UptdPasar,
        'market_id' => $market->id,
    ]);

    $this->actingAs($admin)
        ->get(route('admin.uptd-contributors.create'))
        ->assertOk()
        ->assertSee('Belum ada pasar yang tersedia')
        ->assertSee(route('admin.markets.create'), false);
});

it('hanya menampilkan pasar yang belum memiliki kontributor pada form tambah', function () {
    $admin = User::factory()->create();

    Market::factory()->create(['name' => 'Pasar Baru']);
    $usedMarket = Market::factory()->create(['name' => 'Pasar Terpakai']);

    User::factory()->create([
        'role' => Role::UptdPasar,
        'market_id' => $usedMarket->id,
    ]);

    $this->actingAs($admin)
        ->get(route('admin.uptd-contributors.create'))
        ->assertOk()
        ->assertSee('Pasar Baru')
        ->assertDontSee('Pasar Terpakai')
        ->assertDontSee('Email');
});

it('menolak pasar yang sudah memiliki kontributor UPTD', function () {
    $admin = User::factory()->create();
    $market = Market::factory()->create();

    User::factory()->create([
        'role' => Role::UptdPasar,
        'market_id' => $market->id,
    ]);

    $this->actingAs($admin)
        ->post(route('admin.uptd-contributors.store'), [
            'name' => 'Kontributor Baru',
            'username' => 'kontributor.baru',
            'password' => 'password123',
            'market_id' => $market->id,
            'is_active' => '1',
        ])
        ->assertSessionHasErrors('market_id');

    expect(session('errors')->first('market_id'))
        ->toContain('Pasar ini sudah memiliki kontributor UPTD.');

    $this->assertDatabaseMissing('users', ['username' => 'kontributor.baru']);
});

it('mempertahankan pasar saat ini dan hanya menampilkan pasar kosong pada form edit', function () {
    $admin = User::factory()->create();
    $currentMarket = Market::factory()->create(['name' => 'Pasar Saat Ini']);
    $availableMarket = Market::factory()->create(['name' => 'Pasar Kosong']);
    $usedMarket = Market::factory()->create(['name' => 'Pasar Terpakai Lain']);

    $contributor = User::factory()->create([
        'role' => Role::UptdPasar,
        'market_id' => $currentMarket->id,
    ]);

    User::factory()->create([
        'role' => Role::UptdPasar,
        'market_id' => $usedMarket->id,
    ]);

    $this->actingAs($admin)
        ->get(route('admin.uptd-contributors.edit', $contributor))
        ->assertOk()
        ->assertSee('Pasar Saat Ini')
        ->assertSee('Pasar Kosong')
        ->assertDontSee('Pasar Terpakai Lain')
        ->assertDontSee('Email');
});

it('menolak memindahkan kontributor ke pasar yang sudah memiliki kontributor lain', function () {
    $admin = User::factory()->create();
    $currentMarket = Market::factory()->create();
    $usedMarket = Market::factory()->create();

    $contributor = User::factory()->create([
        'role' => Role::UptdPasar,
        'market_id' => $currentMarket->id,
    ]);

    User::factory()->create([
        'role' => Role::UptdPasar,
        'market_id' => $usedMarket->id,
    ]);

    $this->actingAs($admin)
        ->put(route('admin.uptd-contributors.update', $contributor), [
            'name' => $contributor->name,
            'username' => $contributor->username,
            'market_id' => $usedMarket->id,
            'is_active' => '1',
        ])
        ->assertSessionHasErrors('market_id');

    expect($contributor->fresh()->market_id)->toBe($currentMarket->id);
});
