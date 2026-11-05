<?php

use App\Models\Market;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('membuat pasar aktif dari factory', function () {
    $market = Market::factory()->create();

    expect($market->exists)->toBeTrue()
        ->and($market->lokasi)->not->toBeEmpty()
        ->and($market->name)->not->toBeEmpty()
        ->and($market->is_active)->toBeTrue();
});

it('menyimpan status nonaktif sebagai boolean', function () {
    $market = Market::factory()->create(['is_active' => false]);

    expect($market->fresh()->is_active)->toBeFalse();
});
