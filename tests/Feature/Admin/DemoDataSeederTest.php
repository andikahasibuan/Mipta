<?php

use App\Enums\Role;
use App\Models\Distribution;
use App\Models\Distributor;
use App\Models\Market;
use App\Models\MarketMonitoring;
use App\Models\RecipientStore;
use App\Models\User;
use Database\Seeders\DemoDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('membuat data contoh untuk master, pemantauan, penyaluran, dan pengguna', function () {
    $this->seed(DemoDataSeeder::class);

    expect(Distributor::count())->toBe(3)
        ->and(Market::count())->toBe(3)
        ->and(RecipientStore::count())->toBe(4)
        ->and(MarketMonitoring::count())->toBe(4)
        ->and(Distribution::count())->toBe(5)
        ->and(Distribution::sum('cartons'))->toBe(900)
        ->and(User::count())->toBe(3)
        ->and(User::where('role', Role::Distributor)->count())->toBe(1)
        ->and(User::where('role', Role::UptdPasar)->count())->toBe(1)
        ->and(User::where('role', Role::KepalaDinas)->count())->toBe(1);
});

it('menampilkan data contoh pada dashboard dan menu pengguna dan data master', function () {
    $this->seed(DemoDataSeeder::class);

    $admin = User::factory()->create();

    $this->actingAs($admin)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('PT Amanah Niaga Mandiri')
        ->assertSee('Pasar Raya Padang')
        ->assertSeeInOrder(['Total Laporan', '3', '+50,0% dari bulan lalu'])
        ->assertSeeInOrder(['Total Penyaluran', '550'])
        ->assertSeeInOrder(['Distributor Aktif', '3', 'Seluruhnya aktif'])
        ->assertSeeInOrder(['Pasar Terpantau', '2', '67% wilayah terpantau']);

    $this->get(route('admin.distributors.index'))
        ->assertOk()
        ->assertSee('PT Amanah Niaga Mandiri');

    $this->get(route('admin.markets.index'))
        ->assertOk()
        ->assertSee('Pasar Raya Padang');

    $this->get(route('admin.users.index'))
        ->assertOk()
        ->assertSee('Rina Marlina')
        ->assertSee('Andi Pratama')
        ->assertSee('Dewi Anggraini');

    $this->get(route('admin.recipient-stores.index'))
        ->assertOk()
        ->assertSee('Toko Atong')
        ->assertSee('Toko Mak Itam')
        ->assertSee('Toko Mail')
        ->assertSee('Toko Hisyam');
});
