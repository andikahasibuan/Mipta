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

it('menolak akun yang bukan admin', function () {
    User::factory()->create([
        'email' => 'distributor@mipta.test',
        'password' => 'admin123',
        'role' => Role::Distributor,
    ]);

    $this->post(route('login'), [
        'identifier' => 'distributor@mipta.test',
        'password' => 'admin123',
    ])->assertSessionHasErrors('identifier');

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
