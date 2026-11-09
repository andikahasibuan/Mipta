<?php

use App\Enums\Role;
use App\Models\Distributor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

it('menampilkan daftar distributor dari database', function () {
    Distributor::factory()->create([
        'name' => 'PT Contoh Distributor',
        'wilayah' => 'Padang',
    ]);
    Distributor::factory()->create([
        'name' => 'PT Distributor Nonaktif',
        'wilayah' => 'Padang',
        'is_active' => false,
    ]);

    $this->actingAs(User::factory()->create())
        ->get(route('admin.distributors.index'))
        ->assertOk()
        ->assertSeeInOrder(['ID Data', 'Nama Distributor', 'Wilayah', 'Status'])
        ->assertSee('DST-001')
        ->assertSee('DST-002')
        ->assertSee('PT Contoh Distributor')
        ->assertSee('Padang')
        ->assertSee('PT Distributor Nonaktif')
        ->assertSee('Nonaktif')
        ->assertSee('Menampilkan 2 dari 2 distributor');
});

it('menampilkan keadaan kosong saat belum ada distributor', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('admin.distributors.index'))
        ->assertOk()
        ->assertSee('Belum ada data distributor');
});

it('menampilkan formulir tambah distributor beserta akun perusahaan', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('admin.distributors.create'))
        ->assertOk()
        ->assertSee('Nama Perusahaan Distributor')
        ->assertSee('Nama Penanggung Jawab')
        ->assertSee('No. Handphone Penanggung Jawab')
        ->assertSee('Email Perusahaan')
        ->assertSee('Password')
        ->assertDontSee('Wilayah')
        ->assertDontSee('Username')
        ->assertDontSee('Alamat Kantor')
        ->assertDontSee('Status aktif');
});

it('menyimpan distributor baru dengan wilayah otomatis dan username dari nama perusahaan', function () {
    $this->actingAs(User::factory()->create());

    $this->post(route('admin.distributors.store'), [
        'name' => 'PT Profil Lengkap',
        'penanggung_jawab' => 'Budi Santoso',
        'telepon' => '081234567890',
        'email' => 'profil@example.com',
        'password' => 'password123',
    ])->assertRedirect(route('admin.distributors.index'));

    $this->assertDatabaseHas('distributors', [
        'name' => 'PT Profil Lengkap',
        'wilayah' => 'Padang',
        'penanggung_jawab' => 'Budi Santoso',
        'telepon' => '081234567890',
        'alamat_kantor' => null,
        'is_active' => true,
    ]);

    $this->assertDatabaseHas('users', [
        'name' => 'PT Profil Lengkap',
        'username' => 'pt-profil-lengkap',
        'email' => 'profil@example.com',
        'role' => Role::Distributor->value,
        'is_active' => true,
    ]);
});

it('membuat username unik dengan akhiran angka saat nama perusahaan sama', function () {
    $this->actingAs(User::factory()->create());

    $payload = [
        'name' => 'PT Sama Nama',
        'penanggung_jawab' => 'Budi Santoso',
        'telepon' => '081234567890',
        'password' => 'password123',
    ];

    $this->post(route('admin.distributors.store'), [...$payload, 'email' => 'satu@example.com'])
        ->assertRedirect(route('admin.distributors.index'));
    $this->post(route('admin.distributors.store'), [...$payload, 'email' => 'dua@example.com'])
        ->assertRedirect(route('admin.distributors.index'));

    $this->assertDatabaseHas('users', ['username' => 'pt-sama-nama']);
    $this->assertDatabaseHas('users', ['username' => 'pt-sama-nama-2']);
});

it('memperbarui data distributor tanpa mengubah username, wilayah, alamat, dan status', function () {
    $this->actingAs(User::factory()->create());
    $distributor = Distributor::factory()->create([
        'name' => 'PT Profil Admin',
        'wilayah' => 'Padang',
        'penanggung_jawab' => 'Pengelola Lama',
        'telepon' => '0751-000000',
        'alamat_kantor' => 'Alamat Lama',
        'is_active' => false,
    ]);
    $account = User::factory()->distributor()->create([
        'distributor_id' => $distributor->id,
        'name' => 'PT Profil Admin',
        'username' => 'pt-profil-admin',
        'email' => 'admin@example.com',
    ]);

    $this->get(route('admin.distributors.edit', $distributor))
        ->assertOk()
        ->assertSee('Pengelola Lama')
        ->assertSee('0751-000000')
        ->assertSee('admin@example.com')
        ->assertDontSee('Username')
        ->assertDontSee('Alamat Kantor')
        ->assertDontSee('Status aktif');

    $this->put(route('admin.distributors.update', $distributor), [
        'name' => 'PT Nama Berubah',
        'penanggung_jawab' => 'Pengelola Baru',
        'telepon' => '0812345678',
        'email' => 'baru@example.com',
    ])->assertRedirect(route('admin.distributors.index'));

    $distributor->refresh();
    $account->refresh();

    expect($distributor->name)->toBe('PT Nama Berubah')
        ->and($distributor->penanggung_jawab)->toBe('Pengelola Baru')
        ->and($distributor->telepon)->toBe('0812345678')
        ->and($distributor->wilayah)->toBe('Padang')
        ->and($distributor->alamat_kantor)->toBe('Alamat Lama')
        ->and($distributor->is_active)->toBeFalse()
        ->and($account->name)->toBe('PT Nama Berubah')
        ->and($account->username)->toBe('pt-profil-admin')
        ->and($account->email)->toBe('baru@example.com');
});

it('menampilkan email akun perusahaan dan tanda minus alamat kantor kosong pada daftar distributor', function () {
    $distributor = Distributor::factory()->create([
        'name' => 'PT Akun',
        'alamat_kantor' => null,
    ]);
    User::factory()->distributor()->create([
        'distributor_id' => $distributor->id,
        'email' => 'akun@pt.test',
    ]);

    $this->actingAs(User::factory()->create())
        ->get(route('admin.distributors.index'))
        ->assertOk()
        ->assertSeeInOrder(['Alamat Kantor', 'Email Akun'])
        ->assertSee('akun@pt.test')
        ->assertSeeInOrder(['Alamat Kantor', '-']);
});

it('membuat akun untuk distributor lama yang belum memiliki akun', function () {
    $this->actingAs(User::factory()->create());
    $distributor = Distributor::factory()->create(['name' => 'PT Lama', 'wilayah' => 'Padang']);

    $this->put(route('admin.distributors.update', $distributor), [
        'name' => 'PT Lama',
        'penanggung_jawab' => 'Pengelola Lama',
        'telepon' => '081234567890',
        'email' => 'pt.lama@example.com',
        'password' => 'password123',
    ])->assertRedirect(route('admin.distributors.index'));

    $this->assertDatabaseHas('users', [
        'name' => 'PT Lama',
        'email' => 'pt.lama@example.com',
        'username' => 'pt-lama',
        'role' => Role::Distributor->value,
        'distributor_id' => $distributor->id,
    ]);
});

it('menolak email yang sudah dipakai akun lain', function () {
    $this->actingAs(User::factory()->create());
    User::factory()->create([
        'email' => 'dipakai@example.com',
        'username' => 'dipakai',
    ]);

    $this->post(route('admin.distributors.store'), [
        'name' => 'PT Duplikat',
        'penanggung_jawab' => 'Budi Santoso',
        'telepon' => '081234567890',
        'email' => 'dipakai@example.com',
        'password' => 'password123',
    ])->assertSessionHasErrors(['email']);

    $this->assertDatabaseMissing('distributors', ['name' => 'PT Duplikat']);
});

it('menyinkronkan nama dan email akun tanpa mengubah status saat distributor diperbarui', function () {
    $this->actingAs(User::factory()->create());
    $distributor = Distributor::factory()->create(['name' => 'PT Awal', 'is_active' => false]);
    $account = User::factory()->distributor()->create([
        'distributor_id' => $distributor->id,
        'name' => 'PT Awal',
        'email' => 'awal@example.com',
        'username' => 'awal',
        'password' => 'password-lama',
        'is_active' => false,
    ]);

    $this->put(route('admin.distributors.update', $distributor), [
        'name' => 'PT Baru',
        'penanggung_jawab' => 'Pengelola Baru',
        'telepon' => '081234567890',
        'email' => 'baru@example.com',
    ])->assertRedirect(route('admin.distributors.index'));

    $account->refresh();

    expect($account->name)->toBe('PT Baru')
        ->and($account->email)->toBe('baru@example.com')
        ->and($account->username)->toBe('awal')
        ->and($account->is_active)->toBeFalse()
        ->and(Hash::check('password-lama', $account->password))->toBeTrue();
});

it('menolak nama perusahaan, nama penanggung jawab, dan no handphone yang mengandung karakter tidak valid', function () {
    $this->actingAs(User::factory()->create());

    $this->post(route('admin.distributors.store'), [
        'name' => 'PT !@#$%',
        'penanggung_jawab' => 'Budi 123',
        'telepon' => '0812-3456-7890',
        'email' => 'tidak.valid@example.com',
        'password' => 'password123',
    ])->assertSessionHasErrors(['name', 'penanggung_jawab', 'telepon']);

    $this->assertDatabaseMissing('users', ['email' => 'tidak.valid@example.com']);
});

it('menghapus akun perusahaan saat distributor dihapus', function () {
    $this->actingAs(User::factory()->create());
    $distributor = Distributor::factory()->create();
    $account = User::factory()->distributor()->create(['distributor_id' => $distributor->id]);

    $this->delete(route('admin.distributors.destroy', $distributor))
        ->assertRedirect(route('admin.distributors.index'));

    $this->assertDatabaseMissing('users', ['id' => $account->id]);
    $this->assertDatabaseMissing('distributors', ['id' => $distributor->id]);
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

it('mencari distributor berdasarkan nama atau wilayah', function () {
    $admin = User::factory()->create();

    Distributor::factory()->create(['name' => 'PT Sumber Makmur', 'wilayah' => 'Padang']);
    Distributor::factory()->create(['name' => 'PT Lain Sekali', 'wilayah' => 'Padang Pariaman']);

    $this->actingAs($admin)
        ->get(route('admin.distributors.index', ['q' => 'Sumber']))
        ->assertOk()
        ->assertSee('PT Sumber Makmur')
        ->assertDontSee('PT Lain Sekali');

    $this->actingAs($admin)
        ->get(route('admin.distributors.index', ['q' => 'Pariaman']))
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
