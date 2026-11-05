<?php

use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('menyimpan pengguna baru ke database', function () {
    $this->actingAs(User::factory()->create());

    $this->post(route('admin.users.store'), [
        'name' => 'Andi Pratama',
        'username' => 'andi.pratama',
        'email' => 'andi@example.com',
        'password' => 'password123',
        'role' => Role::Distributor->value,
        'is_active' => '1',
    ])->assertRedirect(route('admin.users.index'));

    $this->assertDatabaseHas('users', [
        'email' => 'andi@example.com',
        'role' => Role::Distributor->value,
        'is_active' => true,
    ]);
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
