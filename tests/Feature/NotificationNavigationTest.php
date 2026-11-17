<?php

use App\Enums\Role;
use App\Models\Distribution;
use App\Models\Distributor;
use App\Models\FieldDistributionReport;
use App\Models\Market;
use App\Models\RecipientStore;
use App\Models\UptdContributor;
use App\Models\User;
use App\Notifications\DistributionReportCreated;
use App\Notifications\DistributionReportDeleted;
use App\Notifications\DistributionReportUpdated;
use App\Notifications\FieldDistributionReportUpdated;
use App\Notifications\RecipientStoreCreated;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('menandai pemberitahuan dibaca dan mengarahkan admin ke penyaluran', function () {
    $admin = User::factory()->create(['role' => Role::Admin]);
    $distributor = Distributor::factory()->create(['name' => 'PT Rajawali Nusantara']);
    $market = Market::factory()->create();

    $distribution = Distribution::factory()->create([
        'distributor_id' => $distributor->id,
        'market_id' => $market->id,
        'uptd_user_id' => null,
        'distribution_date' => now()->toDateString(),
    ]);

    $admin->notify(new DistributionReportCreated(
        now()->format('d/m/Y'),
        $distributor->name,
        $distribution->id,
    ));

    $notification = $admin->notifications()->firstOrFail();

    $this->actingAs($admin)
        ->post(route('notifications.read', $notification))
        ->assertRedirect(route('admin.distribution.index', [
            'month' => now()->format('Y-m'),
            'highlight' => $distribution->id,
        ]));

    expect($notification->fresh()->read_at)->not->toBeNull();
});

it('mengarahkan kepala dinas ke menu penyaluran', function () {
    $kepalaDinas = User::factory()->create(['role' => Role::KepalaDinas]);
    $distributor = Distributor::factory()->create(['name' => 'PT Rajawali Nusantara']);
    $market = Market::factory()->create();

    $distribution = Distribution::factory()->create([
        'distributor_id' => $distributor->id,
        'market_id' => $market->id,
        'uptd_user_id' => null,
        'distribution_date' => now()->toDateString(),
    ]);

    $kepalaDinas->notify(new DistributionReportUpdated(
        now()->format('d/m/Y'),
        $distributor->name,
        $distribution->id,
    ));

    $notification = $kepalaDinas->notifications()->firstOrFail();

    $this->actingAs($kepalaDinas)
        ->post(route('notifications.read', $notification))
        ->assertRedirect(route('kepala-dinas.distribution.index', [
            'month' => now()->format('Y-m'),
            'highlight' => $distribution->id,
        ]));
});

it('mengarahkan laporan pengawasan ke menu pengawasan', function () {
    $admin = User::factory()->create(['role' => Role::Admin]);
    $market = Market::factory()->create(['name' => 'Pasar Raya Padang']);
    $contributor = UptdContributor::factory()->create([
        'name' => 'Kontributor UPTD 01',
        'market_id' => $market->id,
    ]);

    $report = FieldDistributionReport::factory()->create([
        'market_id' => $market->id,
        'uptd_contributor_id' => $contributor->id,
        'report_date' => now()->toDateString(),
    ]);

    $admin->notify(new FieldDistributionReportUpdated(
        $report->report_date->format('d/m/Y'),
        $market->name,
        $contributor->name,
        $report->id,
    ));

    $notification = $admin->notifications()->firstOrFail();

    $this->actingAs($admin)
        ->post(route('notifications.read', $notification))
        ->assertRedirect(route('admin.supervision.index', [
            'month' => now()->format('Y-m'),
            'highlight' => $report->id,
        ]));
});

it('mengarahkan notifikasi toko admin ke menu toko penerima', function () {
    $admin = User::factory()->create(['role' => Role::Admin]);
    $distributor = Distributor::factory()->create(['name' => 'PT Rajawali Nusantara']);
    $market = Market::factory()->create(['name' => 'Pasar Raya Padang']);

    $store = RecipientStore::factory()->create([
        'distributor_id' => $distributor->id,
        'market_id' => $market->id,
        'name' => 'Toko Baru',
    ]);

    $admin->notify(new RecipientStoreCreated(
        $store->name,
        $market->name,
        $distributor->name,
        $store->id,
    ));

    $notification = $admin->notifications()->firstOrFail();

    $this->actingAs($admin)
        ->post(route('notifications.read', $notification))
        ->assertRedirect(route('admin.recipient-stores.index', [
            'highlight' => $store->id,
        ]));
});

it('tidak membawa highlight untuk notifikasi penghapusan', function () {
    $admin = User::factory()->create(['role' => Role::Admin]);
    $distributor = Distributor::factory()->create(['name' => 'PT Rajawali Nusantara']);
    $market = Market::factory()->create();

    $distribution = Distribution::factory()->create([
        'distributor_id' => $distributor->id,
        'market_id' => $market->id,
        'uptd_user_id' => null,
        'distribution_date' => now()->toDateString(),
    ]);

    $admin->notify(new DistributionReportDeleted(
        now()->format('d/m/Y'),
        $distributor->name,
        $distribution->id,
    ));

    $notification = $admin->notifications()->firstOrFail();

    $this->actingAs($admin)
        ->post(route('notifications.read', $notification))
        ->assertRedirect(route('admin.distribution.index', [
            'month' => now()->format('Y-m'),
        ]));
});

it('tidak membawa highlight untuk notifikasi lama tanpa id', function () {
    $admin = User::factory()->create(['role' => Role::Admin]);

    $admin->notify(new DistributionReportCreated(
        now()->format('d/m/Y'),
        'PT Rajawali Nusantara',
    ));

    $notification = $admin->notifications()->firstOrFail();

    $this->actingAs($admin)
        ->post(route('notifications.read', $notification))
        ->assertRedirect(route('admin.distribution.index', [
            'month' => now()->format('Y-m'),
        ]));
});

it('menolak menandai pemberitahuan milik pengguna lain', function () {
    $admin = User::factory()->create(['role' => Role::Admin]);
    $otherAdmin = User::factory()->create(['role' => Role::Admin]);

    $otherAdmin->notify(new DistributionReportCreated(
        now()->format('d/m/Y'),
        'PT Rajawali Nusantara',
    ));

    $notification = $otherAdmin->notifications()->firstOrFail();

    $this->actingAs($admin)
        ->post(route('notifications.read', $notification))
        ->assertNotFound();

    expect($notification->fresh()->read_at)->toBeNull();
});

it('membuka halaman penyaluran yang memuat baris highlight', function () {
    $admin = User::factory()->create(['role' => Role::Admin]);
    $distributor = Distributor::factory()->create();
    $market = Market::factory()->create();

    $stores = collect(range(1, 11))->map(fn (int $index): RecipientStore => RecipientStore::factory()->create([
        'distributor_id' => $distributor->id,
        'market_id' => $market->id,
        'name' => "Toko {$index}",
    ]));

    $distributions = $stores->map(fn (RecipientStore $store, int $index) => Distribution::factory()->create([
        'distributor_id' => $distributor->id,
        'market_id' => $market->id,
        'uptd_user_id' => null,
        'recipient_store_id' => $store->id,
        'distribution_date' => now()->startOfMonth()->addDays($index)->toDateString(),
    ]));

    $oldest = $distributions->first();

    $this->actingAs($admin)
        ->get(route('admin.distribution.index', [
            'month' => now()->format('Y-m'),
            'highlight' => $oldest->id,
        ]))
        ->assertOk()
        ->assertSee('Toko 1')
        ->assertDontSee('Toko 2')
        ->assertSee('Menampilkan 1 dari 11 laporan')
        ->assertSee('data-highlighted-row', false)
        ->assertSee('notification-row-highlight', false);
});

it('membuka halaman pengawasan yang memuat baris highlight', function () {
    $admin = User::factory()->create(['role' => Role::Admin]);

    $contributors = collect(range(1, 11))->map(function (int $index): UptdContributor {
        $market = Market::factory()->create();

        return UptdContributor::factory()->create([
            'name' => "Kontributor {$index}",
            'market_id' => $market->id,
        ]);
    });

    $reports = $contributors->map(fn (UptdContributor $contributor, int $index) => FieldDistributionReport::factory()->create([
        'market_id' => $contributor->market_id,
        'uptd_contributor_id' => $contributor->id,
        'report_date' => now()->startOfMonth()->addDays($index)->toDateString(),
    ]));

    $oldest = $reports->first();

    $this->actingAs($admin)
        ->get(route('admin.supervision.index', [
            'month' => now()->format('Y-m'),
            'highlight' => $oldest->id,
        ]))
        ->assertOk()
        ->assertSee('Kontributor 1')
        ->assertDontSee('Kontributor 2')
        ->assertSee('Menampilkan 1 dari 11 laporan')
        ->assertSee('data-highlighted-row', false)
        ->assertSee('notification-row-highlight', false);
});

it('membuka halaman toko penerima yang memuat baris highlight', function () {
    $admin = User::factory()->create(['role' => Role::Admin]);
    $distributor = Distributor::factory()->create();
    $market = Market::factory()->create();

    $stores = collect(range(1, 11))->map(fn (int $index): RecipientStore => RecipientStore::factory()->create([
        'distributor_id' => $distributor->id,
        'market_id' => $market->id,
        'name' => "Toko {$index}",
    ]));

    $oldest = $stores->first();

    $this->actingAs($admin)
        ->get(route('admin.recipient-stores.index', [
            'highlight' => $oldest->id,
        ]))
        ->assertOk()
        ->assertSee('Toko 1')
        ->assertDontSee('Toko 2')
        ->assertSee('Menampilkan 1 dari 11 toko')
        ->assertSee('data-highlighted-row', false)
        ->assertSee('notification-row-highlight', false);
});
