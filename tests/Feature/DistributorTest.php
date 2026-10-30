<?php

use App\Models\Distributor;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('membuat distributor aktif dari factory', function () {
    $distributor = Distributor::factory()->create();

    expect($distributor->exists)->toBeTrue()
        ->and($distributor->code)->not->toBeEmpty()
        ->and($distributor->name)->not->toBeEmpty()
        ->and($distributor->is_active)->toBeTrue();
});

it('menyimpan status nonaktif sebagai boolean', function () {
    $distributor = Distributor::factory()->create(['is_active' => false]);

    expect($distributor->fresh()->is_active)->toBeFalse();
});
