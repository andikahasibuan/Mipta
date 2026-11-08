<?php

use App\Enums\FieldCondition;
use App\Enums\Role;
use App\Models\Distribution;
use App\Models\FieldDistributionReport;
use App\Models\Market;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

dataset('kepala dinas pages', [
    'dashboard' => ['kepala-dinas.dashboard', 'Dashboard Monitoring'],
    'distribution' => ['kepala-dinas.distribution.index', 'Laporan Penyaluran'],
    'supervision' => ['kepala-dinas.supervision.index', 'Monitoring Kondisi Pasar'],
    'reports' => ['kepala-dinas.reports.index', 'Rekapitulasi Laporan'],
]);

it('menampilkan halaman kepala dinas dengan menu yang sesuai aktif', function (string $routeName, string $label) {
    $response = $this->actingAs(User::factory()->create(['role' => Role::KepalaDinas]))
        ->get(route($routeName));

    $response->assertOk()
        ->assertSee('<title>'.e($label).'</title>', false);

    $html = $response->getContent();

    preg_match('/<nav[^>]*aria-label="Menu utama".*?<\/nav>/s', $html, $matches);
    $sidebar = $matches[0] ?? '';

    expect(substr_count($sidebar, 'aria-current="page"'))->toBe(1)
        ->and($sidebar)->toMatch('/aria-current="page"[^>]*>(?:(?!<\/a>).)*'.preg_quote(e($label), '/').'/s');
})->with('kepala dinas pages');

it('mengarahkan tamu dari halaman kepala dinas ke login', function (string $routeName) {
    $this->get(route($routeName))->assertRedirect(route('login'));
})->with('kepala dinas pages');

it('menolak admin dari halaman kepala dinas', function (string $routeName) {
    $this->actingAs(User::factory()->create())
        ->get(route($routeName))
        ->assertForbidden();
})->with('kepala dinas pages');

dataset('admin pages', [
    'dashboard' => ['admin.dashboard'],
    'users' => ['admin.users.index'],
    'distributors' => ['admin.distributors.index'],
    'markets' => ['admin.markets.index'],
    'stores' => ['admin.recipient-stores.index'],
    'uptd' => ['admin.uptd-contributors.index'],
    'distribution' => ['admin.distribution.index'],
    'monitoring' => ['admin.monitoring.index'],
    'supervision' => ['admin.supervision.index'],
    'reports' => ['admin.reports.index'],
]);

it('menolak kepala dinas dari seluruh halaman admin', function (string $routeName) {
    $this->actingAs(User::factory()->create(['role' => Role::KepalaDinas]))
        ->get(route($routeName))
        ->assertForbidden();
})->with('admin pages');

it('mengarahkan kepala dinas dari halaman utama ke dashboard kepala dinas', function () {
    $this->actingAs(User::factory()->create(['role' => Role::KepalaDinas]))
        ->get('/')
        ->assertRedirect(route('kepala-dinas.dashboard'));
});

it('mengarahkan admin dari halaman utama ke dashboard admin', function () {
    $this->actingAs(User::factory()->create())
        ->get('/')
        ->assertRedirect(route('admin.dashboard'));
});

it('menampilkan konten dashboard untuk kepala dinas', function () {
    $this->actingAs(User::factory()->create(['role' => Role::KepalaDinas]))
        ->get(route('kepala-dinas.dashboard'))
        ->assertOk()
        ->assertSee('Selamat datang')
        ->assertSee('Total Laporan');
});

it('menampilkan tabel laporan penyaluran tanpa aksi hapus untuk kepala dinas', function () {
    $this->actingAs(User::factory()->create(['role' => Role::KepalaDinas]))
        ->get(route('kepala-dinas.distribution.index'))
        ->assertOk()
        ->assertSeeInOrder(['ID Data', 'Tanggal', 'Pasar', 'Nama Toko', 'Distributor', 'Jumlah Karton'])
        ->assertSee('action="'.route('kepala-dinas.distribution.index').'"', false)
        ->assertDontSee('aria-label="Hapus"', false)
        ->assertDontSee('Aksi');
});

it('menampilkan laporan kondisi lapangan untuk kepala dinas', function () {
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

    $this->actingAs(User::factory()->create(['role' => Role::KepalaDinas]))
        ->get(route('kepala-dinas.supervision.index'))
        ->assertOk()
        ->assertSee('Monitoring Kondisi Pasar')
        ->assertSee('Laporan Kondisi Lapangan')
        ->assertSee('KP-001')
        ->assertSee('Andi Pratama')
        ->assertSee('Pasar Raya Padang')
        ->assertSee('Stok Minyakita tersedia, harga sesuai HET')
        ->assertSee('action="'.route('kepala-dinas.supervision.index').'"', false);
});

it('memfilter laporan kondisi lapangan untuk kepala dinas', function () {
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

    $this->actingAs(User::factory()->create(['role' => Role::KepalaDinas]))
        ->get(route('kepala-dinas.supervision.index', ['q' => 'Rina']))
        ->assertOk()
        ->assertSee('Stok menipis')
        ->assertDontSee('Stok aman');
});

it('menampilkan keadaan kosong laporan kondisi lapangan untuk kepala dinas', function () {
    $this->actingAs(User::factory()->create(['role' => Role::KepalaDinas]))
        ->get(route('kepala-dinas.supervision.index'))
        ->assertOk()
        ->assertSee('Belum ada laporan kondisi lapangan');
});

it('menampilkan rekapitulasi laporan untuk kepala dinas', function () {
    $uptd = User::factory()->create(['role' => Role::UptdPasar]);
    $market = Market::factory()->create(['name' => 'Pasar Raya Padang']);

    Distribution::factory()->create([
        'market_id' => $market->id,
        'uptd_user_id' => $uptd->id,
        'distribution_date' => now()->toDateString(),
        'cartons' => 120,
    ]);

    $this->actingAs(User::factory()->create(['role' => Role::KepalaDinas]))
        ->get(route('kepala-dinas.reports.index'))
        ->assertOk()
        ->assertSee('Rekapitulasi Laporan')
        ->assertSee('Laporan Penyaluran')
        ->assertSee('Jumlah Penyaluran')
        ->assertSee('Rekap Penyaluran per Pasar')
        ->assertSee('Pasar Raya Padang')
        ->assertSee('action="'.route('kepala-dinas.reports.index').'"', false)
        ->assertSee(route('kepala-dinas.reports.index', [
            'start_date' => now()->startOfMonth()->toDateString(),
            'end_date' => now()->endOfMonth()->toDateString(),
            'tab' => 'kondisi',
        ]));
});

it('menampilkan rekap kondisi pasar pada tab kedua untuk kepala dinas', function () {
    $uptd = User::factory()->create(['name' => 'Andi Pratama', 'role' => Role::UptdPasar]);
    $market = Market::factory()->create(['name' => 'Pasar Raya Padang']);

    FieldDistributionReport::factory()->create([
        'market_id' => $market->id,
        'uptd_user_id' => $uptd->id,
        'report_date' => now()->toDateString(),
        'description' => 'Stok Minyakita menipis di beberapa kios',
        'condition' => FieldCondition::PerluPerhatian,
    ]);

    $this->actingAs(User::factory()->create(['role' => Role::KepalaDinas]))
        ->get(route('kepala-dinas.reports.index', ['tab' => 'kondisi']))
        ->assertOk()
        ->assertSee('Rekap Kondisi Pasar')
        ->assertSee('Pasar Raya Padang')
        ->assertSee('Stok Minyakita menipis di beberapa kios')
        ->assertSee('Perlu Perhatian');
});

it('menampilkan dua tombol ekspor nonaktif untuk kepala dinas', function () {
    $response = $this->actingAs(User::factory()->create(['role' => Role::KepalaDinas]))
        ->get(route('kepala-dinas.reports.index'));

    $response->assertOk()
        ->assertSeeInOrder(['Ekspor Excel', 'Ekspor PDF'])
        ->assertDontSee('akan tersedia segera');

    expect(substr_count($response->getContent(), 'aria-disabled="true"'))->toBe(2);
});
