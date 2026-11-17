<?php

use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('menampilkan halaman login untuk tamu', function () {
    $this->get(route('login'))
        ->assertOk()
        ->assertSee('MIPTA')
        ->assertSee('Selamat Datang')
        ->assertSee('Silahkan masuk menggunakan akun yang telah terdaftar')
        ->assertSee('Email atau Username')
        ->assertSee('Hubungi Admin')
        ->assertSee('data-password-toggle="password"', false)
        ->assertSee('Masuk');
});

it('mengarahkan tamu dari dashboard admin ke halaman login', function () {
    $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
});

it('mengautentikasi admin memakai email', function () {
    $admin = User::factory()->create([
        'email' => 'admin@mipta.test',
        'password' => 'admin123',
    ]);

    $this->post(route('login'), [
        'identifier' => 'admin@mipta.test',
        'password' => 'admin123',
    ])->assertRedirect(route('admin.dashboard'));

    $this->assertAuthenticatedAs($admin);
});

it('mengautentikasi admin memakai username', function () {
    $admin = User::factory()->create([
        'username' => 'admin',
        'password' => 'admin123',
    ]);

    $this->post(route('login'), [
        'identifier' => 'admin',
        'password' => 'admin123',
    ])->assertRedirect(route('admin.dashboard'));

    $this->assertAuthenticatedAs($admin);
});

it('mengautentikasi kepala dinas dan mengarahkan ke dashboard kepala dinas', function () {
    $kepalaDinas = User::factory()->create([
        'email' => 'kepala.dinas@mipta.test',
        'password' => 'admin123',
        'role' => Role::KepalaDinas,
    ]);

    $this->post(route('login'), [
        'identifier' => 'kepala.dinas@mipta.test',
        'password' => 'admin123',
    ])->assertRedirect(route('kepala-dinas.dashboard'));

    $this->assertAuthenticatedAs($kepalaDinas);
});

it('menghapus identitas kontributor lama saat akun bersama UPTD login', function () {
    $shared = User::factory()->create([
        'email' => 'uptd@mipta.test',
        'password' => 'admin123',
        'role' => Role::UptdPasar,
    ]);

    $this->withSession([
        'uptd_selected_contributor' => ['id' => 99],
    ]);

    $this->post(route('login'), [
        'identifier' => 'uptd@mipta.test',
        'password' => 'admin123',
    ])
        ->assertRedirect(route('uptd.contributors.index'))
        ->assertSessionMissing('uptd_selected_contributor');

    $this->assertAuthenticatedAs($shared);
});

it('mengarahkan akun bersama UPTD ke halaman pemilihan identitas', function () {
    $shared = User::factory()->create([
        'email' => 'uptd@mipta.test',
        'password' => 'admin123',
        'role' => Role::UptdPasar,
    ]);

    $this->post(route('login'), [
        'identifier' => 'uptd@mipta.test',
        'password' => 'admin123',
    ])->assertRedirect(route('uptd.contributors.index'));

    $this->assertAuthenticatedAs($shared);
});

it('menolak distributor yang belum dikaitkan ke PT', function () {
    User::factory()->create([
        'email' => 'distributor.lepas@mipta.test',
        'password' => 'admin123',
        'role' => Role::Distributor,
    ]);

    $this->post(route('login'), [
        'identifier' => 'distributor.lepas@mipta.test',
        'password' => 'admin123',
    ])->assertSessionHasErrors('identifier');

    expect(session('errors')->first('identifier'))
        ->toContain('Akun distributor belum dikaitkan ke PT. Hubungi administrator.');

    $this->assertGuest();
});

it('mengabaikan tujuan awal di luar area peran setelah login', function () {
    User::factory()->create([
        'email' => 'kepala.dinas@mipta.test',
        'password' => 'admin123',
        'role' => Role::KepalaDinas,
    ]);

    $this->get(route('admin.dashboard'))->assertRedirect(route('login'));

    $this->post(route('login'), [
        'identifier' => 'kepala.dinas@mipta.test',
        'password' => 'admin123',
    ])->assertRedirect(route('kepala-dinas.dashboard'));
});

it('tidak membuat cookie ingat saya saat login', function () {
    User::factory()->create([
        'email' => 'admin@mipta.test',
        'password' => 'admin123',
    ]);

    $this->post(route('login'), [
        'identifier' => 'admin@mipta.test',
        'password' => 'admin123',
    ])
        ->assertRedirect(route('admin.dashboard'))
        ->assertCookieMissing(auth()->guard('web')->getRecallerName());

    $this->assertAuthenticated();
});

it('menolak password yang salah', function () {
    User::factory()->create([
        'email' => 'admin@mipta.test',
        'password' => 'admin123',
    ]);

    $this->post(route('login'), [
        'identifier' => 'admin@mipta.test',
        'password' => 'password-salah',
    ])->assertSessionHasErrors('identifier');

    $this->assertGuest();
});

it('mengautentikasi distributor dan mengarahkan ke dashboard distributor', function () {
    $distributor = User::factory()->distributor()->create([
        'email' => 'distributor@mipta.test',
        'password' => 'admin123',
    ]);

    $this->post(route('login'), [
        'identifier' => 'distributor@mipta.test',
        'password' => 'admin123',
    ])->assertRedirect(route('distributor.dashboard'));

    $this->assertAuthenticatedAs($distributor);
});

it('menolak akun yang dinonaktifkan', function () {
    User::factory()->inactive()->create([
        'email' => 'nonaktif@mipta.test',
        'password' => 'admin123',
    ]);

    $this->post(route('login'), [
        'identifier' => 'nonaktif@mipta.test',
        'password' => 'admin123',
    ])
        ->assertSessionHasErrors('identifier')
        ->assertCookieMissing(auth()->guard('web')->getRecallerName());

    expect(session('errors')->first('identifier'))
        ->toContain('Akun ini dinonaktifkan. Hubungi administrator.');

    $this->assertGuest();
});

it('mengunci login selama 10 menit setelah tiga kali gagal', function () {
    $admin = User::factory()->create([
        'email' => 'admin@mipta.test',
        'password' => 'admin123',
    ]);

    foreach (range(1, 3) as $attempt) {
        $this->post(route('login'), [
            'identifier' => 'admin@mipta.test',
            'password' => 'password-salah',
        ])->assertSessionHasErrors('identifier');
    }

    $this->post(route('login'), [
        'identifier' => 'admin@mipta.test',
        'password' => 'admin123',
    ])->assertSessionHasErrors('identifier');

    expect(session('errors')->first('identifier'))
        ->toContain('Terlalu banyak percobaan masuk.')
        ->toContain('10 menit');

    $this->assertGuest();

    $this->travel(11)->minutes();

    $this->post(route('login'), [
        'identifier' => 'admin@mipta.test',
        'password' => 'admin123',
    ])->assertRedirect(route('admin.dashboard'));

    $this->assertAuthenticatedAs($admin);
});

it('mengunci akun meskipun percobaan berikutnya memakai email atau username berbeda', function () {
    User::factory()->create([
        'email' => 'admin@mipta.test',
        'username' => 'admin.mipta',
        'password' => 'admin123',
    ]);

    foreach (range(1, 3) as $attempt) {
        $this->post(route('login'), [
            'identifier' => 'admin@mipta.test',
            'password' => 'password-salah',
        ])->assertSessionHasErrors('identifier');
    }

    $this->post(route('login'), [
        'identifier' => 'admin.mipta',
        'password' => 'admin123',
    ])->assertSessionHasErrors('identifier');

    expect(session('errors')->first('identifier'))
        ->toContain('Terlalu banyak percobaan masuk.')
        ->toContain('10 menit');

    $this->assertGuest();
});

it('mengunci ip setelah tiga percobaan dengan username yang tidak terdaftar', function () {
    User::factory()->create([
        'username' => 'admin.mipta',
        'password' => 'admin123',
    ]);

    foreach (range(1, 3) as $attempt) {
        $this->post(route('login'), [
            'identifier' => "tidak-terdaftar-{$attempt}",
            'password' => 'password-salah',
        ])->assertSessionHasErrors('identifier');
    }

    $this->post(route('login'), [
        'identifier' => 'admin.mipta',
        'password' => 'admin123',
    ])->assertSessionHasErrors('identifier');

    expect(session('errors')->first('identifier'))
        ->toContain('Terlalu banyak percobaan masuk.')
        ->toContain('10 menit');

    $this->assertGuest();
});

it('tidak mengunci pengguna lain pada ip yang sama saat satu akun gagal berkali-kali', function () {
    User::factory()->create([
        'username' => 'admin.pertama',
        'password' => 'admin123',
    ]);
    $kedua = User::factory()->create([
        'username' => 'admin.kedua',
        'password' => 'admin456',
    ]);

    foreach (range(1, 3) as $attempt) {
        $this->post(route('login'), [
            'identifier' => 'admin.pertama',
            'password' => 'password-salah',
        ])->assertSessionHasErrors('identifier');
    }

    $this->post(route('login'), [
        'identifier' => 'admin.kedua',
        'password' => 'admin456',
    ])->assertRedirect(route('admin.dashboard'));

    $this->assertAuthenticatedAs($kedua);
});

it('mengakhiri sesi saat logout', function () {
    $admin = User::factory()->create();

    $this->actingAs($admin)
        ->post(route('logout'))
        ->assertRedirect(route('login'));

    $this->assertGuest();
});
