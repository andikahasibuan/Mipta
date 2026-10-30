<?php

use App\Models\Market;
use App\Models\RecipientStore;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('membuat toko penerima beserta pasar dari factory', function () {
    $store = RecipientStore::factory()->create();

    expect($store->exists)->toBeTrue()
        ->and($store->code)->not->toBeEmpty()
        ->and($store->name)->not->toBeEmpty()
        ->and($store->is_active)->toBeTrue()
        ->and($store->market)->toBeInstanceOf(Market::class);
});

it('menghubungkan toko ke pasar dan pasar ke banyak toko', function () {
    $market = Market::factory()->create();
    $stores = RecipientStore::factory()->count(2)->create(['market_id' => $market->id]);

    expect($market->stores()->count())->toBe(2)
        ->and($market->stores->pluck('id')->all())->toEqual($stores->pluck('id')->all());
});

it('menyimpan status nonaktif sebagai boolean', function () {
    $store = RecipientStore::factory()->create(['is_active' => false]);

    expect($store->fresh()->is_active)->toBeFalse();
});

it('menolak kode toko yang duplikat', function () {
    RecipientStore::factory()->create(['code' => 'TKS-RAYA']);

    RecipientStore::factory()->create(['code' => 'TKS-RAYA']);
})->throws(QueryException::class);
