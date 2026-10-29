<?php

use App\Models\User;
use App\Services\DashboardSampleData;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('menampilkan sapaan dan tombol unduh pada dashboard monitoring', function () {
    $admin = User::factory()->create(['name' => 'Iqbal Shyhab']);

    $this->actingAs($admin)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('Selamat datang, Iqbal Shyhab')
        ->assertSee('Pantau penyaluran Minyakita secara terintegrasi')
        ->assertSee('Unduh Rekap Bulanan')
        ->assertSee('aria-disabled="true"', false)
        ->assertSee('title="Akan tersedia setelah modul laporan selesai"', false);
});

it('menampilkan empat kartu statistik dari data contoh', function () {
    $summary = app(DashboardSampleData::class)->summary(now()->format('Y-m'));
    $change = $summary['reports_change'];
    $changeText = $change === null
        ? 'Belum ada data bulan lalu'
        : ($change > 0 ? '+' : '').number_format($change, 1, ',', '.').'% dari bulan lalu';

    $this->actingAs(User::factory()->create())
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSeeInOrder([
            'Total Laporan',
            'Total Penyaluran',
            'Distributor Aktif',
            'Pasar Terpantau',
        ])
        ->assertSee(number_format($summary['cartons'], 0, ',', '.'))
        ->assertSee('karton pada '.$summary['month_label'])
        ->assertSee($summary['coverage_percent'].'% wilayah terpantau')
        ->assertSee($changeText);
});

it('menghasilkan data contoh yang stabil untuk bulan yang sama', function () {
    $data = app(DashboardSampleData::class);

    expect($data->summary('2026-10'))->toBe($data->summary('2026-10'));
});

it('menampilkan filter dengan pilihan default bulan berjalan', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('Semua Pasar')
        ->assertSee('Semua Distributor')
        ->assertSee('Terapkan Filter')
        ->assertSee('value="'.now()->format('Y-m').'" selected', false);
});

it('menerapkan filter bulan pada kartu statistik', function () {
    $data = app(DashboardSampleData::class);
    $previousMonth = now()->subMonthNoOverflow()->format('Y-m');
    $summary = $data->summary($previousMonth);

    expect($summary['cartons'])->not->toBe($data->summary(now()->format('Y-m'))['cartons']);

    $this->actingAs(User::factory()->create())
        ->get(route('admin.dashboard', ['month' => $previousMonth]))
        ->assertOk()
        ->assertSee(number_format($summary['cartons'], 0, ',', '.'))
        ->assertSee('karton pada '.$summary['month_label'])
        ->assertSee('value="'.$previousMonth.'" selected', false);
});

it('menerapkan filter pasar pada kartu statistik', function () {
    $data = app(DashboardSampleData::class);
    $month = now()->format('Y-m');
    $summary = $data->summary($month, 'pasar-raya');

    expect($summary['cartons'])->not->toBe($data->summary($month)['cartons']);

    $this->actingAs(User::factory()->create())
        ->get(route('admin.dashboard', ['market' => 'pasar-raya']))
        ->assertOk()
        ->assertSee(number_format($summary['cartons'], 0, ',', '.'))
        ->assertSee('value="pasar-raya" selected', false);
});

it('menerapkan filter distributor pada kartu statistik', function () {
    $data = app(DashboardSampleData::class);
    $month = now()->format('Y-m');
    $summary = $data->summary($month, null, 'bulog');

    expect($summary['cartons'])->not->toBe($data->summary($month)['cartons']);

    $this->actingAs(User::factory()->create())
        ->get(route('admin.dashboard', ['distributor' => 'bulog']))
        ->assertOk()
        ->assertSee(number_format($summary['cartons'], 0, ',', '.'))
        ->assertSee('value="bulog" selected', false);
});

it('kembali ke filter default saat nilai filter tidak valid', function () {
    $summary = app(DashboardSampleData::class)->summary(now()->format('Y-m'));

    $this->actingAs(User::factory()->create())
        ->get(route('admin.dashboard', ['month' => 'bukan-bulan', 'market' => 'pasar-tidak-ada', 'distributor' => 'tidak-ada']))
        ->assertOk()
        ->assertSee('karton pada '.$summary['month_label'])
        ->assertSee('value="'.now()->format('Y-m').'" selected', false);
});

it('menampilkan panel grafik perkembangan bulanan', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('Perkembangan Bulanan')
        ->assertSee('id="monthly-chart"', false)
        ->assertSee("type: 'line'", false);
});

it('membangun seri harian yang konsisten dengan total penyaluran', function () {
    $data = app(DashboardSampleData::class);
    $month = now()->format('Y-m');

    $series = $data->dailySeries($month);
    $summary = $data->summary($month);

    expect($series)->toHaveCount(now()->daysInMonth)
        ->and(array_sum($series))->toBe($summary['cartons'])
        ->and($data->dailySeries($month, null, 'bulog'))->not->toBe($series);
});

it('menampilkan panel penyaluran per distributor', function () {
    $data = app(DashboardSampleData::class);
    $month = now()->format('Y-m');
    $top = $data->distributorBreakdown($month)[0];

    $this->actingAs(User::factory()->create())
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('Perbandingan')
        ->assertSee('Penyaluran per Distributor')
        ->assertSee($top['name'])
        ->assertSee(number_format($top['cartons'], 0, ',', '.'))
        ->assertSee('Total Penyaluran '.$data->summary($month)['month_label']);
});

it('menyusun breakdown distributor menurun dan konsisten dengan total', function () {
    $data = app(DashboardSampleData::class);
    $month = now()->format('Y-m');
    $breakdown = $data->distributorBreakdown($month);
    $cartons = array_column($breakdown, 'cartons');

    expect(array_sum($cartons))->toBe($data->summary($month)['cartons'])
        ->and($cartons)->toBe(collect($cartons)->sortDesc()->values()->all());
});

it('membatasi panel distributor sesuai filter yang dipilih', function () {
    $data = app(DashboardSampleData::class);
    $month = now()->format('Y-m');

    expect($data->distributorBreakdown($month, null, 'bulog'))->toHaveCount(1)
        ->and($data->distributorBreakdown($month, 'pasar-raya'))->not->toBe($data->distributorBreakdown($month));
});
