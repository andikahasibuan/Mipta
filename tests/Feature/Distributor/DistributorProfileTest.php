<?php

use App\Enums\Role;
use App\Models\Distributor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

/**
 * Buat distributor beserta akun login perusahaannya.
 *
 * @return array{0: Distributor, 1: User}
 */
function profileDistributorAccount(string $name = 'PT Andalas Niaga Utama', string $username = 'andalas.niaga'): array
{
    $distributor = Distributor::factory()->create([
        'name' => $name,
        'wilayah' => 'Padang',
        'penanggung_jawab' => 'Pengelola Distributor',
        'telepon' => '0751-000000',
        'alamat_kantor' => 'Kota Padang, Sumatera Barat',
        'is_active' => true,
    ]);

    $user = User::factory()->create([
        'name' => $name,
        'username' => $username,
        'role' => Role::Distributor,
        'distributor_id' => $distributor->id,
        'is_active' => true,
        'password' => 'password-lama',
    ]);

    return [$distributor, $user];
}

it('menampilkan informasi profil distributor sesuai desain', function () {
    [$distributor, $user] = profileDistributorAccount();

    $this->actingAs($user)
        ->get(route('distributor.profile.edit'))
        ->assertOk()
        ->assertSee('Manajemen Profil')
        ->assertSee('Kelola informasi akun dan profil Distributor')
        ->assertSee('Informasi Distributor')
        ->assertSee('Data identitas yang terhubung dengan akun anda')
        ->assertSeeInOrder(['Nama akun', 'andalas.niaga'])
        ->assertSeeInOrder(['Nama distributor', $distributor->name])
        ->assertSeeInOrder(['Email perusahaan', $user->email])
        ->assertSeeInOrder(['Nama penanggung jawab', 'Pengelola Distributor'])
        ->assertSeeInOrder(['Nomor Telepon', '0751-000000'])
        ->assertSeeInOrder(['Alamat kantor', 'Kota Padang, Sumatera Barat'])
        ->assertSeeInOrder(['Status akun', 'Aktif'])
        ->assertSee('Keamanan akun')
        ->assertSee('Kata sandi')
        ->assertSee('Ubah')
        ->assertSee('Perubahan nama distributor, nama akun, dan email hanya dapat dilakukan melalui Admin MIPTA.')
        ->assertSee('aria-label="Edit Nama penanggung jawab"', false)
        ->assertSee('aria-label="Edit Nomor Telepon"', false)
        ->assertSee('aria-label="Edit Alamat kantor"', false)
        ->assertDontSee('aria-label="Edit Email perusahaan"', false)
        ->assertSee('id="password-modal"', false)
        ->assertSee('action="'.route('distributor.profile.password.update').'"', false)
        ->assertDontSee('Ubah profil');
});

it('menampilkan penanda alamat kantor belum diisi pada profil', function () {
    [$distributor, $user] = profileDistributorAccount();
    $distributor->update(['alamat_kantor' => null]);

    $this->actingAs($user)
        ->get(route('distributor.profile.edit'))
        ->assertOk()
        ->assertSee('Belum diisi')
        ->assertSee('title="Belum diisi"', false)
        ->assertSeeInOrder(['Alamat kantor', 'Belum diisi']);
});

it('tidak mengizinkan distributor mengubah email melalui permintaan profil', function () {
    [$distributor, $user] = profileDistributorAccount();
    $emailBefore = $user->email;

    $this->actingAs($user)
        ->from(route('distributor.profile.edit'))
        ->put(route('distributor.profile.update'), [
            'penanggung_jawab' => 'Pengelola Distributor',
            'email' => 'email.baru@example.com',
        ])
        ->assertRedirect(route('distributor.profile.edit'));

    expect($user->fresh()->email)->toBe($emailBefore);
});

it('memperbarui informasi kontak distributor melalui form inline', function () {
    [$distributor, $user] = profileDistributorAccount();

    $this->actingAs($user)
        ->from(route('distributor.profile.edit'))
        ->put(route('distributor.profile.update'), [
            'penanggung_jawab' => 'Budi Santoso',
        ])
        ->assertRedirect(route('distributor.profile.edit'))
        ->assertSessionHas('success', 'Informasi distributor berhasil diperbarui.');

    $this->from(route('distributor.profile.edit'))
        ->put(route('distributor.profile.update'), [
            'telepon' => '081234567890',
        ])
        ->assertRedirect(route('distributor.profile.edit'));

    $this->from(route('distributor.profile.edit'))
        ->put(route('distributor.profile.update'), [
            'alamat_kantor' => 'Jln. Sudirman No. 10, Padang',
        ])
        ->assertRedirect(route('distributor.profile.edit'));

    $distributor->refresh();

    expect($distributor->penanggung_jawab)->toBe('Budi Santoso')
        ->and($distributor->telepon)->toBe('081234567890')
        ->and($distributor->alamat_kantor)->toBe('Jln. Sudirman No. 10, Padang');
});

it('tidak mengubah data distributor lain saat memperbarui profil', function () {
    [$distributor, $user] = profileDistributorAccount();
    [$otherDistributor] = profileDistributorAccount('PT Distributor Lain', 'distributor.lain');

    $this->actingAs($user)
        ->from(route('distributor.profile.edit'))
        ->put(route('distributor.profile.update'), [
            'penanggung_jawab' => 'Nama Baru',
        ])
        ->assertRedirect(route('distributor.profile.edit'));

    expect($distributor->fresh()->penanggung_jawab)->toBe('Nama Baru')
        ->and($otherDistributor->fresh()->penanggung_jawab)->toBe('Pengelola Distributor');
});

it('memvalidasi panjang informasi kontak distributor', function () {
    [, $user] = profileDistributorAccount();

    $this->actingAs($user)
        ->from(route('distributor.profile.edit'))
        ->put(route('distributor.profile.update'), [
            'penanggung_jawab' => str_repeat('a', 256),
            'telepon' => str_repeat('1', 31),
            'alamat_kantor' => str_repeat('b', 256),
        ])
        ->assertSessionHasErrors(['penanggung_jawab', 'telepon', 'alamat_kantor']);
});

it('menolak informasi kontak distributor yang mengandung karakter khusus', function () {
    [, $user] = profileDistributorAccount();

    $this->actingAs($user)
        ->from(route('distributor.profile.edit'))
        ->put(route('distributor.profile.update'), [
            'penanggung_jawab' => 'Budi !@#$%',
            'telepon' => '0812-3456-7890',
            'alamat_kantor' => 'Jln. Merdeka & Co',
        ])
        ->assertSessionHasErrors(['penanggung_jawab', 'telepon', 'alamat_kantor']);
});

it('mengganti kata sandi dengan kata sandi lama yang benar', function () {
    [, $user] = profileDistributorAccount();

    $this->actingAs($user)
        ->from(route('distributor.profile.edit'))
        ->put(route('distributor.profile.password.update'), [
            'current_password' => 'password-lama',
            'password' => 'password-baru',
            'password_confirmation' => 'password-baru',
        ])
        ->assertRedirect(route('distributor.profile.edit'))
        ->assertSessionHas('success', 'Kata sandi berhasil diperbarui.');

    expect(Hash::check('password-baru', $user->fresh()->password))->toBeTrue()
        ->and(Hash::check('password-lama', $user->fresh()->password))->toBeFalse();
});

it('menolak kata sandi saat ini yang salah', function () {
    [, $user] = profileDistributorAccount();

    $this->actingAs($user)
        ->from(route('distributor.profile.edit'))
        ->put(route('distributor.profile.password.update'), [
            'current_password' => 'kata-sandi-salah',
            'password' => 'password-baru',
            'password_confirmation' => 'password-baru',
        ])
        ->assertSessionHasErrors('current_password');

    expect(Hash::check('password-lama', $user->fresh()->password))->toBeTrue();
});

it('menolak konfirmasi kata sandi yang tidak cocok', function () {
    [, $user] = profileDistributorAccount();

    $this->actingAs($user)
        ->from(route('distributor.profile.edit'))
        ->put(route('distributor.profile.password.update'), [
            'current_password' => 'password-lama',
            'password' => 'password-baru',
            'password_confirmation' => 'password-lain',
        ])
        ->assertSessionHasErrors('password');

    expect(Hash::check('password-lama', $user->fresh()->password))->toBeTrue();
});

it('menolak kata sandi baru yang sama dengan kata sandi lama', function () {
    [, $user] = profileDistributorAccount();

    $this->actingAs($user)
        ->from(route('distributor.profile.edit'))
        ->put(route('distributor.profile.password.update'), [
            'current_password' => 'password-lama',
            'password' => 'password-lama',
            'password_confirmation' => 'password-lama',
        ])
        ->assertSessionHasErrors('password');
});

it('membatasi pembaruan profil hanya untuk distributor', function () {
    [$distributor, $user] = profileDistributorAccount();

    $this->put(route('distributor.profile.update'), [
        'penanggung_jawab' => 'Tamu',
    ])->assertRedirect(route('login'));

    $this->actingAs(User::factory()->create())
        ->put(route('distributor.profile.update'), [
            'penanggung_jawab' => 'Admin',
        ])
        ->assertForbidden();

    expect($distributor->fresh()->penanggung_jawab)->toBe('Pengelola Distributor');

    $this->actingAs($user)
        ->from(route('distributor.profile.edit'))
        ->put(route('distributor.profile.password.update'), [
            'current_password' => 'password-lama',
            'password' => 'password-baru',
            'password_confirmation' => 'password-baru',
        ])
        ->assertRedirect(route('distributor.profile.edit'));
});
