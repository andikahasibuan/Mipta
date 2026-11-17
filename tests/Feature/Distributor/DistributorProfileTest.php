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
        'penanggung_jawab' => 'Pengelola Distributor',
        'telepon' => '0751000000',
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
        ->assertSee('Profil')
        ->assertSee('Informasi Akun')
        ->assertSee('Data identitas yang terhubung dengan akun anda')
        ->assertSeeInOrder(['distributor', $distributor->name])
        ->assertSeeInOrder(['Username', 'andalas.niaga'])
        ->assertSeeInOrder(['penanggung jawab', 'Pengelola Distributor'])
        ->assertSeeInOrder(['Nomor Telepon', '0751000000'])
        ->assertSeeInOrder(['Email', $user->email])
        ->assertSeeInOrder(['Alamat kantor', 'Kota Padang, Sumatera Barat'])
        ->assertSeeInOrder(['penanggung jawab', 'Nomor Telepon', 'Email', 'Alamat kantor'])
        ->assertSee('Distributor MinyaKita')
        ->assertDontSee('Aktif')
        ->assertSee('Keamanan akun')
        ->assertSee('Kata sandi')
        ->assertSee('Ubah')
        ->assertSee('aria-label="Edit Email"', false)
        ->assertSee('aria-label="Edit penanggung jawab"', false)
        ->assertSee('aria-label="Edit Nomor Telepon"', false)
        ->assertSee('aria-label="Edit Alamat kantor"', false)
        ->assertSee('id="password-modal"', false)
        ->assertSee('action="'.route('distributor.profile.password.update').'"', false)
        ->assertDontSee('</div>z', false)
        ->assertDontSee('Ubah profil');
});

it('menampilkan penanda alamat kantor belum diisi pada profil', function () {
    [$distributor, $user] = profileDistributorAccount();
    $distributor->update(['alamat_kantor' => null]);

    $this->actingAs($user)
        ->get(route('distributor.profile.edit'))
        ->assertOk()
        ->assertSee('Belum diisi')
        ->assertSeeInOrder(['Alamat kantor', 'Belum diisi']);
});

it('memperbarui email distributor melalui form profil', function () {
    [, $user] = profileDistributorAccount();

    $this->actingAs($user)
        ->from(route('distributor.profile.edit'))
        ->put(route('distributor.profile.update'), [
            'email' => 'email.baru@example.com',
        ])
        ->assertRedirect(route('distributor.profile.edit'));

    expect($user->fresh()->email)->toBe('email.baru@example.com');
});

it('mengosongkan email dan menampilkan penanda belum diisi', function () {
    [, $user] = profileDistributorAccount();

    $this->actingAs($user)
        ->from(route('distributor.profile.edit'))
        ->put(route('distributor.profile.update'), [
            'email' => '',
        ])
        ->assertRedirect(route('distributor.profile.edit'));

    expect($user->fresh()->email)->toBeNull();

    $this->get(route('distributor.profile.edit'))
        ->assertOk()
        ->assertSeeInOrder(['Email', 'Belum diisi']);
});

it('menolak email distributor yang sudah digunakan akun lain', function () {
    [, $user] = profileDistributorAccount();
    User::factory()->create(['email' => 'dipakai@example.com']);

    $this->actingAs($user)
        ->from(route('distributor.profile.edit'))
        ->put(route('distributor.profile.update'), [
            'email' => 'dipakai@example.com',
        ])
        ->assertSessionHasErrors('email');
});

it('memperbarui informasi kontak distributor melalui form inline', function () {
    [$distributor, $user] = profileDistributorAccount();

    $this->actingAs($user)
        ->from(route('distributor.profile.edit'))
        ->put(route('distributor.profile.update'), [
            'penanggung_jawab' => 'budi santoso',
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
            'alamat_kantor' => 'jln. sudirman no. 10, padang',
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

it('membatasi nomor telepon maksimal 13 digit', function () {
    [$distributor, $user] = profileDistributorAccount();

    $this->actingAs($user)
        ->from(route('distributor.profile.edit'))
        ->put(route('distributor.profile.update'), [
            'telepon' => '12345678901234',
        ])
        ->assertSessionHasErrors('telepon');

    $this->from(route('distributor.profile.edit'))
        ->put(route('distributor.profile.update'), [
            'telepon' => '1234567890123',
        ])
        ->assertRedirect(route('distributor.profile.edit'));

    expect($distributor->fresh()->telepon)->toBe('1234567890123');
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
