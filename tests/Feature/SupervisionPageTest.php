<?php

use App\Enums\FieldCondition;
use App\Enums\Role;
use App\Models\FieldDistributionReport;
use App\Models\Market;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('mengarahkan tamu dari halaman pengawasan ke halaman login', function () {
    $this->get(route('admin.supervision.index'))->assertRedirect(route('login'));
});

it('menampilkan laporan kondisi lapangan dari database', function () {
    $contributor = User::factory()->create([
        'name' => 'Andi Pratama',
        'role' => Role::UptdPasar,
    ]);
    $market = Market::factory()->create(['name' => 'Pasar Raya Padang']);

    FieldDistributionReport::factory()->create([
        'market_id' => $market->id,
        'uptd_user_id' => $contributor->id,
        'report_date' => now()->toDateString(),
        'description' => 'Stok Minyakita tersedia, harga sesuai HET',
        'condition' => FieldCondition::Normal,
    ]);

    $this->actingAs(User::factory()->create())
        ->get(route('admin.supervision.index'))
        ->assertOk()
        ->assertSee('Laporan Kondisi Pasar')
        ->assertSee('KP-001')
        ->assertSee('Andi Pratama')
        ->assertSee('Pasar Raya Padang')
        ->assertSee('Stok Minyakita tersedia, harga sesuai HET')
        ->assertSee('Normal')
        ->assertSee(now()->format('d/m/Y'));
});

it('memfilter laporan berdasarkan teks, kondisi, pasar, dan bulan', function () {
    $firstContributor = User::factory()->create(['name' => 'Andi Pratama', 'role' => Role::UptdPasar]);
    $secondContributor = User::factory()->create(['name' => 'Rina Marlina', 'role' => Role::UptdPasar]);
    $firstMarket = Market::factory()->create(['name' => 'Pasar Alpha']);
    $secondMarket = Market::factory()->create(['name' => 'Pasar Beta']);

    FieldDistributionReport::factory()->create([
        'market_id' => $firstMarket->id,
        'uptd_user_id' => $firstContributor->id,
        'report_date' => now()->toDateString(),
        'description' => 'Stok aman',
        'condition' => FieldCondition::Normal,
    ]);

    FieldDistributionReport::factory()->create([
        'market_id' => $secondMarket->id,
        'uptd_user_id' => $secondContributor->id,
        'report_date' => now()->toDateString(),
        'description' => 'Stok menipis',
        'condition' => FieldCondition::PerluPerhatian,
    ]);

    FieldDistributionReport::factory()->create([
        'market_id' => $firstMarket->id,
        'uptd_user_id' => $firstContributor->id,
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
    $contributor = User::factory()->create(['role' => Role::UptdPasar]);
    $firstMarket = Market::factory()->create(['name' => 'Pasar Alpha']);
    $secondMarket = Market::factory()->create(['name' => 'Pasar Beta']);
    $thirdMarket = Market::factory()->create(['name' => 'Pasar Gamma']);
    $fourthMarket = Market::factory()->create(['name' => 'Pasar Delta']);

    FieldDistributionReport::factory()->create([
        'market_id' => $firstMarket->id,
        'uptd_user_id' => $contributor->id,
        'report_date' => now()->startOfMonth()->toDateString(),
        'condition' => FieldCondition::PerluPerhatian,
    ]);

    FieldDistributionReport::factory()->create([
        'market_id' => $firstMarket->id,
        'uptd_user_id' => $contributor->id,
        'report_date' => now()->startOfMonth()->addDay()->toDateString(),
        'condition' => FieldCondition::Normal,
    ]);

    FieldDistributionReport::factory()->create([
        'market_id' => $secondMarket->id,
        'uptd_user_id' => $contributor->id,
        'report_date' => now()->startOfMonth()->toDateString(),
        'condition' => FieldCondition::Normal,
    ]);

    FieldDistributionReport::factory()->create([
        'market_id' => $secondMarket->id,
        'uptd_user_id' => $contributor->id,
        'report_date' => now()->startOfMonth()->addDay()->toDateString(),
        'condition' => FieldCondition::PerluPerhatian,
    ]);

    FieldDistributionReport::factory()->create([
        'market_id' => $thirdMarket->id,
        'uptd_user_id' => $contributor->id,
        'report_date' => now()->startOfMonth()->toDateString(),
        'condition' => FieldCondition::Normal,
    ]);

    FieldDistributionReport::factory()->create([
        'market_id' => $thirdMarket->id,
        'uptd_user_id' => $contributor->id,
        'report_date' => now()->startOfMonth()->toDateString(),
        'condition' => FieldCondition::PerluPerhatian,
    ]);

    FieldDistributionReport::factory()->create([
        'market_id' => $fourthMarket->id,
        'uptd_user_id' => $contributor->id,
        'report_date' => now()->subMonthNoOverflow()->startOfMonth()->toDateString(),
        'condition' => FieldCondition::PerluPerhatian,
    ]);

    $this->actingAs(User::factory()->create())
        ->get(route('admin.supervision.index'))
        ->assertOk()
        ->assertSee('Jumlah Laporan')
        ->assertSee('Pasar Sudah Melapor')
        ->assertSee('Perlu Perhatian')
        ->assertSee('3 dari '.Market::count().' pasar')
        ->assertViewHas('summary', fn (array $summary): bool => $summary['reports'] === 6
            && $summary['reported_markets'] === 3
            && $summary['total_markets'] === Market::count()
            && $summary['needs_attention_markets'] === 2);
});

it('membatasi card total dan pasar dengan filter tetapi tidak card perlu perhatian', function () {
    $contributor = User::factory()->create(['role' => Role::UptdPasar]);
    $firstMarket = Market::factory()->create(['name' => 'Pasar Alpha']);
    $secondMarket = Market::factory()->create(['name' => 'Pasar Beta']);
    $previousMarket = Market::factory()->create(['name' => 'Pasar Gamma']);

    FieldDistributionReport::factory()->create([
        'market_id' => $firstMarket->id,
        'uptd_user_id' => $contributor->id,
        'report_date' => now()->startOfMonth()->toDateString(),
        'condition' => FieldCondition::Normal,
    ]);

    FieldDistributionReport::factory()->create([
        'market_id' => $secondMarket->id,
        'uptd_user_id' => $contributor->id,
        'report_date' => now()->startOfMonth()->toDateString(),
        'condition' => FieldCondition::PerluPerhatian,
    ]);

    FieldDistributionReport::factory()->create([
        'market_id' => $previousMarket->id,
        'uptd_user_id' => $contributor->id,
        'report_date' => now()->subMonthNoOverflow()->startOfMonth()->toDateString(),
        'condition' => FieldCondition::PerluPerhatian,
    ]);

    $this->actingAs(User::factory()->create());

    $this->get(route('admin.supervision.index', ['q' => 'Pasar Alpha']))
        ->assertOk()
        ->assertSee('1 dari '.Market::count().' pasar')
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

it('menampilkan bulan terpilih pada subtitle card jumlah laporan', function () {
    $this->travelTo('2026-11-15');

    $this->actingAs(User::factory()->create());

    $this->get(route('admin.supervision.index'))
        ->assertOk()
        ->assertSee('<p class="mt-1 text-xs text-slate-500">November 2026</p>', false)
        ->assertViewHas('month_label', 'November 2026');

    $this->get(route('admin.supervision.index', ['month' => '2026-10']))
        ->assertOk()
        ->assertSee('<p class="mt-1 text-xs text-slate-500">Oktober 2026</p>', false)
        ->assertViewHas('month_label', 'Oktober 2026');
});

it('menampilkan keadaan kosong saat belum ada laporan kondisi lapangan', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('admin.supervision.index'))
        ->assertOk()
        ->assertSee('Belum ada laporan kondisi lapangan');
});
