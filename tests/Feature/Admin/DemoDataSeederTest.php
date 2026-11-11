<?php

use App\Enums\Role;
use App\Models\Distribution;
use App\Models\Distributor;
use App\Models\FieldDistributionReport;
use App\Models\Market;
use App\Models\MarketMonitoring;
use App\Models\RecipientStore;
use App\Models\User;
use Database\Seeders\DemoDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('membuat data contoh tanpa akun dan data distributor', function () {
    $this->seed(DemoDataSeeder::class);

    expect(Distributor::count())->toBe(0)
        ->and(Market::count())->toBe(3)
        ->and(RecipientStore::count())->toBe(0)
        ->and(MarketMonitoring::count())->toBe(4)
        ->and(Distribution::count())->toBe(0)
        ->and(FieldDistributionReport::count())->toBe(2)
        ->and(User::count())->toBe(2)
        ->and(User::where('role', Role::Distributor)->count())->toBe(0)
        ->and(User::where('role', Role::UptdPasar)->count())->toBe(1)
        ->and(User::where('role', Role::KepalaDinas)->count())->toBe(1);
});

it('menampilkan data contoh yang tersedia pada dashboard dan menu pengguna dan data master', function () {
    $this->seed(DemoDataSeeder::class);

    $admin = User::factory()->create();

    $this->actingAs($admin)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('Pasar Raya Padang')
        ->assertSee('Belum ada data distributor');

    $this->get(route('admin.distributors.index'))
        ->assertOk()
        ->assertSee('Belum ada data distributor');

    $this->get(route('admin.markets.index'))
        ->assertOk()
        ->assertSee('Pasar Raya Padang');

    $this->get(route('admin.users.index'))
        ->assertOk()
        ->assertSee('Dewi Anggraini')
        ->assertDontSee('Andi Pratama')
        ->assertDontSee('Rina Marlina');

    $this->get(route('admin.uptd-contributors.index'))
        ->assertOk()
        ->assertSee('Andi Pratama')
        ->assertSee('Pasar Raya Padang');

    $this->get(route('admin.recipient-stores.index'))
        ->assertOk()
        ->assertSee('Belum ada data toko penerima');
});
