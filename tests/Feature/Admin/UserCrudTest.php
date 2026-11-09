<?php

use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('menolak pembuatan akun distributor melalui halaman pengguna', function () {
    $this->actingAs(User::factory()->create());

    $this->post(route('admin.users.store'), [
        'name' => 'PT Contoh',
        'username' => 'pt.contoh',
        'email' => 'pt.contoh@example.com',
        'password' => 'password123',
        'role' => Role::Distributor->value,
        'is_active' => '1',
    ])->assertSessionHasErrors('role');

    $this->assertDatabaseMissing('users', ['email' => 'pt.contoh@example.com']);
});

it('tidak menampilkan role distributor pada form pengguna', function () {
    $this->actingAs(User::factory()->create());

    $this->get(route('admin.users.create'))
        ->assertOk()
        ->assertDontSee('value="'.Role::Distributor->value.'"', false);
});

it('menampilkan halaman edit pengguna', function () {
    $this->actingAs(User::factory()->create());
    $user = User::factory()->create(['name' => 'Rina Marlina']);

    $this->get(route('admin.users.edit', $user))
        ->assertOk()
        ->assertSee('Rina Marlina');
});

it('memperbarui pengguna dan menyimpannya ke database', function () {
    $this->actingAs(User::factory()->create());
    $user = User::factory()->create(['name' => 'Nama Lama']);

    $this->put(route('admin.users.update', $user), [
        'name' => 'Nama Baru',
        'username' => $user->username,
        'email' => $user->email,
        'role' => $user->role->value,
    ])->assertRedirect(route('admin.users.index'));

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'name' => 'Nama Baru',
    ]);
});

it('menolak nama dan username pengguna yang mengandung karakter khusus', function () {
    $this->actingAs(User::factory()->create());

    $this->post(route('admin.users.store'), [
        'name' => 'Andi !@#$%',
        'username' => 'andi!#$',
        'email' => 'andi.khusus@example.com',
        'password' => 'password123',
        'role' => Role::Admin->value,
    ])->assertSessionHasErrors(['name', 'username']);

    $this->assertDatabaseMissing('users', ['email' => 'andi.khusus@example.com']);
});

it('menolak nama dan username kontributor UPTD yang mengandung karakter khusus', function () {
    $this->actingAs(User::factory()->create());

    $this->post(route('admin.uptd-contributors.store'), [
        'name' => 'Rina !@#$%',
        'username' => 'rina!#$',
        'email' => 'rina.khusus@example.com',
        'password' => 'password123',
    ])->assertSessionHasErrors(['name', 'username']);

    $this->assertDatabaseMissing('users', ['email' => 'rina.khusus@example.com']);
});

it('menghapus pengguna dari database', function () {
    $this->actingAs(User::factory()->create());
    $user = User::factory()->create();

    $this->delete(route('admin.users.destroy', $user))
        ->assertRedirect(route('admin.users.index'));

    $this->assertDatabaseMissing('users', ['id' => $user->id]);
});

it('menolak menghapus akun yang sedang digunakan untuk login', function () {
    $admin = User::factory()->create();
    $this->actingAs($admin);

    $this->from(route('admin.users.index'))
        ->delete(route('admin.users.destroy', $admin))
        ->assertRedirect(route('admin.users.index'))
        ->assertSessionHasErrors('delete');

    $this->assertDatabaseHas('users', ['id' => $admin->id]);
});

it('menyimpan kontributor UPTD dengan peran UPTD Pasar', function () {
    $this->actingAs(User::factory()->create());

    $this->post(route('admin.uptd-contributors.store'), [
        'name' => 'Dewi Anggraini',
        'username' => 'dewi.anggraini',
        'email' => 'dewi@example.com',
        'password' => 'password123',
        'is_active' => '1',
    ])->assertRedirect(route('admin.uptd-contributors.index'));

    $this->assertDatabaseHas('users', [
        'email' => 'dewi@example.com',
        'role' => Role::UptdPasar->value,
    ]);
});

it('memperbarui kontributor UPTD dan menyimpannya ke database', function () {
    $this->actingAs(User::factory()->create());
    $contributor = User::factory()->create([
        'role' => Role::UptdPasar,
        'name' => 'Nama Lama',
    ]);

    $this->put(route('admin.uptd-contributors.update', $contributor), [
        'name' => 'Nama Baru',
        'username' => $contributor->username,
        'email' => $contributor->email,
    ])->assertRedirect(route('admin.uptd-contributors.index'));

    $this->assertDatabaseHas('users', [
        'id' => $contributor->id,
        'name' => 'Nama Baru',
        'role' => Role::UptdPasar->value,
    ]);
});

it('menghapus kontributor UPTD dari database', function () {
    $this->actingAs(User::factory()->create());
    $contributor = User::factory()->create(['role' => Role::UptdPasar]);

    $this->delete(route('admin.uptd-contributors.destroy', $contributor))
        ->assertRedirect(route('admin.uptd-contributors.index'));

    $this->assertDatabaseMissing('users', ['id' => $contributor->id]);
});

it('mencegah admin mengubah peran atau menonaktifkan akunnya sendiri', function () {
    $admin = User::factory()->create(['role' => Role::Admin]);

    $this->actingAs($admin)
        ->put(route('admin.users.update', $admin), [
            'name' => $admin->name,
            'username' => $admin->username,
            'email' => $admin->email,
            'role' => Role::KepalaDinas->value,
            'is_active' => '1',
        ])
        ->assertSessionHasErrors('role');

    $this->put(route('admin.users.update', $admin), [
        'name' => $admin->name,
        'username' => $admin->username,
        'email' => $admin->email,
        'role' => Role::Admin->value,
        'is_active' => '0',
    ])->assertSessionHasErrors('is_active');

    expect($admin->fresh()->role)->toBe(Role::Admin)
        ->and($admin->fresh()->is_active)->toBeTrue();
});
