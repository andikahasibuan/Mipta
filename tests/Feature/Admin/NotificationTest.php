<?php

use App\Enums\Role;
use App\Models\User;
use App\Notifications\DistributionReportDeleted;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('menampilkan notifikasi penghapusan laporan di topbar', function () {
    $admin = User::factory()->create(['role' => Role::Admin]);

    $admin->notify(new DistributionReportDeleted('LP-007', '09/11/2026', 'PT Rajawali Nusantara'));

    $this->actingAs($admin)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('Laporan LP-007 tanggal 09/11/2026 telah dihapus oleh PT Rajawali Nusantara.')
        ->assertSee('Tandai semua dibaca')
        ->assertSee('action="'.route('notifications.read-all').'"', false)
        ->assertSee('aria-label="Notifikasi"', false);
});

it('menampilkan keadaan kosong saat pengguna belum memiliki notifikasi', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('Belum ada notifikasi')
        ->assertDontSee('Tandai semua dibaca');
});

it('menandai seluruh notifikasi milik pengguna sebagai sudah dibaca', function () {
    $admin = User::factory()->create(['role' => Role::Admin]);
    $otherAdmin = User::factory()->create(['role' => Role::Admin]);

    $admin->notify(new DistributionReportDeleted('LP-001', '01/11/2026', 'PT Alpha'));
    $admin->notify(new DistributionReportDeleted('LP-002', '02/11/2026', 'PT Beta'));
    $otherAdmin->notify(new DistributionReportDeleted('LP-003', '03/11/2026', 'PT Gamma'));

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
        ->assertSee('Laporan LP-001 tanggal 01/11/2026 telah dihapus oleh PT Alpha.')
        ->assertDontSee('Tandai semua dibaca');
});

it('mengarahkan tamu yang menandai notifikasi ke halaman login', function () {
    $this->post(route('notifications.read-all'))->assertRedirect(route('login'));
});
