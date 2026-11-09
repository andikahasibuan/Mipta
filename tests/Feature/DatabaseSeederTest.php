<?php

use App\Enums\Role;
use App\Models\Distribution;
use App\Models\Distributor;
use App\Models\FieldDistributionReport;
use App\Models\Market;
use App\Models\MarketMonitoring;
use App\Models\RecipientStore;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('hanya membuat akun admin saat database di-seed secara default', function () {
    $this->seed();

    expect(User::count())->toBe(1)
        ->and(User::where('email', 'admin@mipta.test')->exists())->toBeTrue()
        ->and(User::where('role', Role::Admin)->count())->toBe(1)
        ->and(Market::count())->toBe(0)
        ->and(Distributor::count())->toBe(0)
        ->and(RecipientStore::count())->toBe(0)
        ->and(Distribution::count())->toBe(0)
        ->and(FieldDistributionReport::count())->toBe(0)
        ->and(MarketMonitoring::count())->toBe(0);
});
