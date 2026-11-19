<?php

use App\Enums\Role;
use App\Models\Distribution;
use App\Models\Distributor;
use App\Models\Market;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('menampilkan grafik dashboard kepala dinas dari total karton per tanggal', function () {
    $kepalaDinas = User::factory()->create(['role' => Role::KepalaDinas]);
    $distributor = Distributor::factory()->create();
    $market = Market::factory()->create();
    $uptd = User::factory()->create(['role' => Role::UptdPasar]);
    $firstDay = now()->startOfMonth();

    foreach ([[$firstDay, 100], [$firstDay, 50], [$firstDay->copy()->addDay(), 75]] as [$date, $cartons]) {
        Distribution::factory()->create([
            'distributor_id' => $distributor->id,
            'market_id' => $market->id,
            'uptd_user_id' => $uptd->id,
            'distribution_date' => $date->toDateString(),
            'cartons' => $cartons,
        ]);
    }

    $response = $this->actingAs($kepalaDinas)
        ->get(route('kepala-dinas.dashboard'));

    $response->assertOk()
        ->assertSee('Perkembangan Bulanan')
        ->assertSee('Total karton per hari')
        ->assertSee('Penyaluran MinyaKita (karton)')
        ->assertSee('id="monthly-chart"', false)
        ->assertSee('role="img"', false)
        ->assertSee('aria-label="Grafik perkembangan penyaluran MinyaKita per hari"', false);

    preg_match('/const chartData = (\{.*?\});/s', $response->getContent(), $matches);

    $chartData = json_decode($matches[1], true);

    expect($chartData['values'][0])->toBe(150)
        ->and($chartData['values'][1])->toBe(75)
        ->and(array_sum($chartData['values']))->toBe(225);
});

it('memfilter grafik dashboard kepala dinas berdasarkan bulan', function () {
    $kepalaDinas = User::factory()->create(['role' => Role::KepalaDinas]);
    $market = Market::factory()->create();
    $uptd = User::factory()->create(['role' => Role::UptdPasar]);
    $previousMonth = now()->subMonthNoOverflow()->startOfMonth();

    Distribution::factory()->create([
        'market_id' => $market->id,
        'uptd_user_id' => $uptd->id,
        'distribution_date' => now()->startOfMonth()->toDateString(),
        'cartons' => 100,
    ]);

    Distribution::factory()->create([
        'market_id' => $market->id,
        'uptd_user_id' => $uptd->id,
        'distribution_date' => $previousMonth->toDateString(),
        'cartons' => 50,
    ]);

    $response = $this->actingAs($kepalaDinas)
        ->get(route('kepala-dinas.dashboard', ['month' => $previousMonth->format('Y-m')]));

    $response->assertOk()
        ->assertSee('value="'.$previousMonth->format('Y-m').'" selected', false)
        ->assertSeeInOrder(['Total Laporan', '1'])
        ->assertSeeInOrder(['Total Penyaluran', '50']);

    preg_match('/const chartData = (\{.*?\});/s', $response->getContent(), $matches);

    $chartData = json_decode($matches[1], true);

    expect(array_sum($chartData['values']))->toBe(50);
});
