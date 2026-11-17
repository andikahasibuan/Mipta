<?php

use App\Models\User;
use App\Services\SessionRevoker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

it('menghapus session lain milik pengguna tanpa menghapus session aktif', function () {
    config(['session.driver' => 'database']);

    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    foreach ([
        ['id' => 'current-session', 'user_id' => $user->id],
        ['id' => 'old-session', 'user_id' => $user->id],
        ['id' => 'other-user-session', 'user_id' => $otherUser->id],
    ] as $session) {
        DB::table('sessions')->insert([
            ...$session,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Pest',
            'payload' => '',
            'last_activity' => now()->timestamp,
        ]);
    }

    app(SessionRevoker::class)->revokeFor($user, 'current-session');

    expect(DB::table('sessions')->where('id', 'current-session')->exists())->toBeTrue()
        ->and(DB::table('sessions')->where('id', 'old-session')->exists())->toBeFalse()
        ->and(DB::table('sessions')->where('id', 'other-user-session')->exists())->toBeTrue();
});
