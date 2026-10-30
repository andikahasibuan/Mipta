<?php

use App\Models\Distributor;
use App\Models\Market;
use App\Models\RecipientStore;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('menampilkan tombol ekspor data dalam keadaan nonaktif', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('admin.users.index'))
        ->assertOk()
        ->assertSee('Ekspor Data')
        ->assertSee('aria-disabled="true"', false)
        ->assertSee('title="Akan tersedia setelah format ekspor disepakati"', false);
});

it('tidak menampilkan breadcrumb, judul, dan deskripsi di area konten', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('admin.users.index'))
        ->assertOk()
        ->assertDontSee('Cari, filter, dan kelola data sistem secara terpusat')
        ->assertDontSee('aria-label="Breadcrumb"', false);
});

it('menampilkan kartu statistik dari database walaupun data master masih kosong', function () {
    $admin = User::factory()->create();

    $this->actingAs($admin)
        ->get(route('admin.users.index'))
        ->assertOk()
        ->assertSeeInOrder([
            'Total Pengguna', '1', 'Akun terdaftar',
            'Distributor', '0', '0 aktif',
            'Toko Penerima', '0', 'Tersebar di 0 pasar',
        ]);
});

it('menghitung distributor, toko, dan pasar dari database', function () {
    $user = User::factory()->create();

    Distributor::factory()->count(2)->create();
    Distributor::factory()->create(['is_active' => false]);

    $firstMarket = Market::factory()->create();
    $secondMarket = Market::factory()->create();
    Market::factory()->create();

    RecipientStore::factory()->count(2)->create(['market_id' => $firstMarket->id]);
    RecipientStore::factory()->create(['market_id' => $secondMarket->id]);

    $this->actingAs($user)
        ->get(route('admin.users.index'))
        ->assertOk()
        ->assertSeeInOrder([
            'Total Pengguna', '1',
            'Distributor', '3', '2 aktif',
            'Toko Penerima', '3', 'Tersebar di 2 pasar',
        ]);
});
