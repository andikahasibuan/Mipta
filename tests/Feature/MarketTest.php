<?php

use App\Models\Market;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('membuat pasar aktif dari factory', function () {
    $market = Market::factory()->create();

    expect($market->exists)->toBeTrue()
        ->and($market->code)->not->toBeEmpty()
        ->and($market->name)->not->toBeEmpty()
        ->and($market->is_active)->toBeTrue();
});

it('menyimpan status nonaktif sebagai boolean', function () {
    $market = Market::factory()->create(['is_active' => false]);

    expect($market->fresh()->is_active)->toBeFalse();
});

it('menolak kode pasar yang duplikat', function () {
    Market::factory()->create(['code' => 'PAS-RAYA']);

    Market::factory()->create(['code' => 'PAS-RAYA']);
})->throws(QueryException::class);
