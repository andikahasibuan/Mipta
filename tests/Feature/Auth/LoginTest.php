<?php

use App\Enums\Role;
use App\Models\Market;
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
        ->assertSee('Ingat saya')
        ->assertSee('Lupa kata sandi?')
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

it('mengautentikasi kontributor UPTD dan mengarahkan ke dashboard UPTD', function () {
    $contributor = User::factory()->create([
        'email' => 'kontributor.uptd.01@mipta.test',
        'password' => 'admin123',
        'role' => Role::UptdPasar,
        'market_id' => Market::factory(),
    ]);

    $this->post(route('login'), [
        'identifier' => 'kontributor.uptd.01@mipta.test',
        'password' => 'admin123',
    ])->assertRedirect(route('uptd.dashboard'));

    $this->assertAuthenticatedAs($contributor);
});

it('menolak kontributor UPTD yang belum dikaitkan ke pasar', function () {
    User::factory()->create([
        'email' => 'uptd.lepas@mipta.test',
        'password' => 'admin123',
        'role' => Role::UptdPasar,
    ]);

    $this->post(route('login'), [
        'identifier' => 'uptd.lepas@mipta.test',
        'password' => 'admin123',
    ])->assertSessionHasErrors('identifier');

    expect(session('errors')->first('identifier'))
        ->toContain('Akun kontributor UPTD belum dikaitkan ke pasar. Hubungi administrator.');

    $this->assertGuest();
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

it('mengingat sesi login saat opsi ingat saya dipilih', function () {
    User::factory()->create([
        'email' => 'admin@mipta.test',
        'password' => 'admin123',
    ]);

    $this->post(route('login'), [
        'identifier' => 'admin@mipta.test',
        'password' => 'admin123',
        'remember' => '1',
    ])
        ->assertRedirect(route('admin.dashboard'))
        ->assertCookie(auth()->guard('web')->getRecallerName());

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
        'remember' => '1',
    ])
        ->assertSessionHasErrors('identifier')
        ->assertCookieMissing(auth()->guard('web')->getRecallerName());

    expect(session('errors')->first('identifier'))
        ->toContain('Akun ini dinonaktifkan. Hubungi administrator.');

    $this->assertGuest();
});

it('membatasi percobaan login yang gagal', function () {
    User::factory()->create([
        'email' => 'admin@mipta.test',
        'password' => 'admin123',
    ]);

    foreach (range(1, 5) as $attempt) {
        $this->post(route('login'), [
            'identifier' => 'admin@mipta.test',
            'password' => 'password-salah',
        ])->assertSessionHasErrors('identifier');
    }

    $this->post(route('login'), [
        'identifier' => 'admin@mipta.test',
        'password' => 'admin123',
    ])->assertSessionHasErrors('identifier');

    expect(session('errors')->first('identifier'))->toContain('Terlalu banyak percobaan masuk.');

    $this->assertGuest();
});

it('mengakhiri sesi saat logout', function () {
    $admin = User::factory()->create();

    $this->actingAs($admin)
        ->post(route('logout'))
        ->assertRedirect(route('login'));

    $this->assertGuest();
});
