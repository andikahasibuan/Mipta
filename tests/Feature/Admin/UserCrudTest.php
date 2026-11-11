<?php

use App\Enums\Role;
use App\Models\Market;
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
    $market = Market::factory()->create();

    $this->post(route('admin.uptd-contributors.store'), [
        'name' => 'Rina !@#$%',
        'username' => 'rina!#$',
        'password' => 'password123',
        'market_id' => $market->id,
    ])->assertSessionHasErrors(['name', 'username']);

    $this->assertDatabaseMissing('users', ['username' => 'rina!#$']);
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
    $market = Market::factory()->create();

    $this->post(route('admin.uptd-contributors.store'), [
        'name' => 'Dewi Anggraini',
        'username' => 'dewi.anggraini',
        'password' => 'password123',
        'market_id' => $market->id,
        'is_active' => '1',
    ])->assertRedirect(route('admin.uptd-contributors.index'));

    $this->assertDatabaseHas('users', [
        'username' => 'dewi.anggraini',
        'email' => null,
        'role' => Role::UptdPasar->value,
        'market_id' => $market->id,
    ]);
});

it('memperbarui kontributor UPTD dan menyimpannya ke database', function () {
    $this->actingAs(User::factory()->create());
    $market = Market::factory()->create();
    $contributor = User::factory()->create([
        'role' => Role::UptdPasar,
        'name' => 'Nama Lama',
        'market_id' => $market->id,
    ]);

    $this->put(route('admin.uptd-contributors.update', $contributor), [
        'name' => 'Nama Baru',
        'username' => $contributor->username,
        'market_id' => $market->id,
    ])->assertRedirect(route('admin.uptd-contributors.index'));

    $this->assertDatabaseHas('users', [
        'id' => $contributor->id,
        'name' => 'Nama Baru',
        'role' => Role::UptdPasar->value,
        'market_id' => $market->id,
    ]);
});

it('menghapus kontributor UPTD dari database', function () {
    $this->actingAs(User::factory()->create());
    $contributor = User::factory()->create([
        'role' => Role::UptdPasar,
        'market_id' => Market::factory(),
    ]);

    $this->delete(route('admin.uptd-contributors.destroy', $contributor))
        ->assertRedirect(route('admin.uptd-contributors.index'));

    $this->assertDatabaseMissing('users', ['id' => $contributor->id]);
});

it('menolak mengelola kontributor pasar lewat tab pengguna', function () {
    $admin = User::factory()->create();
    $contributor = User::factory()->create([
        'role' => Role::UptdPasar,
        'market_id' => Market::factory(),
    ]);

    $this->actingAs($admin)
        ->get(route('admin.users.edit', $contributor))
        ->assertNotFound();

    $this->delete(route('admin.users.destroy', $contributor))
        ->assertNotFound();

    $this->assertDatabaseHas('users', ['id' => $contributor->id]);
});

it('mengizinkan pengelolaan akun bersama UPTD dari tab pengguna', function () {
    $admin = User::factory()->create();
    $shared = User::factory()->create(['role' => Role::UptdPasar]);

    $this->actingAs($admin)
        ->get(route('admin.users.edit', $shared))
        ->assertOk();
});

it('menampilkan pilihan peran UPTD pada form pengguna saat belum ada akun bersama', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('admin.users.create'))
        ->assertOk()
        ->assertSee('value="'.Role::UptdPasar->value.'"', false)
        ->assertDontSee('Peran UPTD di sini');
});

it('menyembunyikan pilihan peran UPTD pada form pengguna saat akun bersama sudah ada', function () {
    User::factory()->create(['role' => Role::UptdPasar]);

    $this->actingAs(User::factory()->create())
        ->get(route('admin.users.create'))
        ->assertOk()
        ->assertDontSee('value="'.Role::UptdPasar->value.'"', false);
});

it('mengizinkan pembuatan akun bersama UPTD pertama', function () {
    $admin = User::factory()->create();

    $this->actingAs($admin)
        ->post(route('admin.users.store'), [
            'name' => 'Akun UPTD Bersama',
            'username' => 'uptd.bersama',
            'email' => 'uptd.bersama@mipta.test',
            'password' => 'password123',
            'role' => Role::UptdPasar->value,
            'is_active' => '1',
        ])
        ->assertRedirect(route('admin.users.index'));

    $this->assertDatabaseHas('users', [
        'username' => 'uptd.bersama',
        'role' => Role::UptdPasar->value,
        'market_id' => null,
    ]);
});

it('menolak pembuatan akun bersama UPTD kedua', function () {
    $admin = User::factory()->create();
    User::factory()->create(['role' => Role::UptdPasar]);

    $this->actingAs($admin)
        ->post(route('admin.users.store'), [
            'name' => 'Akun UPTD Kedua',
            'username' => 'uptd.kedua',
            'email' => 'uptd.kedua@mipta.test',
            'password' => 'password123',
            'role' => Role::UptdPasar->value,
            'is_active' => '1',
        ])
        ->assertSessionHasErrors('role');

    $this->assertDatabaseMissing('users', ['username' => 'uptd.kedua']);

    expect(User::where('role', Role::UptdPasar->value)->whereNull('market_id')->count())->toBe(1);
});

it('tetap menampilkan pilihan peran UPTD saat mengedit akun bersama UPTD', function () {
    $admin = User::factory()->create();
    $shared = User::factory()->create(['role' => Role::UptdPasar]);

    $this->actingAs($admin)
        ->get(route('admin.users.edit', $shared))
        ->assertOk()
        ->assertSee('value="'.Role::UptdPasar->value.'"', false);
});

it('menyembunyikan pilihan peran UPTD saat mengedit akun lain jika akun bersama sudah ada', function () {
    $admin = User::factory()->create();
    User::factory()->create(['role' => Role::UptdPasar]);
    $target = User::factory()->create(['role' => Role::Admin]);

    $this->actingAs($admin)
        ->get(route('admin.users.edit', $target))
        ->assertOk()
        ->assertDontSee('value="'.Role::UptdPasar->value.'"', false);
});

it('mengizinkan pembaruan akun bersama UPTD tanpa menolak akun itu sendiri', function () {
    $admin = User::factory()->create();
    $shared = User::factory()->create(['role' => Role::UptdPasar]);

    $this->actingAs($admin)
        ->put(route('admin.users.update', $shared), [
            'name' => $shared->name,
            'username' => $shared->username,
            'email' => $shared->email,
            'role' => Role::UptdPasar->value,
            'is_active' => '1',
        ])
        ->assertRedirect(route('admin.users.index'));

    expect($shared->fresh()->role)->toBe(Role::UptdPasar);
});

it('menolak mengubah pengguna lain menjadi UPTD saat akun bersama sudah ada', function () {
    $admin = User::factory()->create();
    User::factory()->create(['role' => Role::UptdPasar]);
    $target = User::factory()->create(['role' => Role::Admin]);

    $this->actingAs($admin)
        ->put(route('admin.users.update', $target), [
            'name' => $target->name,
            'username' => $target->username,
            'email' => $target->email,
            'role' => Role::UptdPasar->value,
            'is_active' => '1',
        ])
        ->assertSessionHasErrors('role');

    expect($target->fresh()->role)->toBe(Role::Admin);
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
