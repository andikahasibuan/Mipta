<?php

use App\Enums\Role;
use App\Models\Market;
use App\Models\User;
use Database\Seeders\UptdContributorSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

it('membuat akun bersama dan sembilan kontributor dengan password berbeda', function () {
    $this->seed(UptdContributorSeeder::class);

    $shared = User::where('username', 'uptd')->first();

    expect($shared)->not->toBeNull()
        ->and($shared->isSharedUptdAccount())->toBeTrue()
        ->and(Hash::check('UptdPasar#2026', $shared->password))->toBeTrue();

    $contributors = User::where('role', Role::UptdPasar)
        ->whereNotNull('market_id')
        ->orderBy('username')
        ->get();

    expect(Market::count())->toBe(9)
        ->and($contributors)->toHaveCount(9)
        ->and($contributors->pluck('market_id')->unique())->toHaveCount(9)
        ->and($contributors->every(fn (User $contributor): bool => $contributor->email === null))->toBeTrue();

    foreach ($contributors->values() as $index => $contributor) {
        $number = str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT);

        expect($contributor->username)->toBe('kontributor.uptd.'.$number)
            ->and(Hash::check('KontributorUptd#'.$number, $contributor->password))->toBeTrue();
    }
});

it('memisahkan akun bersama dan kontributor pasar pada tab admin', function () {
    $this->seed(UptdContributorSeeder::class);

    $admin = User::factory()->create();

    $this->actingAs($admin)
        ->get(route('admin.users.index'))
        ->assertOk()
        ->assertSee('Kontributor UPTD Pasar')
        ->assertDontSee('Kontributor UPTD 01');

    $this->get(route('admin.uptd-contributors.index'))
        ->assertOk()
        ->assertSee('Kontributor UPTD 09')
        ->assertDontSee('Kontributor UPTD Pasar');
});
