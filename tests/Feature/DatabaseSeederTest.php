<?php

use App\Enums\Role;
use App\Models\Distribution;
use App\Models\Distributor;
use App\Models\FieldDistributionReport;
use App\Models\Market;
use App\Models\MarketMonitoring;
use App\Models\RecipientStore;
use App\Models\UptdContributor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

it('membuat data awal sesuai struktur sistem', function () {
    $this->seed();

    expect(User::count())->toBe(7)
        ->and(User::where('role', Role::Admin)->count())->toBe(1)
        ->and(User::where('role', Role::KepalaDinas)->count())->toBe(1)
        ->and(User::where('role', Role::Distributor)->count())->toBe(4)
        ->and(User::where('role', Role::Distributor)->whereNotNull('distributor_id')->count())->toBe(4)
        ->and(User::where('role', Role::UptdPasar)->whereNull('market_id')->count())->toBe(1)
        ->and(User::where('username', 'uptd.pasar')->exists())->toBeTrue()
        ->and(Distributor::count())->toBe(4)
        ->and(Market::count())->toBe(9)
        ->and(UptdContributor::count())->toBe(9)
        ->and(UptdContributor::where('username', 'uptd.rayapadang')->exists())->toBeTrue()
        ->and(RecipientStore::count())->toBe(0)
        ->and(Distribution::count())->toBe(0)
        ->and(FieldDistributionReport::count())->toBe(0)
        ->and(MarketMonitoring::count())->toBe(0);
});

it('menggunakan password default yang disepakati untuk tahap pengembangan', function () {
    $this->seed();

    expect(Hash::check('admin123', User::where('username', 'admin')->firstOrFail()->password))->toBeTrue()
        ->and(Hash::check('kepala123', User::where('username', 'kepala.dinas')->firstOrFail()->password))->toBeTrue()
        ->and(Hash::check('uptdpasar123', User::where('username', 'uptd.pasar')->firstOrFail()->password))->toBeTrue()
        ->and(Hash::check('distributor123', User::where('username', 'bulog')->firstOrFail()->password))->toBeTrue()
        ->and(Hash::check('uptdpasar123', UptdContributor::where('username', 'uptd.rayapadang')->firstOrFail()->password))->toBeTrue();
});

it('tidak menimpa password yang sudah diubah saat seeder dijalankan ulang', function () {
    $this->seed();

    $admin = User::where('username', 'admin')->firstOrFail();
    $admin->update(['password' => 'password-baru']);

    $contributor = UptdContributor::where('username', 'uptd.rayapadang')->firstOrFail();
    $contributor->update(['password' => 'kontributor-baru']);

    $this->seed();

    expect(Hash::check('password-baru', $admin->fresh()->password))->toBeTrue()
        ->and(Hash::check('kontributor-baru', $contributor->fresh()->password))->toBeTrue()
        ->and(User::count())->toBe(7)
        ->and(UptdContributor::count())->toBe(9);
});
