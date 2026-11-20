<?php

use App\Enums\FieldCondition;
use App\Models\FieldDistributionReport;
use App\Models\Market;
use App\Models\RecipientStore;
use App\Models\UptdContributor;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('menampilkan identitas kontributor dan tautan navigasi dashboard', function () {
    $market = Market::factory()->create(['name' => 'Pasar Raya Padang']);
    $contributor = UptdContributor::factory()->create([
        'name' => 'Kontributor UPTD 01',
        'market_id' => $market->id,
    ]);

    $response = $this->actingAs(sharedUptdAccount())
        ->withSession(uptdContributorSession($contributor))
        ->get(route('uptd.dashboard'));

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

it('menghitung total laporan pengawasan milik kontributor di pasar ini', function () {
    $market = Market::factory()->create();
    $otherMarket = Market::factory()->create();
    $contributor = UptdContributor::factory()->create(['market_id' => $market->id]);
    $otherContributor = UptdContributor::factory()->create(['market_id' => $otherMarket->id]);

    FieldDistributionReport::factory()->create([
        'market_id' => $market->id,
        'uptd_contributor_id' => $contributor->id,
        'condition' => FieldCondition::Normal,
    ]);

    FieldDistributionReport::factory()->create([
        'market_id' => $market->id,
        'uptd_contributor_id' => $contributor->id,
        'condition' => FieldCondition::PerluPerhatian,
    ]);

    FieldDistributionReport::factory()->create([
        'market_id' => $market->id,
        'uptd_contributor_id' => $otherContributor->id,
        'condition' => FieldCondition::Normal,
    ]);

    FieldDistributionReport::factory()->create([
        'market_id' => $otherMarket->id,
        'uptd_contributor_id' => $contributor->id,
        'condition' => FieldCondition::Normal,
    ]);

    $this->actingAs(sharedUptdAccount())
        ->withSession(uptdContributorSession($contributor))
        ->get(route('uptd.dashboard'))
        ->assertOk()
        ->assertSeeInOrder(['Total Laporan', '2']);
});

it('menghitung toko penerima aktif di pasar kontributor', function () {
    $market = Market::factory()->create();
    $otherMarket = Market::factory()->create();
    $contributor = UptdContributor::factory()->create(['market_id' => $market->id]);

    RecipientStore::factory()->create([
        'market_id' => $market->id,
        'is_active' => true,
    ]);

    RecipientStore::factory()->create([
        'market_id' => $market->id,
        'is_active' => true,
    ]);

    RecipientStore::factory()->create([
        'market_id' => $market->id,
        'is_active' => false,
    ]);

    RecipientStore::factory()->create([
        'market_id' => $otherMarket->id,
        'is_active' => true,
    ]);

    $this->actingAs(sharedUptdAccount())
        ->withSession(uptdContributorSession($contributor))
        ->get(route('uptd.dashboard'))
        ->assertOk()
        ->assertSeeInOrder(['Toko Penerima', '2']);
});

it('menampilkan lima laporan pengawasan terbaru per halaman secara berurutan', function () {
    $market = Market::factory()->create();
    $contributor = UptdContributor::factory()->create([
        'name' => 'Kontributor UPTD 01',
        'market_id' => $market->id,
    ]);
    $otherContributor = UptdContributor::factory()->create();
    $otherMarket = Market::factory()->create();

    FieldDistributionReport::factory()->create([
        'market_id' => $otherMarket->id,
        'uptd_contributor_id' => $otherContributor->id,
        'report_date' => '2026-05-21',
        'condition' => FieldCondition::Normal,
    ]);

    foreach (['2026-05-20', '2026-05-19', '2026-05-18', '2026-05-17', '2026-05-16', '2026-05-15'] as $index => $date) {
        FieldDistributionReport::factory()->create([
            'market_id' => $market->id,
            'uptd_contributor_id' => $contributor->id,
            'report_date' => $date,
            'condition' => $index === 0 ? FieldCondition::PerluPerhatian : FieldCondition::Normal,
        ]);
    }

    $this->actingAs(sharedUptdAccount())
        ->withSession(uptdContributorSession($contributor))
        ->get(route('uptd.dashboard'))
        ->assertOk()
        ->assertSeeInOrder(['20/05/2026', '19/05/2026', '18/05/2026', '17/05/2026', '16/05/2026'])
        ->assertDontSee('15/05/2026')
        ->assertDontSee('21/05/2026')
        ->assertSee('Tanggal Pengawasan')
        ->assertSee('Menampilkan 5 dari 6 laporan')
        ->assertSee('page=2', false)
        ->assertSee('Perlu Perhatian')
        ->assertSee('Normal');

    $this->get(route('uptd.dashboard', ['page' => 2]))
        ->assertOk()
        ->assertSee('15/05/2026')
        ->assertDontSee('20/05/2026')
        ->assertSee('Menampilkan 1 dari 6 laporan');
});

it('menampilkan keadaan kosong saat belum ada laporan dan toko penerima', function () {
    $market = Market::factory()->create();
    $contributor = UptdContributor::factory()->create(['market_id' => $market->id]);

    $this->actingAs(sharedUptdAccount())
        ->withSession(uptdContributorSession($contributor))
        ->get(route('uptd.dashboard'))
        ->assertOk()
        ->assertSee('Belum ada laporan pengawasan')
        ->assertSeeInOrder(['Total Laporan', '0', 'Toko Penerima', '0'])
        ->assertDontSee('Laporan pengawasan kontributor ini')
        ->assertDontSee('Toko aktif di pasar ini');
});
