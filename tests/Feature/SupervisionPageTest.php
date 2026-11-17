<?php

use App\Enums\FieldCondition;
use App\Models\FieldDistributionReport;
use App\Models\Market;
use App\Models\UptdContributor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('mengarahkan tamu dari halaman pengawasan ke halaman login', function () {
    $this->get(route('admin.supervision.index'))->assertRedirect(route('login'));
});

it('menampilkan laporan pengawasan dari database', function () {
    $contributor = UptdContributor::factory()->create([
        'name' => 'Andi Pratama',
    ]);
    $market = Market::factory()->create(['name' => 'Pasar Raya Padang']);

    FieldDistributionReport::factory()->create([
        'market_id' => $market->id,
        'uptd_contributor_id' => $contributor->id,
        'report_date' => now()->toDateString(),
        'description' => 'Stok Minyakita tersedia, harga sesuai HET',
        'condition' => FieldCondition::Normal,
    ]);

    $this->actingAs(User::factory()->create())
        ->get(route('admin.supervision.index'))
        ->assertOk()
        ->assertSee('Laporan Pengawasan')
        ->assertSee('Keterangan')
        ->assertSee('Andi Pratama')
        ->assertSee('Pasar Raya Padang')
        ->assertSee('Stok Minyakita tersedia, harga sesuai HET')
        ->assertSee('Normal')
        ->assertSee(now()->format('d/m/Y'));
});

it('menampilkan dua tombol ekspor nonaktif pada laporan pengawasan', function () {
    $response = $this->actingAs(User::factory()->create())
        ->get(route('admin.supervision.index'));

    $response->assertOk()
        ->assertSeeInOrder(['Ekspor Excel', 'Ekspor PDF'])
        ->assertDontSee('akan tersedia segera');

    expect(substr_count($response->getContent(), 'aria-disabled="true"'))->toBe(2);
});

it('memfilter laporan berdasarkan teks, kondisi, pasar, dan bulan', function () {
    $firstContributor = UptdContributor::factory()->create(['name' => 'Andi Pratama']);
    $secondContributor = UptdContributor::factory()->create(['name' => 'Rina Marlina']);
    $firstMarket = Market::factory()->create(['name' => 'Pasar Alpha']);
    $secondMarket = Market::factory()->create(['name' => 'Pasar Beta']);

    FieldDistributionReport::factory()->create([
        'market_id' => $firstMarket->id,
        'uptd_contributor_id' => $firstContributor->id,
        'report_date' => now()->toDateString(),
        'description' => 'Stok aman',
        'condition' => FieldCondition::Normal,
    ]);

    FieldDistributionReport::factory()->create([
        'market_id' => $secondMarket->id,
        'uptd_contributor_id' => $secondContributor->id,
        'report_date' => now()->toDateString(),
        'description' => 'Stok menipis',
        'condition' => FieldCondition::PerluPerhatian,
    ]);

    FieldDistributionReport::factory()->create([
        'market_id' => $firstMarket->id,
        'uptd_contributor_id' => $firstContributor->id,
        'report_date' => now()->subMonthNoOverflow()->startOfMonth()->toDateString(),
        'description' => 'Laporan bulan lalu',
        'condition' => FieldCondition::Normal,
    ]);

    $admin = User::factory()->create();

    $this->actingAs($admin)
        ->get(route('admin.supervision.index', ['q' => 'Rina']))
        ->assertOk()
        ->assertSee('Stok menipis')
        ->assertDontSee('Stok aman');

    $this->get(route('admin.supervision.index', ['condition' => FieldCondition::PerluPerhatian->value]))
        ->assertOk()
        ->assertSee('Stok menipis')
        ->assertDontSee('Stok aman');

    $this->get(route('admin.supervision.index', ['q' => 'Pasar Beta']))
        ->assertOk()
        ->assertSee('Stok menipis')
        ->assertDontSee('Stok aman');

    $this->get(route('admin.supervision.index', [
        'month' => now()->subMonthNoOverflow()->format('Y-m'),
    ]))
        ->assertOk()
        ->assertSee('Laporan bulan lalu')
        ->assertDontSee('Stok aman');
});

it('menampilkan ringkasan total, pasar sudah melapor, dan perlu perhatian', function () {
    $contributor = UptdContributor::factory()->create();
    $firstMarket = Market::factory()->create(['name' => 'Pasar Alpha']);
    $secondMarket = Market::factory()->create(['name' => 'Pasar Beta']);
    $thirdMarket = Market::factory()->create(['name' => 'Pasar Gamma']);
    $fourthMarket = Market::factory()->create(['name' => 'Pasar Delta']);

    FieldDistributionReport::factory()->create([
        'market_id' => $firstMarket->id,
        'uptd_contributor_id' => $contributor->id,
        'report_date' => now()->startOfMonth()->toDateString(),
        'condition' => FieldCondition::PerluPerhatian,
    ]);

    FieldDistributionReport::factory()->create([
        'market_id' => $firstMarket->id,
        'uptd_contributor_id' => $contributor->id,
        'report_date' => now()->startOfMonth()->addDay()->toDateString(),
        'condition' => FieldCondition::Normal,
    ]);

    FieldDistributionReport::factory()->create([
        'market_id' => $secondMarket->id,
        'uptd_contributor_id' => $contributor->id,
        'report_date' => now()->startOfMonth()->toDateString(),
        'condition' => FieldCondition::Normal,
    ]);

    FieldDistributionReport::factory()->create([
        'market_id' => $secondMarket->id,
        'uptd_contributor_id' => $contributor->id,
        'report_date' => now()->startOfMonth()->addDay()->toDateString(),
        'condition' => FieldCondition::PerluPerhatian,
    ]);

    FieldDistributionReport::factory()->create([
        'market_id' => $thirdMarket->id,
        'uptd_contributor_id' => $contributor->id,
        'report_date' => now()->startOfMonth()->toDateString(),
        'condition' => FieldCondition::Normal,
    ]);

    FieldDistributionReport::factory()->create([
        'market_id' => $thirdMarket->id,
        'uptd_contributor_id' => $contributor->id,
        'report_date' => now()->startOfMonth()->toDateString(),
        'condition' => FieldCondition::PerluPerhatian,
    ]);

    FieldDistributionReport::factory()->create([
        'market_id' => $fourthMarket->id,
        'uptd_contributor_id' => $contributor->id,
        'report_date' => now()->subMonthNoOverflow()->startOfMonth()->toDateString(),
        'condition' => FieldCondition::PerluPerhatian,
    ]);

    $this->actingAs(User::factory()->create())
        ->get(route('admin.supervision.index'))
        ->assertOk()
        ->assertSee('Jumlah Laporan')
        ->assertSee('Pasar Sudah Melapor')
        ->assertSee('Perlu Perhatian')
        ->assertViewHas('summary', fn (array $summary): bool => $summary['reports'] === 6
            && $summary['reported_markets'] === 3
            && $summary['total_markets'] === Market::count()
            && $summary['needs_attention_markets'] === 2);
});

it('membatasi card total dan pasar dengan filter tetapi tidak card perlu perhatian', function () {
    $contributor = UptdContributor::factory()->create();
    $firstMarket = Market::factory()->create(['name' => 'Pasar Alpha']);
    $secondMarket = Market::factory()->create(['name' => 'Pasar Beta']);
    $previousMarket = Market::factory()->create(['name' => 'Pasar Gamma']);

    FieldDistributionReport::factory()->create([
        'market_id' => $firstMarket->id,
        'uptd_contributor_id' => $contributor->id,
        'report_date' => now()->startOfMonth()->toDateString(),
        'condition' => FieldCondition::Normal,
    ]);

    FieldDistributionReport::factory()->create([
        'market_id' => $secondMarket->id,
        'uptd_contributor_id' => $contributor->id,
        'report_date' => now()->startOfMonth()->toDateString(),
        'condition' => FieldCondition::PerluPerhatian,
    ]);

    FieldDistributionReport::factory()->create([
        'market_id' => $previousMarket->id,
        'uptd_contributor_id' => $contributor->id,
        'report_date' => now()->subMonthNoOverflow()->startOfMonth()->toDateString(),
        'condition' => FieldCondition::PerluPerhatian,
    ]);

    $this->actingAs(User::factory()->create());

    $this->get(route('admin.supervision.index', ['q' => 'Pasar Alpha']))
        ->assertOk()
        ->assertViewHas('summary', fn (array $summary): bool => $summary['reports'] === 1
            && $summary['reported_markets'] === 1
            && $summary['needs_attention_markets'] === 1);

    $this->get(route('admin.supervision.index', ['condition' => FieldCondition::Normal->value]))
        ->assertOk()
        ->assertViewHas('summary', fn (array $summary): bool => $summary['reports'] === 1
            && $summary['reported_markets'] === 1
            && $summary['needs_attention_markets'] === 1);

    $this->get(route('admin.supervision.index', ['month' => now()->subMonthNoOverflow()->format('Y-m')]))
        ->assertOk()
        ->assertViewHas('summary', fn (array $summary): bool => $summary['reports'] === 1
            && $summary['reported_markets'] === 1
            && $summary['needs_attention_markets'] === 1);
});

it('menampilkan keadaan kosong saat belum ada laporan pengawasan', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('admin.supervision.index'))
        ->assertOk()
        ->assertSee('Belum ada laporan pengawasan');
});
