<?php

use App\Enums\Role;
use App\Models\Market;
use App\Models\User;
use App\Notifications\DistributionReportDeleted;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

it('menampilkan notifikasi penghapusan laporan di topbar', function () {
    $admin = User::factory()->create(['role' => Role::Admin]);

    $admin->notify(new DistributionReportDeleted('09/11/2026', 'PT Rajawali Nusantara'));

    $this->actingAs($admin)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('Laporan penyaluran tanggal 09/11/2026 telah dihapus oleh PT Rajawali Nusantara.')
        ->assertSee('Tandai semua telah dibaca')
        ->assertSee('action="'.route('notifications.read-all').'"', false)
        ->assertSee('aria-label="Notifikasi"', false);
});

it('menampilkan keadaan kosong saat pengguna belum memiliki notifikasi', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('Belum ada notifikasi')
        ->assertDontSee('Tandai semua telah dibaca');
});

it('menandai seluruh notifikasi milik pengguna sebagai sudah dibaca', function () {
    $admin = User::factory()->create(['role' => Role::Admin]);
    $otherAdmin = User::factory()->create(['role' => Role::Admin]);

    $admin->notify(new DistributionReportDeleted('01/11/2026', 'PT Alpha'));
    $admin->notify(new DistributionReportDeleted('02/11/2026', 'PT Beta'));
    $otherAdmin->notify(new DistributionReportDeleted('03/11/2026', 'PT Gamma'));

    expect($admin->unreadNotifications()->count())->toBe(2)
        ->and($otherAdmin->unreadNotifications()->count())->toBe(1);

    $this->actingAs($admin)
        ->from(route('admin.dashboard'))
        ->post(route('notifications.read-all'))
        ->assertRedirect(route('admin.dashboard'))
        ->assertSessionHas('success', 'Semua notifikasi telah ditandai dibaca.');

    expect($admin->fresh()->unreadNotifications()->count())->toBe(0)
        ->and($otherAdmin->fresh()->unreadNotifications()->count())->toBe(1);

    $this->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('Laporan penyaluran tanggal 01/11/2026 telah dihapus oleh PT Alpha.')
        ->assertDontSee('Tandai semua telah dibaca');
});

it('mengarahkan tamu yang menandai notifikasi ke halaman login', function () {
    $this->post(route('notifications.read-all'))->assertRedirect(route('login'));
});

it('hanya mengizinkan admin dan kepala dinas menandai notifikasi', function () {
    $kepalaDinas = User::factory()->create(['role' => Role::KepalaDinas]);

    $this->actingAs($kepalaDinas)
        ->from(route('kepala-dinas.dashboard'))
        ->post(route('notifications.read-all'))
        ->assertRedirect(route('kepala-dinas.dashboard'));

    $distributor = User::factory()->distributor()->create();

    $this->actingAs($distributor)
        ->post(route('notifications.read-all'))
        ->assertForbidden();

    $uptd = User::factory()->create([
        'role' => Role::UptdPasar,
        'market_id' => Market::factory(),
    ]);

    $this->actingAs($uptd)
        ->post(route('notifications.read-all'))
        ->assertForbidden();
});

it('menyediakan feed notifikasi terbaru untuk topbar', function () {
    $admin = User::factory()->create(['role' => Role::Admin]);

    $admin->notify(new DistributionReportDeleted('09/11/2026', 'PT Rajawali Nusantara'));

    $notification = $admin->notifications()->firstOrFail();

    $response = $this->actingAs($admin)
        ->getJson(route('notifications.feed'))
        ->assertOk()
        ->assertJsonPath('unread_count', 1);

    expect($response->json('content_html'))
        ->toContain('Laporan penyaluran telah dihapus')
        ->toContain('Laporan penyaluran tanggal 09/11/2026 telah dihapus oleh PT Rajawali Nusantara.')
        ->toContain(route('notifications.read', $notification))
        ->toContain('Tandai semua telah dibaca');
});

it('membatasi feed notifikasi menjadi lima item terbaru', function () {
    $admin = User::factory()->create(['role' => Role::Admin]);
    $base = now();

    foreach (range(1, 6) as $index) {
        Carbon::setTestNow($base->copy()->addMinutes($index));
        $admin->notify(new DistributionReportDeleted(sprintf('%02d/11/2026', $index), "PT Alpha {$index}"));
    }

    Carbon::setTestNow();

    $response = $this->actingAs($admin)
        ->getJson(route('notifications.feed'))
        ->assertOk()
        ->assertJsonPath('unread_count', 6);

    $content = $response->json('content_html');

    expect(substr_count($content, 'data-notification-item'))->toBe(5)
        ->and($content)->toContain('PT Alpha 6')
        ->and($content)->not->toContain('PT Alpha 1');
});

it('membatasi akses feed notifikasi untuk admin dan kepala dinas', function () {
    $kepalaDinas = User::factory()->create(['role' => Role::KepalaDinas]);

    $this->actingAs($kepalaDinas)
        ->getJson(route('notifications.feed'))
        ->assertOk();

    $this->actingAs(User::factory()->distributor()->create())
        ->getJson(route('notifications.feed'))
        ->assertForbidden();

    $this->actingAs(User::factory()->create(['role' => Role::UptdPasar]))
        ->getJson(route('notifications.feed'))
        ->assertForbidden();
});

it('mengarahkan tamu dari feed notifikasi ke login', function () {
    $this->getJson(route('notifications.feed'))->assertUnauthorized();
});
