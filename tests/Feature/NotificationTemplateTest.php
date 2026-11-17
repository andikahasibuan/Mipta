<?php

use App\Enums\Role;
use App\Models\User;
use App\Notifications\DistributionReportCreated;
use App\Notifications\DistributionReportDeleted;
use App\Notifications\RecipientStoreCreated;
use App\Services\NotificationTemplateRenderer;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('merender template notifikasi dengan placeholder', function () {
    $renderer = app(NotificationTemplateRenderer::class);

    expect($renderer->render('recipient_store_created', [
        'nama toko' => 'Toko Baru',
        'nama distributor' => 'PT Rajawali Nusantara',
    ]))->toBe([
        'title' => 'Toko penerima baru',
        'message' => 'Toko Toko Baru telah ditambahkan oleh PT Rajawali Nusantara.',
    ]);

    expect($renderer->render('field_distribution_report_deleted', [
        'tanggal' => '20/05/2026',
        'nama kontributor pasar' => 'Kontributor UPTD 01',
    ]))->toBe([
        'title' => 'Laporan pengawasan telah dihapus',
        'message' => 'Laporan pengawasan tanggal 20/05/2026 telah dihapus oleh Kontributor UPTD 01.',
    ]);
});

it('memakai template terbaru saat membuat notifikasi baru', function () {
    $admin = User::factory()->create(['role' => Role::Admin]);

    $admin->notify(new DistributionReportCreated('09/11/2026', 'PT Rajawali Nusantara', 99));

    $notification = $admin->notifications()->firstOrFail();

    expect($notification->data['title'])->toBe('Laporan penyaluran baru')
        ->and($notification->data['message'])->toBe('Ada laporan baru dari PT Rajawali Nusantara.')
        ->and($notification->data['distribution_id'])->toBe(99);
});

it('memperbarui teks notifikasi lama', function () {
    $admin = User::factory()->create(['role' => Role::Admin]);

    $admin->notify(new DistributionReportDeleted('09/11/2026', 'PT Rajawali Nusantara'));

    $notification = $admin->notifications()->firstOrFail();

    $notification->forceFill([
        'data' => [
            'title' => 'Laporan penyaluran dihapus',
            'message' => 'Laporan penyaluran tanggal 09/11/2026 telah dihapus oleh PT Rajawali Nusantara.',
            'distribution_date' => '09/11/2026',
            'distributor_name' => 'PT Rajawali Nusantara',
            'distribution_id' => null,
        ],
    ])->save();

    $this->artisan('notifications:refresh-text')->assertSuccessful();

    $notification->refresh();

    expect($notification->data['title'])->toBe('Laporan penyaluran telah dihapus')
        ->and($notification->data['message'])->toBe('Laporan penyaluran tanggal 09/11/2026 telah dihapus oleh PT Rajawali Nusantara.');
});

it('menghapus notifikasi toko lama milik kepala dinas', function () {
    $admin = User::factory()->create(['role' => Role::Admin]);
    $kepalaDinas = User::factory()->create(['role' => Role::KepalaDinas]);

    $admin->notify(new RecipientStoreCreated('Toko Baru', 'Pasar Raya Padang', 'PT Rajawali Nusantara', 1));
    $kepalaDinas->notify(new RecipientStoreCreated('Toko Baru', 'Pasar Raya Padang', 'PT Rajawali Nusantara', 1));

    $this->artisan('notifications:cleanup-store-recipients')->assertSuccessful();

    expect($admin->notifications()->count())->toBe(1)
        ->and($kepalaDinas->notifications()->count())->toBe(0);
});
