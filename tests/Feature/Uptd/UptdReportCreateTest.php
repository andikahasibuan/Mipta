<?php

use App\Enums\FieldCondition;
use App\Enums\Role;
use App\Models\FieldDistributionReport;
use App\Models\Market;
use App\Models\UptdContributor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('menampilkan formulir laporan pengawasan', function () {
    $market = Market::factory()->create(['name' => 'Pasar Raya Padang']);
    $contributor = UptdContributor::factory()->create([
        'name' => 'Kontributor UPTD 01',
        'market_id' => $market->id,
    ]);

    $this->actingAs(sharedUptdAccount())
        ->withSession(uptdContributorSession($contributor))
        ->get(route('uptd.reports.create'))
        ->assertOk()
        ->assertSee('Input Laporan')
        ->assertSee('Kontributor UPTD 01')
        ->assertSee('Pasar Raya Padang')
        ->assertSee('Normal')
        ->assertSee('Perlu Perhatian')
        ->assertSee('Simpan Laporan')
        ->assertSee('Batal')
        ->assertDontSee('Batal / Kembali')
        ->assertSee('value="'.now()->format('d/m/Y').'"', false);
});

it('menyimpan laporan pengawasan dengan tanggal hari ini dan mengabaikan tanggal mundur', function () {
    $market = Market::factory()->create();
    $contributor = UptdContributor::factory()->create(['market_id' => $market->id]);
    $today = now()->toDateString();

    $this->actingAs(sharedUptdAccount())
        ->withSession(uptdContributorSession($contributor))
        ->post(route('uptd.reports.store'), [
            'report_date' => now()->subDays(3)->toDateString(),
            'condition' => FieldCondition::Normal->value,
            'description' => 'Kondisi pasar normal dan penyaluran lancar.',
        ])
        ->assertRedirect(route('uptd.dashboard'))
        ->assertSessionHas('success', 'Laporan pengawasan berhasil disimpan.');

    $report = FieldDistributionReport::query()->firstOrFail();

    expect($report->market_id)->toBe($market->id)
        ->and($report->uptd_contributor_id)->toBe($contributor->id)
        ->and($report->report_date->toDateString())->toBe($today)
        ->and($report->condition)->toBe(FieldCondition::Normal)
        ->and($report->description)->toBe('Kondisi pasar normal dan penyaluran lancar.');

    $this->get(route('uptd.dashboard'))
        ->assertOk()
        ->assertSee('Laporan pengawasan berhasil disimpan.')
        ->assertSee(now()->format('d/m/Y'));
});

it('mengirim notifikasi laporan pengawasan baru ke admin dan kepala dinas', function () {
    $market = Market::factory()->create(['name' => 'Pasar Raya Padang']);
    $contributor = UptdContributor::factory()->create([
        'name' => 'Kontributor UPTD 01',
        'market_id' => $market->id,
    ]);

    $shared = sharedUptdAccount();
    $admin = User::factory()->create(['role' => Role::Admin]);
    $kepalaDinas = User::factory()->create(['role' => Role::KepalaDinas]);
    $inactiveAdmin = User::factory()->inactive()->create(['role' => Role::Admin]);
    $distributor = User::factory()->distributor()->create();

    $this->actingAs($shared)
        ->withSession(uptdContributorSession($contributor))
        ->post(route('uptd.reports.store'), [
            'condition' => FieldCondition::Normal->value,
            'description' => 'Laporan pengawasan baru.',
        ])
        ->assertRedirect(route('uptd.dashboard'));

    $expectedMessage = 'Ada laporan baru dari Kontributor UPTD 01.';

    $this->assertDatabaseCount('notifications', 2);

    expect($admin->notifications()->first()?->data['title'])->toBe('Laporan pengawasan baru')
        ->and($admin->notifications()->first()?->data['message'])->toBe($expectedMessage)
        ->and($kepalaDinas->notifications()->first()?->data['message'])->toBe($expectedMessage)
        ->and($inactiveAdmin->notifications()->count())->toBe(0)
        ->and($distributor->notifications()->count())->toBe(0)
        ->and($shared->notifications()->count())->toBe(0);
});

it('menolak laporan pengawasan yang tidak valid', function (array $overrides, string $field) {
    $market = Market::factory()->create();
    $contributor = UptdContributor::factory()->create(['market_id' => $market->id]);

    $payload = array_merge([
        'condition' => FieldCondition::Normal->value,
        'description' => 'Kondisi pasar normal.',
    ], $overrides);

    $this->actingAs(sharedUptdAccount())
        ->withSession(uptdContributorSession($contributor))
        ->post(route('uptd.reports.store'), $payload)
        ->assertSessionHasErrors($field);

    expect(FieldDistributionReport::count())->toBe(0);
})->with([
    'kondisi tidak valid' => [['condition' => 'rusak'], 'condition'],
    'deskripsi kosong' => [['description' => ''], 'description'],
    'deskripsi hanya angka' => [['description' => '12345'], 'description'],
    'deskripsi hanya simbol' => [['description' => '!!!'], 'description'],
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

it('mengarahkan akun bersama UPTD tanpa identitas terpilih saat menyimpan laporan ke pemilihan identitas', function () {
    $this->actingAs(User::factory()->create(['role' => Role::UptdPasar]))
        ->post(route('uptd.reports.store'), [])
        ->assertRedirect(route('uptd.contributors.index'));
});
