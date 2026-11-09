<?php

use App\Models\Distribution;
use App\Models\Distributor;
use App\Models\Market;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('menampilkan ringkasan monitoring dan analisis dari database', function () {
    $distributor = Distributor::factory()->create(['name' => 'PT Contoh Distributor']);
    $marketA = Market::factory()->create(['name' => 'Pasar Alpha']);
    $marketB = Market::factory()->create(['name' => 'Pasar Beta']);

    Distribution::factory()->create([
        'distributor_id' => $distributor->id,
        'market_id' => $marketA->id,
        'distribution_date' => now()->toDateString(),
        'cartons' => 200,
    ]);

    Distribution::factory()->create([
        'distributor_id' => $distributor->id,
        'market_id' => $marketB->id,
        'distribution_date' => now()->toDateString(),
        'cartons' => 100,
    ]);

    $this->actingAs(User::factory()->create())
        ->get(route('admin.monitoring.index'))
        ->assertOk()
        ->assertSee('Total Penyaluran Periode Ini')
        ->assertSee('Rata-rata per Laporan')
        ->assertSee('Pasar Terjangkau')
        ->assertSee('Ringkasan Distribusi per Pasar')
        ->assertSee('Kontribusi Distributor')
        ->assertSee('Pasar Alpha')
        ->assertSee('Pasar Beta')
        ->assertSee('PT Contoh Distributor')
        ->assertSee('300')
        ->assertSee('150,0');
});

it('menampilkan keadaan kosong saat belum ada data', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('admin.monitoring.index'))
        ->assertOk()
        ->assertSee('Belum ada data distribusi')
        ->assertSee('Belum ada data distributor');
});

it('memfilter monitoring berdasarkan distributor', function () {
    $distributorA = Distributor::factory()->create(['name' => 'PT Alpha Niaga']);
    $distributorB = Distributor::factory()->create(['name' => 'PT Beta Pangan']);
    $market = Market::factory()->create(['name' => 'Pasar Alpha']);

    Distribution::factory()->create([
        'distributor_id' => $distributorA->id,
        'market_id' => $market->id,
        'distribution_date' => now()->toDateString(),
        'cartons' => 150,
    ]);

    Distribution::factory()->create([
        'distributor_id' => $distributorB->id,
        'market_id' => $market->id,
        'distribution_date' => now()->toDateString(),
        'cartons' => 250,
    ]);

    $this->actingAs(User::factory()->create())
        ->get(route('admin.monitoring.index', ['distributor' => $distributorA->id]))
        ->assertOk()
        ->assertSee('150')
        ->assertDontSee('250');
});

it('tidak menghitung laporan draft pada monitoring', function () {
    $distributor = Distributor::factory()->create();
    $market = Market::factory()->create();

    Distribution::factory()->create([
        'distributor_id' => $distributor->id,
        'market_id' => $market->id,
        'distribution_date' => now()->toDateString(),
        'cartons' => 120,
    ]);

    Distribution::factory()->draft()->create([
        'distributor_id' => $distributor->id,
        'market_id' => $market->id,
        'distribution_date' => now()->toDateString(),
        'cartons' => 4321,
    ]);

    $this->actingAs(User::factory()->create())
        ->get(route('admin.monitoring.index'))
        ->assertOk()
        ->assertSee('120')
        ->assertDontSee('4.321');
});
