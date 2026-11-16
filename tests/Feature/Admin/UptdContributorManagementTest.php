<?php

use App\Enums\Role;
use App\Models\Market;
use App\Models\UptdContributor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('hanya menampilkan profil kontributor UPTD yang terkait ke pasar', function () {
    $admin = User::factory()->create(['name' => 'Admin MIPTA']);

    UptdContributor::factory()->create([
        'name' => 'Kontributor Satu',
        'username' => 'kontributor.01',
        'market_id' => Market::factory(),
    ]);

    UptdContributor::factory()->inactive()->create([
        'name' => 'Kontributor Dua',
        'username' => 'kontributor.02',
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
        ->assertSeeInOrder(['Nama', 'Pasar Terkait', 'Username', 'Status'])
        ->assertSee('Kontributor Satu')
        ->assertSee('Kontributor Dua')
        ->assertSee('Nonaktif')
        ->assertDontSee('Kontributor Bersama')
        ->assertDontSee('Kepala Dinas Lain')
        ->assertSee('Menampilkan 2 dari 2 kontributor');
});

it('menyimpan username kontributor UPTD dalam huruf kecil', function () {
    $admin = User::factory()->create();
    $market = Market::factory()->create();

    $this->actingAs($admin)
        ->post(route('admin.uptd-contributors.store'), [
            'name' => 'Kontributor Kapital',
            'username' => 'Kontributor.Kapital',
            'market_id' => $market->id,
            'is_active' => '1',
        ])
        ->assertRedirect(route('admin.uptd-contributors.index'));

    $this->assertDatabaseHas('uptd_contributors', [
        'username' => 'kontributor.kapital',
    ]);
});

it('menyimpan profil kontributor UPTD dengan nama dan username', function () {
    $admin = User::factory()->create();
    $market = Market::factory()->create();

    $this->actingAs($admin)
        ->post(route('admin.uptd-contributors.store'), [
            'name' => 'Kontributor UPTD 01',
            'username' => 'kontributor.uptd.01',
            'market_id' => $market->id,
            'is_active' => '1',
        ])
        ->assertRedirect(route('admin.uptd-contributors.index'));

    $this->assertDatabaseHas('uptd_contributors', [
        'name' => 'Kontributor UPTD 01',
        'username' => 'kontributor.uptd.01',
        'market_id' => $market->id,
    ]);
});

it('menampilkan pasar terkait pada kolom kontributor', function () {
    $admin = User::factory()->create();
    $market = Market::factory()->create(['name' => 'Pasar Raya Padang']);

    UptdContributor::factory()->create([
        'name' => 'Kontributor Satu',
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

it('menampilkan navigasi tab data master hingga kontributor UPTD', function () {
    $response = $this->actingAs(User::factory()->create())
        ->get(route('admin.uptd-contributors.index'));

    $response->assertOk();

    preg_match('/<nav[^>]*aria-label="Navigasi Data Master".*?<\/nav>/s', $response->getContent(), $matches);
    $tabs = $matches[0] ?? '';

    expect($tabs)
        ->toContain(route('admin.markets.index'))
        ->toContain(route('admin.recipient-stores.index'))
        ->toContain(route('admin.uptd-contributors.index'));

    expect($tabs)->not->toContain(route('admin.users.index'));
    expect($tabs)->not->toContain(route('admin.distributors.index'));
});

it('mencari kontributor berdasarkan nama atau username', function () {
    $admin = User::factory()->create(['name' => 'Admin MIPTA']);

    UptdContributor::factory()->create([
        'name' => 'Kontributor Pasar Raya',
        'username' => 'kontributor.raya',
        'market_id' => Market::factory(),
    ]);

    UptdContributor::factory()->create([
        'name' => 'Kontributor Lubuk',
        'username' => 'kontributor.lubuk',
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

    UptdContributor::factory()->create([
        'name' => 'Kontributor Aktif',
        'market_id' => Market::factory(),
    ]);

    UptdContributor::factory()->inactive()->create([
        'name' => 'Kontributor Nonaktif',
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

it('menampilkan sepuluh kontributor per halaman', function () {
    $admin = User::factory()->create(['name' => 'Admin MIPTA']);

    collect(range(1, 11))->each(function (int $number): void {
        $suffix = str_pad((string) $number, 2, '0', STR_PAD_LEFT);

        UptdContributor::factory()->create([
            'name' => 'Kontributor '.$suffix,
            'username' => 'kontributor.'.$suffix,
            'market_id' => Market::factory(),
        ]);
    });

    $this->actingAs($admin)
        ->get(route('admin.uptd-contributors.index'))
        ->assertOk()
        ->assertSee('Menampilkan 10 dari 11 kontributor')
        ->assertSee('?page=2', false)
        ->assertSeeInOrder(['Kontributor 11', 'Kontributor 10', 'Kontributor 09'])
        ->assertDontSee('Kontributor 01');

    $this->get(route('admin.uptd-contributors.index', ['page' => 2]))
        ->assertOk()
        ->assertSee('Menampilkan 1 dari 11 kontributor')
        ->assertSee('Kontributor 01')
        ->assertDontSee('Kontributor 11');
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

    UptdContributor::factory()->create([
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

    UptdContributor::factory()->create([
        'market_id' => $usedMarket->id,
    ]);

    $this->actingAs($admin)
        ->get(route('admin.uptd-contributors.create'))
        ->assertOk()
        ->assertSee('Pasar Baru')
        ->assertDontSee('Pasar Terpakai')
        ->assertDontSee('Email')
        ->assertDontSee('Password');
});

it('menolak pasar yang sudah memiliki kontributor UPTD', function () {
    $admin = User::factory()->create();
    $market = Market::factory()->create();

    UptdContributor::factory()->create([
        'market_id' => $market->id,
    ]);

    $this->actingAs($admin)
        ->post(route('admin.uptd-contributors.store'), [
            'name' => 'Kontributor Baru',
            'username' => 'kontributor.baru',
            'market_id' => $market->id,
            'is_active' => '1',
        ])
        ->assertSessionHasErrors('market_id');

    expect(session('errors')->first('market_id'))
        ->toContain('Pasar ini sudah memiliki kontributor UPTD.');

    $this->assertDatabaseMissing('uptd_contributors', ['username' => 'kontributor.baru']);
});

it('mempertahankan pasar saat ini dan hanya menampilkan pasar kosong pada form edit', function () {
    $admin = User::factory()->create();
    $currentMarket = Market::factory()->create(['name' => 'Pasar Saat Ini']);
    $availableMarket = Market::factory()->create(['name' => 'Pasar Kosong']);
    $usedMarket = Market::factory()->create(['name' => 'Pasar Terpakai Lain']);

    $contributor = UptdContributor::factory()->create([
        'market_id' => $currentMarket->id,
    ]);

    UptdContributor::factory()->create([
        'market_id' => $usedMarket->id,
    ]);

    $this->actingAs($admin)
        ->get(route('admin.uptd-contributors.edit', $contributor))
        ->assertOk()
        ->assertSee('Pasar Saat Ini')
        ->assertSee('Pasar Kosong')
        ->assertDontSee('Pasar Terpakai Lain')
        ->assertDontSee('Email')
        ->assertDontSee('Password');
});

it('menolak memindahkan kontributor ke pasar yang sudah memiliki kontributor lain', function () {
    $admin = User::factory()->create();
    $currentMarket = Market::factory()->create();
    $usedMarket = Market::factory()->create();

    $contributor = UptdContributor::factory()->create([
        'market_id' => $currentMarket->id,
    ]);

    UptdContributor::factory()->create([
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
