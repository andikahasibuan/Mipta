<?php

use App\Enums\FieldCondition;
use App\Enums\Role;
use App\Models\FieldDistributionReport;
use App\Models\Market;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function uptdHistoryAccount(): array
{
    $market = Market::factory()->create(['name' => 'Pasar Raya Padang']);
    $contributor = User::factory()->create([
        'name' => 'Kontributor UPTD 01',
        'role' => Role::UptdPasar,
        'market_id' => $market->id,
    ]);

    return [$contributor, $market];
}

it('menampilkan riwayat laporan milik kontributor sendiri', function () {
    [$contributor, $market] = uptdHistoryAccount();
    $otherContributor = User::factory()->create([
        'role' => Role::UptdPasar,
        'market_id' => $market->id,
    ]);

    $report = FieldDistributionReport::factory()->create([
        'market_id' => $market->id,
        'uptd_user_id' => $contributor->id,
        'description' => 'Kondisi pasar tertib dan penyaluran lancar.',
    ]);

    FieldDistributionReport::factory()->create([
        'market_id' => $market->id,
        'uptd_user_id' => $otherContributor->id,
        'description' => 'Laporan milik kontributor lain.',
    ]);

    $this->actingAs($contributor)
        ->get(route('uptd.reports.index'))
        ->assertOk()
        ->assertSee('Laporan Kondisi Lapangan')
        ->assertSee('ID Laporan')
        ->assertSee($report->code)
        ->assertSee('Kontributor UPTD 01')
        ->assertSee('Pasar Raya Padang')
        ->assertSee('Deskripsi Kondisi')
        ->assertSee('Kondisi')
        ->assertSee('Aksi')
        ->assertSee('Terapkan Filter')
        ->assertSee('Semua Kondisi')
        ->assertSee('Kondisi pasar tertib dan penyaluran lancar.')
        ->assertDontSee('Laporan milik kontributor lain.')
        ->assertSee(route('uptd.reports.edit', $report), false)
        ->assertSee(route('uptd.reports.destroy', $report), false);
});

it('memfilter riwayat laporan berdasarkan bulan, kondisi, dan deskripsi', function () {
    [$contributor, $market] = uptdHistoryAccount();

    $currentReport = FieldDistributionReport::factory()->create([
        'market_id' => $market->id,
        'uptd_user_id' => $contributor->id,
        'report_date' => now()->toDateString(),
        'condition' => FieldCondition::Normal,
        'description' => 'Pasar bersih dan tertib.',
    ]);

    $previousMonth = now()->subMonthNoOverflow();
    $previousReport = FieldDistributionReport::factory()->create([
        'market_id' => $market->id,
        'uptd_user_id' => $contributor->id,
        'report_date' => $previousMonth->copy()->startOfMonth()->toDateString(),
        'condition' => FieldCondition::PerluPerhatian,
        'description' => 'Pasar kotor dan rusak.',
    ]);

    $this->actingAs($contributor);

    $this->get(route('uptd.reports.index'))
        ->assertOk()
        ->assertSee($currentReport->code)
        ->assertDontSee($previousReport->code);

    $this->get(route('uptd.reports.index', ['month' => $previousMonth->format('Y-m')]))
        ->assertOk()
        ->assertSee($previousReport->code)
        ->assertDontSee($currentReport->code);

    $this->get(route('uptd.reports.index', [
        'month' => now()->format('Y-m'),
        'condition' => FieldCondition::Normal->value,
    ]))
        ->assertOk()
        ->assertSee($currentReport->code)
        ->assertDontSee($previousReport->code);

    $this->get(route('uptd.reports.index', [
        'month' => $previousMonth->format('Y-m'),
        'q' => 'kotor',
    ]))
        ->assertOk()
        ->assertSee($previousReport->code)
        ->assertDontSee($currentReport->code);
});

it('menampilkan nilai lama pada formulir edit laporan', function () {
    [$contributor, $market] = uptdHistoryAccount();

    $report = FieldDistributionReport::factory()->create([
        'market_id' => $market->id,
        'uptd_user_id' => $contributor->id,
        'report_date' => '2026-05-20',
        'condition' => FieldCondition::PerluPerhatian,
        'description' => 'Kondisi pasar perlu perbaikan.',
    ]);

    $this->actingAs($contributor)
        ->get(route('uptd.reports.edit', $report))
        ->assertOk()
        ->assertSee('Edit Laporan Kondisi Lapangan')
        ->assertSee('value="2026-05-20"', false)
        ->assertSee('Kondisi pasar perlu perbaikan.')
        ->assertSee('Perbarui Laporan')
        ->assertSee('Batal');
});

it('memperbarui laporan kondisi lapangan milik kontributor', function () {
    [$contributor, $market] = uptdHistoryAccount();

    $report = FieldDistributionReport::factory()->create([
        'market_id' => $market->id,
        'uptd_user_id' => $contributor->id,
        'condition' => FieldCondition::Normal,
        'description' => 'Kondisi lama.',
    ]);

    $this->actingAs($contributor)
        ->put(route('uptd.reports.update', $report), [
            'report_date' => now()->toDateString(),
            'condition' => FieldCondition::PerluPerhatian->value,
            'description' => 'Kondisi diperbarui.',
        ])
        ->assertRedirect(route('uptd.reports.index'))
        ->assertSessionHas('success', 'Laporan kondisi lapangan berhasil diperbarui.');

    $report->refresh();

    expect($report->condition)->toBe(FieldCondition::PerluPerhatian)
        ->and($report->description)->toBe('Kondisi diperbarui.')
        ->and($report->market_id)->toBe($market->id)
        ->and($report->uptd_user_id)->toBe($contributor->id);
});

it('menolak pembaruan laporan kondisi lapangan yang tidak valid', function (array $overrides, string $field) {
    [$contributor, $market] = uptdHistoryAccount();

    $report = FieldDistributionReport::factory()->create([
        'market_id' => $market->id,
        'uptd_user_id' => $contributor->id,
        'description' => 'Kondisi awal.',
    ]);

    $payload = array_merge([
        'report_date' => now()->toDateString(),
        'condition' => FieldCondition::Normal->value,
        'description' => 'Kondisi aman.',
    ], $overrides);

    $this->actingAs($contributor)
        ->put(route('uptd.reports.update', $report), $payload)
        ->assertSessionHasErrors($field);

    expect($report->fresh()->description)->toBe('Kondisi awal.');
})->with([
    'tanggal kosong' => [['report_date' => ''], 'report_date'],
    'tanggal masa depan' => [['report_date' => now()->addDay()->toDateString()], 'report_date'],
    'kondisi tidak valid' => [['condition' => 'rusak'], 'condition'],
    'deskripsi kosong' => [['description' => ''], 'description'],
]);

it('menolak mengedit atau memperbarui laporan milik kontributor lain', function () {
    [, $market] = uptdHistoryAccount();
    $otherContributor = User::factory()->create([
        'role' => Role::UptdPasar,
        'market_id' => $market->id,
    ]);

    $report = FieldDistributionReport::factory()->create([
        'market_id' => $market->id,
        'uptd_user_id' => $otherContributor->id,
        'description' => 'Laporan kontributor lain.',
    ]);

    $this->actingAs(User::factory()->create([
        'role' => Role::UptdPasar,
        'market_id' => $market->id,
    ]))
        ->get(route('uptd.reports.edit', $report))
        ->assertNotFound();

    $this->put(route('uptd.reports.update', $report), [
        'report_date' => now()->toDateString(),
        'condition' => FieldCondition::Normal->value,
        'description' => 'Percobaan ubah.',
    ])->assertNotFound();

    expect($report->fresh()->description)->toBe('Laporan kontributor lain.');
});

it('menghapus laporan dan mengirim notifikasi ke admin aktif', function () {
    [$contributor, $market] = uptdHistoryAccount();
    $admin = User::factory()->create(['role' => Role::Admin, 'is_active' => true]);
    $inactiveAdmin = User::factory()->create(['role' => Role::Admin, 'is_active' => false]);
    $kepalaDinas = User::factory()->create(['role' => Role::KepalaDinas]);

    $report = FieldDistributionReport::factory()->create([
        'market_id' => $market->id,
        'uptd_user_id' => $contributor->id,
        'report_date' => '2026-05-20',
        'condition' => FieldCondition::PerluPerhatian,
        'description' => 'Laporan yang akan dihapus.',
    ]);

    $reportCode = $report->code;

    $this->actingAs($contributor)
        ->delete(route('uptd.reports.destroy', $report))
        ->assertRedirect(route('uptd.reports.index'))
        ->assertSessionHas('success', 'Laporan kondisi lapangan berhasil dihapus.');

    $this->assertDatabaseMissing('field_distribution_reports', ['id' => $report->id]);
    $this->assertDatabaseCount('notifications', 1);

    $expectedMessage = sprintf(
        'Laporan kondisi lapangan %s tanggal 20/05/2026 di Pasar Raya Padang telah dihapus oleh Kontributor UPTD 01.',
        $reportCode,
    );

    expect($admin->notifications()->first()?->data['title'])->toBe('Laporan kondisi lapangan dihapus')
        ->and($admin->notifications()->first()?->data['message'])->toBe($expectedMessage)
        ->and($inactiveAdmin->notifications()->count())->toBe(0)
        ->and($kepalaDinas->notifications()->count())->toBe(0)
        ->and($contributor->notifications()->count())->toBe(0);
});

it('menolak menghapus laporan milik kontributor lain', function () {
    [, $market] = uptdHistoryAccount();
    $otherContributor = User::factory()->create([
        'role' => Role::UptdPasar,
        'market_id' => $market->id,
    ]);

    $report = FieldDistributionReport::factory()->create([
        'market_id' => $market->id,
        'uptd_user_id' => $otherContributor->id,
    ]);

    $this->actingAs(User::factory()->create([
        'role' => Role::UptdPasar,
        'market_id' => $market->id,
    ]))
        ->delete(route('uptd.reports.destroy', $report))
        ->assertNotFound();

    $this->assertDatabaseHas('field_distribution_reports', ['id' => $report->id]);
});

it('mengarahkan tamu dari aksi riwayat laporan ke login', function () {
    $report = FieldDistributionReport::factory()->create();

    $this->get(route('uptd.reports.edit', $report))->assertRedirect(route('login'));
    $this->put(route('uptd.reports.update', $report), [])->assertRedirect(route('login'));
    $this->delete(route('uptd.reports.destroy', $report))->assertRedirect(route('login'));
});

it('menolak peran selain kontributor UPTD dari aksi riwayat laporan', function () {
    $report = FieldDistributionReport::factory()->create();
    $admin = User::factory()->create(['role' => Role::Admin]);

    $this->actingAs($admin)->get(route('uptd.reports.edit', $report))->assertForbidden();
    $this->actingAs($admin)->put(route('uptd.reports.update', $report), [])->assertForbidden();
    $this->actingAs($admin)->delete(route('uptd.reports.destroy', $report))->assertForbidden();
});
