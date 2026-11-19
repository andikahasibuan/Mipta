<?php

use App\Enums\Role;
use App\Models\Distributor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

it('menampilkan daftar distributor dari database', function () {
    $activeDistributor = Distributor::factory()->create([
        'name' => 'PT Contoh Distributor',
        'penanggung_jawab' => 'Budi Santoso',
        'telepon' => '081234567890',
    ]);
    User::factory()->distributor()->create([
        'distributor_id' => $activeDistributor->id,
        'username' => 'pt.contoh.distributor',
    ]);
    Distributor::factory()->create([
        'name' => 'PT Distributor Nonaktif',
        'is_active' => false,
    ]);

    $this->actingAs(User::factory()->create())
        ->get(route('admin.distributors.index'))
        ->assertOk()
        ->assertSeeInOrder(['NO', 'Distributor', 'Username', 'Penanggung Jawab', 'No HP', 'Status', 'Aksi'])
        ->assertSee('PT Contoh Distributor')
        ->assertSee('pt.contoh.distributor')
        ->assertSee('Budi Santoso')
        ->assertSee('081234567890')
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
        ->assertSee('Distributor')
        ->assertSee('Username')
        ->assertSee('Penanggung Jawab')
        ->assertSee('No HP Penanggung Jawab')
        ->assertSee('Alamat (opsional)')
        ->assertSee('Email (opsional)')
        ->assertSee('Password')
        ->assertSee('data-password-toggle="password"', false)
        ->assertDontSee('Wilayah')
        ->assertDontSee('Status aktif');
});

it('menyimpan distributor baru dengan username dan alamat dari form', function () {
    $this->actingAs(User::factory()->create());

    $this->post(route('admin.distributors.store'), [
        'name' => 'PT Profil Lengkap',
        'username' => 'pt.profil.lengkap',
        'penanggung_jawab' => 'Budi Santoso',
        'telepon' => '081234567890',
        'alamat_kantor' => 'Jln. Profil No. 1',
        'email' => 'profil@example.com',
        'password' => 'password123',
    ])->assertRedirect(route('admin.distributors.index'));

    $this->assertDatabaseHas('distributors', [
        'name' => 'PT Profil Lengkap',
        'penanggung_jawab' => 'Budi Santoso',
        'telepon' => '081234567890',
        'alamat_kantor' => 'Jln. Profil No. 1',
        'is_active' => true,
    ]);

    $this->assertDatabaseHas('users', [
        'name' => 'PT Profil Lengkap',
        'username' => 'pt.profil.lengkap',
        'email' => 'profil@example.com',
        'role' => Role::Distributor->value,
        'is_active' => true,
    ]);
});

it('menyimpan distributor tanpa alamat', function () {
    $this->actingAs(User::factory()->create());

    $this->post(route('admin.distributors.store'), [
        'name' => 'PT Tanpa Alamat',
        'username' => 'pt.tanpa.alamat',
        'penanggung_jawab' => 'Budi Santoso',
        'telepon' => '081234567890',
        'email' => 'tanpa.alamat@example.com',
        'password' => 'password123',
    ])->assertRedirect(route('admin.distributors.index'));

    $this->assertDatabaseHas('distributors', [
        'name' => 'PT Tanpa Alamat',
        'alamat_kantor' => null,
    ]);
});

it('menyimpan distributor tanpa email', function () {
    $this->actingAs(User::factory()->create());

    $this->post(route('admin.distributors.store'), [
        'name' => 'PT Tanpa Email',
        'username' => 'pt.tanpa.email',
        'penanggung_jawab' => 'Budi Santoso',
        'telepon' => '081234567890',
        'password' => 'password123',
    ])->assertRedirect(route('admin.distributors.index'));

    $this->assertDatabaseHas('users', [
        'username' => 'pt.tanpa.email',
        'email' => null,
        'role' => Role::Distributor->value,
    ]);
});

it('mengosongkan email distributor saat edit', function () {
    $this->actingAs(User::factory()->create());
    $distributor = Distributor::factory()->create(['name' => 'PT Email Opsional']);
    User::factory()->distributor()->create([
        'distributor_id' => $distributor->id,
        'username' => 'pt.email.opsional',
        'email' => 'email@distributor.test',
    ]);

    $this->put(route('admin.distributors.update', $distributor), [
        'name' => 'PT Email Opsional',
        'username' => 'pt.email.opsional',
        'penanggung_jawab' => 'Budi Santoso',
        'telepon' => '081234567890',
        'email' => '',
    ])->assertRedirect(route('admin.distributors.index'));

    expect($distributor->account->fresh()->email)->toBeNull();
});

it('menolak nama distributor yang sudah digunakan', function () {
    $this->actingAs(User::factory()->create());

    $this->post(route('admin.distributors.store'), [
        'name' => 'PT Nama Sama',
        'username' => 'pt.nama.sama',
        'penanggung_jawab' => 'Budi Santoso',
        'telepon' => '081234567890',
        'email' => 'nama.sama@example.com',
        'password' => 'password123',
    ])->assertRedirect(route('admin.distributors.index'));

    $this->post(route('admin.distributors.store'), [
        'name' => 'PT Nama Sama',
        'username' => 'pt.nama.sama.dua',
        'penanggung_jawab' => 'Budi Santoso',
        'telepon' => '081234567890',
        'email' => 'nama.sama.dua@example.com',
        'password' => 'password123',
    ])->assertSessionHasErrors('name');

    $this->assertDatabaseCount('distributors', 1);
});

it('menyimpan username distributor dalam huruf kecil', function () {
    $this->actingAs(User::factory()->create());

    $this->post(route('admin.distributors.store'), [
        'name' => 'PT Username Kapital',
        'username' => 'Distributor.Kapital',
        'penanggung_jawab' => 'Budi Santoso',
        'telepon' => '081234567890',
        'email' => 'username.kapital@example.com',
        'password' => 'password123',
    ])->assertRedirect(route('admin.distributors.index'));

    $this->assertDatabaseHas('users', ['username' => 'distributor.kapital']);
});

it('menolak username distributor yang sudah digunakan', function () {
    $this->actingAs(User::factory()->create());
    User::factory()->create(['username' => 'username.terpakai']);

    $this->post(route('admin.distributors.store'), [
        'name' => 'PT Username Duplikat',
        'username' => 'username.terpakai',
        'penanggung_jawab' => 'Budi Santoso',
        'telepon' => '081234567890',
        'email' => 'username.duplikat@example.com',
        'password' => 'password123',
    ])->assertSessionHasErrors('username');

    $this->assertDatabaseMissing('distributors', ['name' => 'PT Username Duplikat']);
});

it('memperbarui data distributor beserta username dan alamat akun', function () {
    $this->actingAs(User::factory()->create());
    $distributor = Distributor::factory()->create([
        'name' => 'PT Profil Admin',
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
        ->assertSee('pt-profil-admin')
        ->assertSee('Alamat Lama')
        ->assertSee('data-password-toggle="password"', false)
        ->assertDontSee('Status aktif');

    $this->put(route('admin.distributors.update', $distributor), [
        'name' => 'PT Nama Berubah',
        'username' => 'pt.admin.baru',
        'penanggung_jawab' => 'Pengelola Baru',
        'telepon' => '0812345678',
        'alamat_kantor' => 'Alamat Baru',
        'email' => 'baru@example.com',
    ])->assertRedirect(route('admin.distributors.index'));

    $distributor->refresh();
    $account->refresh();

    expect($distributor->name)->toBe('PT Nama Berubah')
        ->and($distributor->penanggung_jawab)->toBe('Pengelola Baru')
        ->and($distributor->telepon)->toBe('0812345678')
        ->and($distributor->alamat_kantor)->toBe('Alamat Baru')
        ->and($distributor->is_active)->toBeFalse()
        ->and($account->name)->toBe('PT Nama Berubah')
        ->and($account->username)->toBe('pt.admin.baru')
        ->and($account->email)->toBe('baru@example.com');
});

it('menolak perubahan nama distributor menjadi nama yang sudah digunakan', function () {
    $this->actingAs(User::factory()->create());
    Distributor::factory()->create(['name' => 'PT Sudah Ada']);
    $distributor = Distributor::factory()->create(['name' => 'PT Akan Diubah']);
    User::factory()->distributor()->create([
        'distributor_id' => $distributor->id,
        'username' => 'pt.akan.diubah',
    ]);

    $this->put(route('admin.distributors.update', $distributor), [
        'name' => 'PT Sudah Ada',
        'username' => 'pt.akan.diubah',
        'penanggung_jawab' => 'Pengelola',
        'telepon' => '081234567890',
        'email' => 'akan.diubah@example.com',
    ])->assertSessionHasErrors('name');

    expect($distributor->fresh()->name)->toBe('PT Akan Diubah');
});

it('menampilkan username, penanggung jawab, dan no hp pada daftar distributor', function () {
    $distributor = Distributor::factory()->create([
        'name' => 'PT Akun',
        'penanggung_jawab' => 'Pengelola Akun',
        'telepon' => '081298765432',
    ]);
    User::factory()->distributor()->create([
        'distributor_id' => $distributor->id,
        'username' => 'pt.akun',
    ]);

    $this->actingAs(User::factory()->create())
        ->get(route('admin.distributors.index'))
        ->assertOk()
        ->assertSee('pt.akun')
        ->assertSee('Pengelola Akun')
        ->assertSee('081298765432');
});

it('menampilkan tanda minus saat akun, penanggung jawab, dan no hp distributor kosong', function () {
    Distributor::factory()->create([
        'name' => 'PT Tanpa Akun',
        'penanggung_jawab' => null,
        'telepon' => null,
    ]);

    $this->actingAs(User::factory()->create())
        ->get(route('admin.distributors.index'))
        ->assertOk()
        ->assertSeeInOrder(['Distributor', 'Username', 'Penanggung Jawab', 'No HP'])
        ->assertSeeInOrder(['Username', '—'])
        ->assertSeeInOrder(['Penanggung Jawab', '-'])
        ->assertSeeInOrder(['No HP', '-']);
});

it('membuat akun untuk distributor lama yang belum memiliki akun', function () {
    $this->actingAs(User::factory()->create());
    $distributor = Distributor::factory()->create(['name' => 'PT Lama']);

    $this->put(route('admin.distributors.update', $distributor), [
        'name' => 'PT Lama',
        'username' => 'pt.lama',
        'penanggung_jawab' => 'Pengelola Lama',
        'telepon' => '081234567890',
        'email' => 'pt.lama@example.com',
        'password' => 'password123',
    ])->assertRedirect(route('admin.distributors.index'));

    $this->assertDatabaseHas('users', [
        'name' => 'PT Lama',
        'email' => 'pt.lama@example.com',
        'username' => 'pt.lama',
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
        'username' => 'pt.duplikat',
        'penanggung_jawab' => 'Budi Santoso',
        'telepon' => '081234567890',
        'email' => 'dipakai@example.com',
        'password' => 'password123',
    ])->assertSessionHasErrors(['email']);

    $this->assertDatabaseMissing('distributors', ['name' => 'PT Duplikat']);
});

it('menyinkronkan nama, username, dan email akun tanpa mengubah status saat distributor diperbarui', function () {
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
        'username' => 'baru',
        'penanggung_jawab' => 'Pengelola Baru',
        'telepon' => '081234567890',
        'email' => 'baru@example.com',
    ])->assertRedirect(route('admin.distributors.index'));

    $account->refresh();

    expect($account->name)->toBe('PT Baru')
        ->and($account->email)->toBe('baru@example.com')
        ->and($account->username)->toBe('baru')
        ->and($account->is_active)->toBeFalse()
        ->and(Hash::check('password-lama', $account->password))->toBeTrue();
});

it('menolak nama perusahaan, nama penanggung jawab, dan no handphone yang mengandung karakter tidak valid', function () {
    $this->actingAs(User::factory()->create());

    $this->post(route('admin.distributors.store'), [
        'name' => 'PT !@#$%',
        'username' => 'pt.tidak.valid',
        'penanggung_jawab' => '12345',
        'telepon' => '0812-3456-7890',
        'email' => 'tidak.valid@example.com',
        'password' => 'password123',
    ])->assertSessionHasErrors(['name', 'penanggung_jawab', 'telepon']);

    $this->assertDatabaseMissing('users', ['email' => 'tidak.valid@example.com']);
});

it('membatasi no hp distributor maksimal 13 digit', function () {
    $this->actingAs(User::factory()->create());

    $this->post(route('admin.distributors.store'), [
        'name' => 'PT Telepon Panjang',
        'username' => 'pt.telepon.panjang',
        'penanggung_jawab' => 'Budi Santoso',
        'telepon' => '12345678901234',
        'email' => 'telepon.panjang@example.com',
        'password' => 'password123',
    ])->assertSessionHasErrors('telepon');

    $this->assertDatabaseMissing('distributors', ['name' => 'PT Telepon Panjang']);
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

it('menampilkan lima tab pengguna tanpa tab data master', function () {
    $response = $this->actingAs(User::factory()->create())
        ->get(route('admin.distributors.index'));

    $response->assertOk()->assertDontSee('Ringkasan');

    preg_match('/<nav[^>]*aria-label="Navigasi Pengguna".*?<\/nav>/s', $response->getContent(), $matches);
    $tabs = $matches[0] ?? '';

    foreach (['Admin', 'Kepala Dinas', 'Distributor', 'Kontributor UPTD', 'UPTD'] as $label) {
        expect($tabs)->toContain($label);
    }

    expect($tabs)->toContain(route('admin.distributors.index'));
    expect($tabs)->toContain(route('admin.users.uptd-contributors.index'));

    expect($tabs)->not->toContain(route('admin.markets.index'));
    expect($tabs)->not->toContain(route('admin.recipient-stores.index'));
});

it('menyorot tab aktif pada navigasi pengguna', function () {
    $response = $this->actingAs(User::factory()->create())
        ->get(route('admin.distributors.index'));

    preg_match('/<nav[^>]*aria-label="Navigasi Pengguna".*?<\/nav>/s', $response->getContent(), $matches);
    $tabs = $matches[0] ?? '';

    expect(substr_count($tabs, 'aria-current="page"'))->toBe(1)
        ->and($tabs)->toMatch('/aria-current="page"[^>]*>\s*Distributor\s*<\/a>/s');
});

it('mencari distributor berdasarkan nama', function () {
    $admin = User::factory()->create();

    Distributor::factory()->create(['name' => 'PT Sumber Makmur']);
    Distributor::factory()->create(['name' => 'PT Lain Sekali']);

    $this->actingAs($admin)
        ->get(route('admin.distributors.index', ['q' => 'Sumber']))
        ->assertOk()
        ->assertSee('PT Sumber Makmur')
        ->assertDontSee('PT Lain Sekali');
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
