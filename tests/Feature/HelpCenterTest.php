<?php

use App\Models\Market;
use App\Models\UptdContributor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    config([
        'help.admin.whatsapp' => '081234567890',
        'help.admin.service_hours' => 'Senin - Jumat, 08.00 - 16.00 WIB',
        'help.admin.whatsapp_message' => 'Halo Admin MIPTA, saya butuh bantuan.',
    ]);
});

it('menampilkan halaman pusat bantuan untuk tamu', function () {
    $this->get(route('help'))
        ->assertOk()
        ->assertSee('Pusat Bantuan')
        ->assertSee('+62 812-3456-7890')
        ->assertSee('Kembali')
        ->assertDontSee('Kembali ke Login')
        ->assertDontSee('Email Admin')
        ->assertDontSee('mailto:', false);
});

it('menyediakan tautan whatsapp dengan pesan dan jam layanan', function () {
    $message = "Halo Admin MIPTA, saya butuh bantuan.\n\nJam layanan admin: Senin - Jumat, 08.00 - 16.00 WIB.";
    $url = 'https://wa.me/6281234567890?text='.rawurlencode($message);

    $this->get(route('help'))
        ->assertOk()
        ->assertSee($url, false);
});

it('tidak menggandakan kode negara pada nomor whatsapp', function () {
    config(['help.admin.whatsapp' => '6281234567890']);

    $this->get(route('help'))
        ->assertOk()
        ->assertSee('+62 812-3456-7890')
        ->assertSee('https://wa.me/6281234567890', false);
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
        ->assertSee('Kembali')
        ->assertDontSee('Kembali ke Dashboard')
        ->assertSee('href="'.route('admin.dashboard').'"', false);
});
