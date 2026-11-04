<?php

use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

it('menampilkan form tambah pengguna untuk admin', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('admin.users.create'))
        ->assertOk()
        ->assertSee('Tambah Pengguna')
        ->assertSeeInOrder(['Nama Lengkap', 'Username', 'Email', 'Peran', 'Status Akun', 'Password', 'Konfirmasi Password'])
        ->assertSee('Simpan')
        ->assertSee('Batal');
});

it('menyimpan pengguna baru dengan password ter-hash', function () {
    $this->actingAs(User::factory()->create())
        ->post(route('admin.users.store'), [
            'name' => 'Kepala Dinas',
            'username' => 'kepala.dinas',
            'email' => 'kepala@mipta.test',
            'role' => Role::KepalaDinas->value,
            'is_active' => '1',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
        ])
        ->assertRedirect(route('admin.users.index'))
        ->assertSessionHas('status', 'Pengguna Kepala Dinas berhasil ditambahkan.');

    $user = User::where('username', 'kepala.dinas')->firstOrFail();

    expect($user->name)->toBe('Kepala Dinas')
        ->and($user->email)->toBe('kepala@mipta.test')
        ->and($user->role)->toBe(Role::KepalaDinas)
        ->and($user->is_active)->toBeTrue()
        ->and($user->password)->not->toBe('rahasia123')
        ->and(Hash::check('rahasia123', $user->password))->toBeTrue();
});

it('menyimpan pengguna baru sebagai nonaktif bila dipilih', function () {
    $this->actingAs(User::factory()->create())
        ->post(route('admin.users.store'), [
            'name' => 'Kontributor UPTD',
            'username' => 'kontributor.01',
            'email' => 'kontributor@mipta.test',
            'role' => Role::UptdPasar->value,
            'is_active' => '0',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
        ])
        ->assertRedirect(route('admin.users.index'));

    expect(User::where('username', 'kontributor.01')->firstOrFail()->is_active)->toBeFalse();
});

it('mewajibkan seluruh field pengguna', function () {
    $this->actingAs(User::factory()->create())
        ->post(route('admin.users.store'), [])
        ->assertSessionHasErrors(['name', 'username', 'email', 'role', 'is_active', 'password']);
});

it('menolak username dan email yang sudah dipakai', function () {
    User::factory()->create([
        'username' => 'admin',
        'email' => 'admin@mipta.test',
    ]);

    $this->actingAs(User::factory()->create())
        ->post(route('admin.users.store'), [
            'name' => 'Admin Baru',
            'username' => 'admin',
            'email' => 'admin@mipta.test',
            'role' => Role::Admin->value,
            'is_active' => '1',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
        ])
        ->assertSessionHasErrors([
            'username' => 'Username sudah digunakan.',
            'email' => 'Email sudah digunakan.',
        ]);
});

it('menolak password pendek atau konfirmasi yang berbeda', function () {
    $admin = User::factory()->create();

    $this->actingAs($admin)
        ->post(route('admin.users.store'), [
            'name' => 'Pengguna Baru',
            'username' => 'pengguna.baru',
            'email' => 'pengguna@mipta.test',
            'role' => Role::Admin->value,
            'is_active' => '1',
            'password' => 'pendek',
            'password_confirmation' => 'pendek',
        ])
        ->assertSessionHasErrors(['password' => 'Password minimal 8 karakter.']);

    $this->actingAs($admin)
        ->post(route('admin.users.store'), [
            'name' => 'Pengguna Baru',
            'username' => 'pengguna.baru',
            'email' => 'pengguna@mipta.test',
            'role' => Role::Admin->value,
            'is_active' => '1',
            'password' => 'rahasia123',
            'password_confirmation' => 'beda123456',
        ])
        ->assertSessionHasErrors(['password' => 'Konfirmasi password tidak sama.']);
});

it('menolak peran yang tidak dikenal', function () {
    $this->actingAs(User::factory()->create())
        ->post(route('admin.users.store'), [
            'name' => 'Pengguna Baru',
            'username' => 'pengguna.baru',
            'email' => 'pengguna@mipta.test',
            'role' => 'superadmin',
            'is_active' => '1',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
        ])
        ->assertSessionHasErrors(['role']);
});

it('menampilkan pesan sukses setelah pengguna ditambahkan', function () {
    $this->actingAs(User::factory()->create())
        ->withSession(['status' => 'Pengguna Baru berhasil ditambahkan.'])
        ->get(route('admin.users.index'))
        ->assertOk()
        ->assertSee('Pengguna Baru berhasil ditambahkan.');
});

it('membatasi form tambah pengguna hanya untuk admin', function () {
    $this->get(route('admin.users.create'))->assertRedirect(route('login'));

    $this->post(route('admin.users.store'), [])->assertRedirect(route('login'));

    $distributor = User::factory()->create(['role' => Role::Distributor]);

    $this->actingAs($distributor)
        ->get(route('admin.users.create'))
        ->assertForbidden();

    $this->actingAs($distributor)
        ->post(route('admin.users.store'), [])
        ->assertForbidden();
});
