<?php

use App\Enums\Role;
use App\Models\User;
use App\Notifications\DistributionReportDeleted;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

/**
 * Atur usia dan status baca sebuah notifikasi.
 */
function ageNotification(string $notificationId, int $months, bool $read): void
{
    DB::table('notifications')->where('id', $notificationId)->update([
        'created_at' => now()->subMonths($months),
        'read_at' => $read ? now() : null,
    ]);
}

it('menghapus semua notifikasi yang sudah berumur tiga bulan', function () {
    $admin = User::factory()->create(['role' => Role::Admin]);
    $kepalaDinas = User::factory()->create(['role' => Role::KepalaDinas]);
    $distributor = User::factory()->distributor()->create();
    $uptd = User::factory()->create(['role' => Role::UptdPasar]);

    $admin->notify(new DistributionReportDeleted('01/11/2026', 'PT Alpha'));
    $admin->notify(new DistributionReportDeleted('02/11/2026', 'PT Beta'));
    $kepalaDinas->notify(new DistributionReportDeleted('03/11/2026', 'PT Gamma'));
    $distributor->notify(new DistributionReportDeleted('04/11/2026', 'PT Delta'));
    $uptd->notify(new DistributionReportDeleted('05/11/2026', 'PT Epsilon'));

    $notifications = collect([
        $admin->notifications()->get()[0],
        $admin->notifications()->get()[1],
        $kepalaDinas->notifications()->firstOrFail(),
        $distributor->notifications()->firstOrFail(),
        $uptd->notifications()->firstOrFail(),
    ]);

    ageNotification($notifications[0]->id, 3, true);
    ageNotification($notifications[1]->id, 3, false);
    ageNotification($notifications[2]->id, 4, true);
    ageNotification($notifications[3]->id, 3, true);
    ageNotification($notifications[4]->id, 3, false);

    $this->artisan('notifications:prune-old')->assertSuccessful();

    $this->assertDatabaseCount('notifications', 0);
});

it('mempertahankan notifikasi yang belum berumur tiga bulan', function () {
    $admin = User::factory()->create(['role' => Role::Admin]);

    $admin->notify(new DistributionReportDeleted('01/11/2026', 'PT Alpha'));
    $admin->notify(new DistributionReportDeleted('02/11/2026', 'PT Beta'));

    $oldest = $admin->notifications()->firstOrFail();
    $newer = $admin->notifications()->skip(1)->firstOrFail();

    ageNotification($oldest->id, 2, true);
    ageNotification($newer->id, 1, false);

    $this->artisan('notifications:prune-old')->assertSuccessful();

    $this->assertDatabaseHas('notifications', ['id' => $oldest->id]);
    $this->assertDatabaseHas('notifications', ['id' => $newer->id]);
    $this->assertDatabaseCount('notifications', 2);
});
