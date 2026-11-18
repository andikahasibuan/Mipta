<?php

use App\Models\Market;
use App\Models\UptdContributor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    config([
        'help.admin.whatsapp' => '081234567890',
        'help.admin.email' => 'admin@mipta.test',
        'help.admin.service_hours' => 'Senin - Jumat, 08.00 - 16.00 WIB',
        'help.admin.whatsapp_message' => 'Halo Admin MIPTA, saya butuh bantuan terkait akun saya.',
    ]);
});

it('menampilkan halaman pusat bantuan untuk tamu', function () {
    $this->get(route('help'))
        ->assertOk()
        ->assertSee('Pusat Bantuan')
        ->assertSee('+62 812-3456-7890')
        ->assertSee('admin@mipta.test')
        ->assertSee('Senin - Jumat, 08.00 - 16.00 WIB')
        ->assertSee('Kembali ke Login');
});

it('menyediakan tautan whatsapp dengan pesan otomatis', function () {
    $this->get(route('help'))
        ->assertOk()
        ->assertSee(
            'https://wa.me/6281234567890?text=Halo%20Admin%20MIPTA%2C%20saya%20butuh%20bantuan.%20Username%3A%20...%20Peran%20akun%3A%20...%20Kendala%3A%20...',
            false,
        );
});

it('menautkan hubungi admin pada halaman login ke pusat bantuan', function () {
    $this->get(route('login'))
        ->assertOk()
        ->assertSee('href="'.route('help').'"', false);
});

it('menautkan hubungi admin pada login kontributor UPTD ke pusat bantuan', function () {
    $contributor = UptdContributor::factory()->create([
        'market_id' => Market::factory(),
    ]);

    $this->actingAs(sharedUptdAccount())
        ->withSession(['uptd_pending_contributor' => $contributor->id])
        ->get(route('uptd.contributors.login', $contributor))
        ->assertOk()
        ->assertSee('href="'.route('help').'"', false);
});

it('menampilkan tautan kembali ke dashboard untuk pengguna yang sudah login', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('help'))
        ->assertOk()
        ->assertSee('Kembali ke Dashboard')
        ->assertSee('href="'.route('admin.dashboard').'"', false);
});
