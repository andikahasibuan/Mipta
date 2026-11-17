<?php

use App\Enums\Role;
use App\Models\User;
use App\Notifications\DistributionReportDeleted;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

afterEach(function () {
    Carbon::setTestNow();
});

it('menampilkan riwayat pemberitahuan milik pengguna', function () {
    $admin = User::factory()->create(['role' => Role::Admin]);
    $otherAdmin = User::factory()->create(['role' => Role::Admin]);

    $admin->notify(new DistributionReportDeleted('09/11/2026', 'PT Rajawali Nusantara'));
    $otherAdmin->notify(new DistributionReportDeleted('10/11/2026', 'PT Beta Pangan'));

    $response = $this->actingAs($admin)
        ->get(route('notifications.index'))
        ->assertOk()
        ->assertSee('Pemberitahuan')
        ->assertSee('Laporan penyaluran tanggal 09/11/2026 telah dihapus oleh PT Rajawali Nusantara.')
        ->assertDontSee('Laporan penyaluran tanggal 10/11/2026 telah dihapus oleh PT Beta Pangan.')
        ->assertSee('data-notification-read="false"', false)
        ->assertSee('data-notification-card', false)
        ->assertDontSee('Belum dibaca')
        ->assertDontSee('Sudah dibaca')
        ->assertSee('Tandai semua telah dibaca');

    expect(preg_match('/<h1[^>]*>\s*Notifikasi\s*<\/h1>/s', $response->getContent()))->toBe(1);
});

it('menampilkan riwayat pemberitahuan untuk kepala dinas', function () {
    $kepalaDinas = User::factory()->create(['role' => Role::KepalaDinas]);

    $kepalaDinas->notify(new DistributionReportDeleted('09/11/2026', 'PT Rajawali Nusantara'));

    $this->actingAs($kepalaDinas)
        ->get(route('notifications.index'))
        ->assertOk()
        ->assertSee('Pemberitahuan')
        ->assertSee('Laporan penyaluran tanggal 09/11/2026 telah dihapus oleh PT Rajawali Nusantara.')
        ->assertSee('Tandai semua telah dibaca');
});

it('membatasi halaman riwayat pemberitahuan untuk admin dan kepala dinas', function () {
    $this->get(route('notifications.index'))->assertRedirect(route('login'));

    $this->actingAs(User::factory()->distributor()->create())
        ->get(route('notifications.index'))
        ->assertForbidden();

    $this->actingAs(User::factory()->create(['role' => Role::UptdPasar]))
        ->get(route('notifications.index'))
        ->assertForbidden();
});

it('menandai seluruh pemberitahuan sebagai dibaca dari halaman riwayat', function () {
    $admin = User::factory()->create(['role' => Role::Admin]);

    $admin->notify(new DistributionReportDeleted('01/11/2026', 'PT Alpha'));
    $admin->notify(new DistributionReportDeleted('02/11/2026', 'PT Beta'));

    $this->actingAs($admin)
        ->from(route('notifications.index'))
        ->post(route('notifications.read-all'))
        ->assertRedirect(route('notifications.index'))
        ->assertSessionHas('success', 'Semua notifikasi telah ditandai dibaca.');

    expect($admin->fresh()->unreadNotifications()->count())->toBe(0);

    $this->get(route('notifications.index'))
        ->assertOk()
        ->assertSee('data-notification-read="true"', false)
        ->assertDontSee('Tandai semua telah dibaca');
});

it('menghapus pemberitahuan milik sendiri', function () {
    $admin = User::factory()->create(['role' => Role::Admin]);

    $admin->notify(new DistributionReportDeleted('01/11/2026', 'PT Alpha'));

    $notification = $admin->notifications()->firstOrFail();

    $this->actingAs($admin)
        ->from(route('notifications.index'))
        ->delete(route('notifications.destroy', $notification))
        ->assertRedirect(route('notifications.index'))
        ->assertSessionHas('success', 'Pemberitahuan berhasil dihapus.');

    $this->assertDatabaseMissing('notifications', ['id' => $notification->id]);
});

it('menolak menghapus pemberitahuan milik pengguna lain', function () {
    $admin = User::factory()->create(['role' => Role::Admin]);
    $otherAdmin = User::factory()->create(['role' => Role::Admin]);

    $otherAdmin->notify(new DistributionReportDeleted('01/11/2026', 'PT Alpha'));

    $notification = $otherAdmin->notifications()->firstOrFail();

    $this->actingAs($admin)
        ->delete(route('notifications.destroy', $notification))
        ->assertNotFound();

    $this->assertDatabaseHas('notifications', ['id' => $notification->id]);
});

it('membagi riwayat pemberitahuan menjadi sepuluh item per halaman', function () {
    $admin = User::factory()->create(['role' => Role::Admin]);

    foreach (range(1, 11) as $index) {
        $admin->notify(new DistributionReportDeleted(sprintf('%02d/11/2026', $index), "PT Alpha {$index}"));
    }

    $this->actingAs($admin)
        ->get(route('notifications.index'))
        ->assertOk()
        ->assertSee('Menampilkan 10 dari 11 notifikasi');

    $this->get(route('notifications.index', ['page' => 2]))
        ->assertOk()
        ->assertSee('Menampilkan 1 dari 11 notifikasi');
});

it('menampilkan card terbaru lebih dulu dengan format waktu yang sesuai', function () {
    $admin = User::factory()->create(['role' => Role::Admin]);

    Carbon::setTestNow(Carbon::parse('2026-11-17 09:15:00', 'Asia/Jakarta'));
    $admin->notify(new DistributionReportDeleted('17/11/2026', 'PT Hari Ini'));

    Carbon::setTestNow(Carbon::parse('2026-11-16 10:00:00', 'Asia/Jakarta'));
    $admin->notify(new DistributionReportDeleted('16/11/2026', 'PT Kemarin'));

    Carbon::setTestNow(Carbon::parse('2026-10-03 10:00:00', 'Asia/Jakarta'));
    $admin->notify(new DistributionReportDeleted('03/10/2026', 'PT Satu Bulan'));

    Carbon::setTestNow(Carbon::parse('2026-09-03 10:00:00', 'Asia/Jakarta'));
    $admin->notify(new DistributionReportDeleted('03/09/2026', 'PT Dua Bulan'));

    Carbon::setTestNow(Carbon::parse('2026-11-17 14:30:00', 'Asia/Jakarta'));

    $this->actingAs($admin)
        ->get(route('notifications.index'))
        ->assertOk()
        ->assertSeeInOrder(['PT Hari Ini', 'PT Kemarin', 'PT Satu Bulan', 'PT Dua Bulan'])
        ->assertSee('09:15')
        ->assertSee('1 hari yang lalu')
        ->assertSee('1 bulan yang lalu')
        ->assertSee('2 bulan yang lalu');
});
