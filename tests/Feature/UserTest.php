<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('membuat pengguna aktif secara default', function () {
    $user = User::factory()->create();

    expect($user->exists)->toBeTrue()
        ->and($user->is_active)->toBeTrue()
        ->and($user->fresh()->is_active)->toBeTrue();
});

it('menyediakan state nonaktif pada factory', function () {
    $user = User::factory()->inactive()->create();

    expect($user->is_active)->toBeFalse()
        ->and($user->fresh()->is_active)->toBeFalse();
});
