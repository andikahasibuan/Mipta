<?php

use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

it('menampilkan form ubah pengguna dengan data terisi', function () {
    $admin = User::factory()->create();
    $user = User::factory()->create([
        'name' => 'Kepala Dinas',
        'username' => 'kepala.dinas',
        'email' => 'kepala@mipta.test',
        'role' => Role::KepalaDinas,
    ]);

    $this->actingAs($admin)
        ->get(route('admin.users.edit', $user))
        ->assertOk()
        ->assertSee('Ubah Pengguna')
        ->assertSee('value="Kepala Dinas"', false)
        ->assertSee('value="kepala.dinas"', false)
        ->assertSee('value="kepala@mipta.test"', false)
        ->assertSee('Simpan Perubahan');
});

it('memperbarui data pengguna tanpa mengubah password lama', function () {
    $admin = User::factory()->create();
    $user = User::factory()->create([
        'username' => 'pengguna.lama',
        'email' => 'lama@mipta.test',
        'password' => 'passwordlama',
    ]);

    $this->actingAs($admin)
        ->put(route('admin.users.update', $user), [
            'name' => 'Nama Baru',
            'username' => 'pengguna.baru',
            'email' => 'baru@mipta.test',
            'role' => Role::KepalaDinas->value,
            'is_active' => '1',
            'password' => '',
            'password_confirmation' => '',
        ])
        ->assertRedirect(route('admin.users.index'))
        ->assertSessionHas('status', 'Pengguna Nama Baru berhasil diperbarui.');

    $user->refresh();

    expect($user->name)->toBe('Nama Baru')
        ->and($user->username)->toBe('pengguna.baru')
        ->and($user->email)->toBe('baru@mipta.test')
        ->and($user->role)->toBe(Role::KepalaDinas)
        ->and(Hash::check('passwordlama', $user->password))->toBeTrue();
});

it('mengganti password pengguna bila diisi', function () {
    $admin = User::factory()->create();
    $user = User::factory()->create(['password' => 'passwordlama']);

    $this->actingAs($admin)
        ->put(route('admin.users.update', $user), [
            'name' => $user->name,
            'username' => $user->username,
            'email' => $user->email,
            'role' => $user->role->value,
            'is_active' => '1',
            'password' => 'passwordbaru',
            'password_confirmation' => 'passwordbaru',
        ])
        ->assertRedirect(route('admin.users.index'));

    expect(Hash::check('passwordbaru', $user->fresh()->password))->toBeTrue();
});

it('mengizinkan username dan email sendiri saat diperbarui', function () {
    $admin = User::factory()->create();
    $user = User::factory()->create([
        'username' => 'tetap.sama',
        'email' => 'tetap@mipta.test',
    ]);

    $this->actingAs($admin)
        ->put(route('admin.users.update', $user), [
            'name' => 'Nama Tetap',
            'username' => 'tetap.sama',
            'email' => 'tetap@mipta.test',
            'role' => $user->role->value,
            'is_active' => '1',
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('admin.users.index'));
});

it('menolak username atau email milik pengguna lain saat diperbarui', function () {
    $admin = User::factory()->create();
    User::factory()->create([
        'username' => 'sudah.dipakai',
        'email' => 'dipakai@mipta.test',
    ]);
    $user = User::factory()->create();

    $this->actingAs($admin)
        ->put(route('admin.users.update', $user), [
            'name' => 'Nama Baru',
            'username' => 'sudah.dipakai',
            'email' => 'dipakai@mipta.test',
            'role' => $user->role->value,
            'is_active' => '1',
        ])
        ->assertSessionHasErrors([
            'username' => 'Username sudah digunakan.',
            'email' => 'Email sudah digunakan.',
        ]);
});

it('tidak mengizinkan admin menonaktifkan akun sendiri', function () {
    $admin = User::factory()->create(['name' => 'Admin MIPTA']);

    $this->actingAs($admin)
        ->put(route('admin.users.update', $admin), [
            'name' => 'Admin MIPTA',
            'username' => $admin->username,
            'email' => $admin->email,
            'role' => $admin->role->value,
            'is_active' => '0',
        ])
        ->assertSessionHasErrors(['is_active' => 'Anda tidak dapat menonaktifkan akun sendiri.']);

    expect($admin->fresh()->is_active)->toBeTrue();
});

it('mengubah status akun pengguna lain lewat aksi cepat', function () {
    $admin = User::factory()->create();
    $user = User::factory()->create(['is_active' => true]);

    $this->actingAs($admin)
        ->patch(route('admin.users.status', $user))
        ->assertRedirect()
        ->assertSessionHas('status', 'Akun '.$user->name.' dinonaktifkan.');

    expect($user->fresh()->is_active)->toBeFalse();

    $this->actingAs($admin)
        ->patch(route('admin.users.status', $user))
        ->assertSessionHas('status', 'Akun '.$user->name.' diaktifkan.');

    expect($user->fresh()->is_active)->toBeTrue();
});

it('tidak mengizinkan admin mengubah status akun sendiri', function () {
    $admin = User::factory()->create();

    $this->actingAs($admin)
        ->patch(route('admin.users.status', $admin))
        ->assertSessionHasErrors('status');

    expect($admin->fresh()->is_active)->toBeTrue();
});

it('menampilkan tombol edit dan aksi status pada tabel pengguna', function () {
    $admin = User::factory()->create(['name' => 'Admin MIPTA']);
    $user = User::factory()->create(['name' => 'Pengguna Lain']);

    $response = $this->actingAs($admin)->get(route('admin.users.index'));

    $response->assertOk()
        ->assertSee('href="'.route('admin.users.edit', $admin).'"', false)
        ->assertSee('href="'.route('admin.users.edit', $user).'"', false)
        ->assertSee('Nonaktifkan')
        ->assertDontSee('Aktifkan');
});

it('membatasi halaman ubah pengguna hanya untuk admin', function () {
    $user = User::factory()->create();

    $this->get(route('admin.users.edit', $user))->assertRedirect(route('login'));

    $this->actingAs(User::factory()->create(['role' => Role::Distributor]))
        ->get(route('admin.users.edit', $user))
        ->assertForbidden();
});
