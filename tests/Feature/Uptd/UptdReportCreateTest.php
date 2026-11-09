<?php

use App\Enums\FieldCondition;
use App\Enums\Role;
use App\Models\FieldDistributionReport;
use App\Models\Market;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('menampilkan formulir laporan kondisi lapangan', function () {
    $market = Market::factory()->create(['name' => 'Pasar Raya Padang']);
    $contributor = User::factory()->create([
        'name' => 'Kontributor UPTD 01',
        'role' => Role::UptdPasar,
        'market_id' => $market->id,
    ]);

    $this->actingAs($contributor)
        ->get(route('uptd.reports.create'))
        ->assertOk()
        ->assertSee('Input Laporan Kondisi Lapangan')
        ->assertSee('Kontributor UPTD 01')
        ->assertSee('Pasar Raya Padang')
        ->assertSee('Terisi otomatis')
        ->assertSee('Tidak dapat diubah')
        ->assertSee('Normal')
        ->assertSee('Perlu Perhatian')
        ->assertSee('Simpan Laporan')
        ->assertSee('Batal')
        ->assertDontSee('Batal / Kembali');
});

it('menyimpan laporan kondisi lapangan milik kontributor yang login', function () {
    $market = Market::factory()->create();
    $contributor = User::factory()->create([
        'role' => Role::UptdPasar,
        'market_id' => $market->id,
    ]);

    $this->actingAs($contributor)
        ->post(route('uptd.reports.store'), [
            'report_date' => '2026-05-20',
            'condition' => FieldCondition::Normal->value,
            'description' => 'Kondisi pasar normal dan penyaluran lancar.',
        ])
        ->assertRedirect(route('uptd.dashboard'))
        ->assertSessionHas('success', 'Laporan kondisi lapangan berhasil disimpan.');

    $report = FieldDistributionReport::query()->firstOrFail();

    expect($report->market_id)->toBe($market->id)
        ->and($report->uptd_user_id)->toBe($contributor->id)
        ->and($report->report_date->toDateString())->toBe('2026-05-20')
        ->and($report->condition)->toBe(FieldCondition::Normal)
        ->and($report->description)->toBe('Kondisi pasar normal dan penyaluran lancar.');

    $this->actingAs($contributor)
        ->get(route('uptd.dashboard'))
        ->assertOk()
        ->assertSee('Laporan kondisi lapangan berhasil disimpan.')
        ->assertSee('20 Mei 2026');
});

it('menolak laporan kondisi lapangan yang tidak valid', function (array $overrides, string $field) {
    $market = Market::factory()->create();
    $contributor = User::factory()->create([
        'role' => Role::UptdPasar,
        'market_id' => $market->id,
    ]);

    $payload = array_merge([
        'report_date' => now()->toDateString(),
        'condition' => FieldCondition::Normal->value,
        'description' => 'Kondisi pasar normal.',
    ], $overrides);

    $this->actingAs($contributor)
        ->post(route('uptd.reports.store'), $payload)
        ->assertSessionHasErrors($field);

    expect(FieldDistributionReport::count())->toBe(0);
})->with([
    'tanggal kosong' => [['report_date' => ''], 'report_date'],
    'tanggal masa depan' => [['report_date' => now()->addDay()->toDateString()], 'report_date'],
    'kondisi tidak valid' => [['condition' => 'rusak'], 'condition'],
    'deskripsi kosong' => [['description' => ''], 'description'],
]);

it('mengarahkan tamu dari penyimpanan laporan ke login', function () {
    $this->post(route('uptd.reports.store'), [])
        ->assertRedirect(route('login'));
});

it('menolak peran selain kontributor UPTD saat menyimpan laporan', function () {
    $this->actingAs(User::factory()->create())
        ->post(route('uptd.reports.store'), [])
        ->assertForbidden();
});

it('menolak kontributor UPTD tanpa pasar saat menyimpan laporan', function () {
    $this->actingAs(User::factory()->create(['role' => Role::UptdPasar]))
        ->post(route('uptd.reports.store'), [])
        ->assertForbidden();
});
