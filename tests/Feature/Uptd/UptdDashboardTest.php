<?php

use App\Enums\FieldCondition;
use App\Enums\Role;
use App\Models\Distribution;
use App\Models\Distributor;
use App\Models\FieldDistributionReport;
use App\Models\Market;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('menampilkan identitas kontributor dan tautan navigasi dashboard', function () {
    $market = Market::factory()->create(['name' => 'Pasar Raya Padang']);
    $contributor = User::factory()->create([
        'name' => 'Kontributor UPTD 01',
        'role' => Role::UptdPasar,
        'market_id' => $market->id,
    ]);

    $response = $this->actingAs($contributor)->get(route('uptd.dashboard'));

    $response->assertOk()
        ->assertSee('Kontributor UPTD 01')
        ->assertSee('Pasar Raya Padang')
        ->assertDontSee('Kontributor aktif')
        ->assertDontSee('aria-label="Breadcrumb"', false)
        ->assertDontSee('Ganti identitas')
        ->assertDontSee('Lihat semua')
        ->assertDontSee('Data laporan dari UPTD Pasar');

    preg_match('/<main[^>]*>.*?<\/main>/s', $response->getContent(), $matches);
    $main = $matches[0] ?? '';

    expect($main)->not->toContain(route('uptd.reports.create'));
});

it('menghitung total laporan dan kondisi normal hanya dari laporan milik kontributor', function () {
    $market = Market::factory()->create();
    $otherMarket = Market::factory()->create();
    $contributor = User::factory()->create([
        'role' => Role::UptdPasar,
        'market_id' => $market->id,
    ]);
    $otherContributor = User::factory()->create([
        'role' => Role::UptdPasar,
        'market_id' => $market->id,
    ]);

    FieldDistributionReport::factory()->create([
        'market_id' => $market->id,
        'uptd_user_id' => $contributor->id,
        'condition' => FieldCondition::Normal,
    ]);

    FieldDistributionReport::factory()->create([
        'market_id' => $market->id,
        'uptd_user_id' => $contributor->id,
        'condition' => FieldCondition::PerluPerhatian,
    ]);

    FieldDistributionReport::factory()->create([
        'market_id' => $market->id,
        'uptd_user_id' => $otherContributor->id,
        'condition' => FieldCondition::Normal,
    ]);

    FieldDistributionReport::factory()->create([
        'market_id' => $otherMarket->id,
        'uptd_user_id' => $contributor->id,
        'condition' => FieldCondition::Normal,
    ]);

    $this->actingAs($contributor)
        ->get(route('uptd.dashboard'))
        ->assertOk()
        ->assertSeeInOrder(['Total Laporan', '2', 'Laporan kontributor aktif'])
        ->assertSeeInOrder(['Kondisi normal', '1', 'Berdasarkan riwayat laporan']);
});

it('menghitung distributor yang menyalurkan ke pasar kontributor tanpa melihat status akun', function () {
    $market = Market::factory()->create();
    $otherMarket = Market::factory()->create();
    $contributor = User::factory()->create([
        'role' => Role::UptdPasar,
        'market_id' => $market->id,
    ]);

    $activeDistributor = Distributor::factory()->create(['is_active' => true]);
    $inactiveDistributor = Distributor::factory()->create(['is_active' => false]);
    $draftOnlyDistributor = Distributor::factory()->create(['is_active' => true]);
    $otherMarketDistributor = Distributor::factory()->create(['is_active' => true]);
    Distributor::factory()->create(['is_active' => true]);

    Distribution::factory()->create([
        'distributor_id' => $activeDistributor->id,
        'market_id' => $market->id,
        'uptd_user_id' => $contributor->id,
    ]);

    Distribution::factory()->create([
        'distributor_id' => $inactiveDistributor->id,
        'market_id' => $market->id,
        'uptd_user_id' => $contributor->id,
    ]);

    Distribution::factory()->draft()->create([
        'distributor_id' => $draftOnlyDistributor->id,
        'market_id' => $market->id,
        'uptd_user_id' => $contributor->id,
    ]);

    Distribution::factory()->create([
        'distributor_id' => $otherMarketDistributor->id,
        'market_id' => $otherMarket->id,
        'uptd_user_id' => $contributor->id,
    ]);

    $this->actingAs($contributor)
        ->get(route('uptd.dashboard'))
        ->assertOk()
        ->assertSeeInOrder(['Distributor Aktif', '2', 'Melakukan penyaluran ke pasar ini']);
});

it('menampilkan catatan penyaluran saat distributor menyalurkan ke pasar', function () {
    $market = Market::factory()->create();
    $contributor = User::factory()->create([
        'role' => Role::UptdPasar,
        'market_id' => $market->id,
    ]);

    Distribution::factory()->create([
        'distributor_id' => Distributor::factory()->create(['is_active' => true]),
        'market_id' => $market->id,
        'uptd_user_id' => $contributor->id,
    ]);

    $this->actingAs($contributor)
        ->get(route('uptd.dashboard'))
        ->assertOk()
        ->assertSeeInOrder(['Distributor Aktif', '1', 'Melakukan penyaluran ke pasar ini']);
});

it('menampilkan lima laporan kondisi pasar terbaru per halaman secara berurutan', function () {
    $market = Market::factory()->create();
    $contributor = User::factory()->create([
        'name' => 'Kontributor UPTD 01',
        'role' => Role::UptdPasar,
        'market_id' => $market->id,
    ]);
    $otherContributor = User::factory()->create([
        'role' => Role::UptdPasar,
        'market_id' => $market->id,
    ]);

    FieldDistributionReport::factory()->create([
        'market_id' => $market->id,
        'uptd_user_id' => $otherContributor->id,
        'report_date' => '2026-05-21',
        'condition' => FieldCondition::Normal,
    ]);

    foreach (['2026-05-20', '2026-05-19', '2026-05-18', '2026-05-17', '2026-05-16', '2026-05-15'] as $index => $date) {
        FieldDistributionReport::factory()->create([
            'market_id' => $market->id,
            'uptd_user_id' => $contributor->id,
            'report_date' => $date,
            'condition' => $index === 0 ? FieldCondition::PerluPerhatian : FieldCondition::Normal,
        ]);
    }

    $this->actingAs($contributor)
        ->get(route('uptd.dashboard'))
        ->assertOk()
        ->assertSeeInOrder(['20 Mei 2026', '19 Mei 2026', '18 Mei 2026', '17 Mei 2026', '16 Mei 2026'])
        ->assertDontSee('15 Mei 2026')
        ->assertDontSee('21 Mei 2026')
        ->assertSee('Menampilkan 5 dari 6 laporan')
        ->assertSee('page=2', false)
        ->assertSee('Perlu Perhatian')
        ->assertSee('Normal');

    $this->actingAs($contributor)
        ->get(route('uptd.dashboard', ['page' => 2]))
        ->assertOk()
        ->assertSee('15 Mei 2026')
        ->assertDontSee('20 Mei 2026')
        ->assertSee('Menampilkan 1 dari 6 laporan');
});

it('menampilkan keadaan kosong saat belum ada laporan dan distributor', function () {
    $market = Market::factory()->create();
    $contributor = User::factory()->create([
        'role' => Role::UptdPasar,
        'market_id' => $market->id,
    ]);

    $this->actingAs($contributor)
        ->get(route('uptd.dashboard'))
        ->assertOk()
        ->assertSee('Belum ada laporan kondisi pasar')
        ->assertSeeInOrder(['Distributor Aktif', '0', 'Belum ada penyaluran ke pasar']);
});
