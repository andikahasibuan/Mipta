<?php

use App\Enums\Role;
use App\Models\Market;
use App\Models\UptdContributor;
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
        ->assertSee('Rina Marlina')
        ->assertSee('data-password-toggle="password"', false);
});

it('menampilkan tombol tampilkan password pada form tambah pengguna', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('admin.users.create'))
        ->assertOk()
        ->assertSee('data-password-toggle="password"', false);
});

it('membuat pengguna tanpa email', function () {
    $this->actingAs(User::factory()->create());

    $this->post(route('admin.users.store'), [
        'name' => 'Tanpa Email',
        'username' => 'tanpa.email',
        'password' => 'password123',
        'role' => Role::KepalaDinas->value,
        'is_active' => '1',
    ])->assertRedirect(route('admin.users.index'));

    $this->assertDatabaseHas('users', [
        'username' => 'tanpa.email',
        'email' => null,
        'role' => Role::KepalaDinas->value,
    ]);
});

it('mengosongkan email pengguna saat edit', function () {
    $this->actingAs(User::factory()->create());
    $user = User::factory()->create();

    $this->put(route('admin.users.update', $user), [
        'name' => $user->name,
        'username' => $user->username,
        'email' => '',
        'role' => $user->role->value,
        'is_active' => '1',
    ])->assertRedirect(route('admin.users.index'));

    expect($user->fresh()->email)->toBeNull();
});

it('menyimpan username pengguna dalam huruf kecil', function () {
    $this->actingAs(User::factory()->create());

    $this->post(route('admin.users.store'), [
        'name' => 'Username Kapital',
        'username' => 'Admin.Baru',
        'password' => 'password123',
        'role' => Role::Admin->value,
    ])->assertRedirect(route('admin.users.index'));

    $this->assertDatabaseHas('users', ['username' => 'admin.baru']);
});

it('menolak username dengan spasi atau karakter khusus', function (string $username) {
    $this->actingAs(User::factory()->create());

    $this->post(route('admin.users.store'), [
        'name' => 'Username Tidak Valid',
        'username' => $username,
        'password' => 'password123',
        'role' => Role::Admin->value,
    ])->assertSessionHasErrors('username');

    $this->assertDatabaseMissing('users', ['username' => $username]);
})->with([
    'spasi' => ['user name'],
    'karakter khusus' => ['user!name'],
]);

it('menolak username yang sudah digunakan', function () {
    $this->actingAs(User::factory()->create());
    User::factory()->create(['username' => 'sudah.dipakai']);

    $this->post(route('admin.users.store'), [
        'name' => 'Username Duplikat',
        'username' => 'sudah.dipakai',
        'password' => 'password123',
        'role' => Role::Admin->value,
    ])->assertSessionHasErrors('username');
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
    User::factory()->create(['role' => Role::Admin]);
    $this->actingAs($admin);

    $this->from(route('admin.users.index'))
        ->delete(route('admin.users.destroy', $admin))
        ->assertRedirect(route('admin.users.index'))
        ->assertSessionHasErrors('delete');

    $this->assertDatabaseHas('users', ['id' => $admin->id]);
});

it('menyimpan profil kontributor UPTD dengan peran UPTD Pasar', function () {
    $this->actingAs(User::factory()->create());
    $market = Market::factory()->create();

    $this->post(route('admin.uptd-contributors.store'), [
        'name' => 'Dewi Anggraini',
        'username' => 'dewi.anggraini',
        'market_id' => $market->id,
        'is_active' => '1',
    ])->assertRedirect(route('admin.uptd-contributors.index'));

    $this->assertDatabaseHas('uptd_contributors', [
        'username' => 'dewi.anggraini',
        'market_id' => $market->id,
    ]);
});

it('memperbarui profil kontributor UPTD dan menyimpannya ke database', function () {
    $this->actingAs(User::factory()->create());
    $market = Market::factory()->create();
    $contributor = UptdContributor::factory()->create([
        'name' => 'Nama Lama',
        'market_id' => $market->id,
    ]);

    $this->put(route('admin.uptd-contributors.update', $contributor), [
        'name' => 'Nama Baru',
        'username' => $contributor->username,
        'market_id' => $market->id,
    ])->assertRedirect(route('admin.uptd-contributors.index'));

    $this->assertDatabaseHas('uptd_contributors', [
        'id' => $contributor->id,
        'name' => 'Nama Baru',
        'market_id' => $market->id,
    ]);
});

it('menghapus profil kontributor UPTD dari database', function () {
    $this->actingAs(User::factory()->create());
    $contributor = UptdContributor::factory()->create([
        'market_id' => Market::factory(),
    ]);

    $this->delete(route('admin.uptd-contributors.destroy', $contributor))
        ->assertRedirect(route('admin.uptd-contributors.index'));

    $this->assertDatabaseMissing('uptd_contributors', ['id' => $contributor->id]);
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

it('menampilkan pilihan peran UPTD pada form pengguna', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('admin.users.create'))
        ->assertOk()
        ->assertSee('value="'.Role::UptdPasar->value.'"', false)
        ->assertDontSee('Peran UPTD di sini');
});

it('tetap menampilkan pilihan peran UPTD pada form pengguna saat akun bersama sudah ada', function () {
    User::factory()->create(['role' => Role::UptdPasar]);

    $this->actingAs(User::factory()->create())
        ->get(route('admin.users.create'))
        ->assertOk()
        ->assertSee('value="'.Role::UptdPasar->value.'"', false);
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

it('mengizinkan pembuatan akun bersama UPTD kedua', function () {
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
        ->assertRedirect(route('admin.users.index'));

    $this->assertDatabaseHas('users', [
        'username' => 'uptd.kedua',
        'role' => Role::UptdPasar->value,
        'market_id' => null,
    ]);

    expect(User::where('role', Role::UptdPasar->value)->whereNull('market_id')->count())->toBe(2);
});

it('tetap menampilkan pilihan peran UPTD saat mengedit akun bersama UPTD', function () {
    $admin = User::factory()->create();
    $shared = User::factory()->create(['role' => Role::UptdPasar]);

    $this->actingAs($admin)
        ->get(route('admin.users.edit', $shared))
        ->assertOk()
        ->assertSee('value="'.Role::UptdPasar->value.'"', false);
});

it('tetap menampilkan pilihan peran UPTD saat mengedit akun lain jika akun bersama sudah ada', function () {
    $admin = User::factory()->create();
    User::factory()->create(['role' => Role::UptdPasar]);
    $target = User::factory()->create(['role' => Role::Admin]);

    $this->actingAs($admin)
        ->get(route('admin.users.edit', $target))
        ->assertOk()
        ->assertSee('value="'.Role::UptdPasar->value.'"', false);
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

it('mengizinkan mengubah pengguna lain menjadi UPTD saat akun bersama sudah ada', function () {
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
        ->assertRedirect(route('admin.users.index'));

    expect($target->fresh()->role)->toBe(Role::UptdPasar);
});

it('mencegah admin mengubah peran atau menonaktifkan akunnya sendiri', function () {
    $admin = User::factory()->create(['role' => Role::Admin]);
    User::factory()->create(['role' => Role::Admin]);

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
