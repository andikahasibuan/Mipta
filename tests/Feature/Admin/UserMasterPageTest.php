<?php

use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('menampilkan tombol tambah data yang mengarah ke halaman tambah', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('admin.users.index'))
        ->assertOk()
        ->assertSee('Tambah data')
        ->assertSee(route('admin.users.create'), false)
        ->assertDontSee('aria-disabled="true"', false)
        ->assertDontSee('Ekspor Data');
});

it('menampilkan judul dan deskripsi pengguna dan data master di setiap tab', function () {
    $admin = User::factory()->create();

    collect([
        'admin.users.index',
        'admin.distributors.index',
        'admin.markets.index',
        'admin.recipient-stores.index',
        'admin.uptd-contributors.index',
    ])->each(function (string $route) use ($admin): void {
        $this->actingAs($admin)
            ->get(route($route))
            ->assertOk()
            ->assertSee('Pengguna & Data Master')
            ->assertSee('Cari, filter, dan kelola data sistem secara terpusat')
            ->assertDontSee('aria-label="Breadcrumb"', false);
    });
});

it('menampilkan tabel data pengguna dari database', function () {
    $admin = User::factory()->create([
        'name' => 'Admin MIPTA',
        'username' => 'admin.mipta',
    ]);

    User::factory()->create([
        'name' => 'Kepala Dinas',
        'username' => 'kepala.dinas',
        'role' => Role::KepalaDinas,
    ]);

    User::factory()->inactive()->create([
        'name' => 'Kontributor UPTD 01',
        'username' => 'kontributor.01',
        'role' => Role::UptdPasar,
    ]);

    $this->actingAs($admin)
        ->get(route('admin.users.index'))
        ->assertOk()
        ->assertSeeInOrder(['Nama / Identitas', 'Nama Akun', 'Peran', 'Status Akun'])
        ->assertSee('Data pengguna')
        ->assertSee('Admin MIPTA')
        ->assertSee('admin.mipta')
        ->assertSee('kepala.dinas')
        ->assertSee('UPTD Pasar')
        ->assertSee('Nonaktif')
        ->assertSee('Menampilkan 3 dari 3 pengguna')
        ->assertDontSee('Total Pengguna');
});

it('mencari pengguna berdasarkan nama, username, atau email', function () {
    $admin = User::factory()->create(['name' => 'Admin MIPTA', 'username' => 'admin.mipta']);

    User::factory()->create([
        'name' => 'Budi Santoso',
        'username' => 'budi.santoso',
        'email' => 'budi@mipta.test',
    ]);

    User::factory()->create([
        'name' => 'Siti Aminah',
        'username' => 'siti.aminah',
        'email' => 'siti@mipta.test',
    ]);

    $this->actingAs($admin)
        ->get(route('admin.users.index', ['q' => 'Budi']))
        ->assertOk()
        ->assertSee('Budi Santoso')
        ->assertDontSee('Siti Aminah')
        ->assertSee('Menampilkan 1 dari 1 pengguna');

    $this->actingAs($admin)
        ->get(route('admin.users.index', ['q' => 'siti@mipta.test']))
        ->assertOk()
        ->assertSee('Siti Aminah')
        ->assertDontSee('Budi Santoso');
});

it('menampilkan pesan kosong saat filter tidak menemukan pengguna', function () {
    $this->actingAs(User::factory()->create(['name' => 'Admin MIPTA']))
        ->get(route('admin.users.index', ['q' => 'tidak-ada-yang-cocok']))
        ->assertOk()
        ->assertSee('Tidak ada pengguna yang sesuai filter')
        ->assertDontSee('Belum ada data pengguna');
});

it('menyembunyikan semua akun distributor dari daftar pengguna', function () {
    $admin = User::factory()->create();

    User::factory()->distributor()->create(['name' => 'PT Akun Distributor']);
    User::factory()->create([
        'name' => 'Distributor Tanpa PT',
        'role' => Role::Distributor,
    ]);

    $this->actingAs($admin)
        ->get(route('admin.users.index'))
        ->assertOk()
        ->assertDontSee('PT Akun Distributor')
        ->assertDontSee('Distributor Tanpa PT')
        ->assertDontSee('value="'.Role::Distributor->value.'"', false);
});

it('memfilter pengguna berdasarkan peran dan status', function () {
    $admin = User::factory()->create(['name' => 'Admin MIPTA']);

    User::factory()->create([
        'name' => 'Kepala Terverifikasi',
        'username' => 'kepala.satu',
        'role' => Role::KepalaDinas,
    ]);

    User::factory()->inactive()->create([
        'name' => 'UPTD Nonaktif',
        'username' => 'uptd.nonaktif',
        'role' => Role::UptdPasar,
    ]);

    $this->actingAs($admin)
        ->get(route('admin.users.index', ['role' => 'kepala_dinas']))
        ->assertOk()
        ->assertSee('Kepala Terverifikasi')
        ->assertDontSee('UPTD Nonaktif');

    $this->actingAs($admin)
        ->get(route('admin.users.index', ['status' => 'nonaktif']))
        ->assertOk()
        ->assertSee('UPTD Nonaktif')
        ->assertDontSee('Kepala Terverifikasi')
        ->assertSee('Menampilkan 1 dari 1 pengguna');
});

it('mengabaikan filter peran dan status yang tidak dikenal', function () {
    $admin = User::factory()->create(['name' => 'Admin MIPTA']);
    User::factory()->create(['name' => 'Pengguna Lain', 'username' => 'pengguna.lain']);

    $this->actingAs($admin)
        ->get(route('admin.users.index', ['role' => 'superadmin', 'status' => 'entah']))
        ->assertOk()
        ->assertSee('Admin MIPTA')
        ->assertSee('Pengguna Lain')
        ->assertSee('Menampilkan 2 dari 2 pengguna');
});

it('mempertahankan filter pada tautan pagination', function () {
    $admin = User::factory()->create(['name' => 'Admin MIPTA']);

    collect(range(1, 6))->each(fn (int $number): User => User::factory()->create([
        'name' => 'Kepala '.$number,
        'username' => 'kepala.'.$number,
        'role' => Role::KepalaDinas,
    ]));

    $this->actingAs($admin)
        ->get(route('admin.users.index', ['role' => 'kepala_dinas']))
        ->assertOk()
        ->assertSee('role=kepala_dinas', false)
        ->assertSee('Menampilkan 5 dari 6 pengguna');
});

it('membatasi lima pengguna per halaman', function () {
    $admin = User::factory()->create([
        'name' => 'Admin MIPTA',
        'username' => 'admin.mipta',
    ]);

    collect(range(1, 6))->each(fn (int $number): User => User::factory()->create([
        'name' => 'Pengguna 0'.$number,
        'username' => 'pengguna.0'.$number,
    ]));

    $this->actingAs($admin)
        ->get(route('admin.users.index'))
        ->assertOk()
        ->assertSeeInOrder(['Pengguna 06', 'Pengguna 05', 'Pengguna 04', 'Pengguna 03', 'Pengguna 02'])
        ->assertDontSee('Pengguna 01')
        ->assertSee('Menampilkan 5 dari 7 pengguna');
});

it('menampilkan pengguna berikutnya pada halaman kedua', function () {
    $admin = User::factory()->create([
        'name' => 'Admin MIPTA',
        'username' => 'admin.mipta',
    ]);

    collect(range(1, 6))->each(fn (int $number): User => User::factory()->create([
        'name' => 'Pengguna 0'.$number,
        'username' => 'pengguna.0'.$number,
    ]));

    $this->actingAs($admin)
        ->get(route('admin.users.index', ['page' => 2]))
        ->assertOk()
        ->assertSeeInOrder(['Pengguna 01', 'Admin MIPTA'])
        ->assertDontSee('Pengguna 06')
        ->assertSee('Menampilkan 2 dari 7 pengguna');
});
