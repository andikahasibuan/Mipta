<?php

use App\Enums\Role;
use App\Models\Market;
use App\Models\UptdContributor;
use App\Models\User;
use Database\Seeders\UptdContributorSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

it('membuat akun bersama dan sembilan profil kontributor tanpa akun login terpisah', function () {
    $this->seed(UptdContributorSeeder::class);

    $shared = User::where('username', 'uptd')->first();

    expect($shared)->not->toBeNull()
        ->and($shared->isSharedUptdAccount())->toBeTrue()
        ->and(Hash::check('UptdPasar#2026', $shared->password))->toBeTrue();

    $contributors = UptdContributor::query()
        ->orderBy('username')
        ->get();

    expect(Market::count())->toBe(9)
        ->and($contributors)->toHaveCount(9)
        ->and($contributors->pluck('market_id')->unique())->toHaveCount(9)
        ->and(User::where('role', Role::UptdPasar)->whereNotNull('market_id')->count())->toBe(0);

    foreach ($contributors->values() as $index => $contributor) {
        $number = str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT);

        expect($contributor->username)->toBe('kontributor.uptd.'.$number)
            ->and($contributor->is_active)->toBeTrue()
            ->and(Hash::check('uptdpasar123', $contributor->password))->toBeTrue();
    }
});

it('memisahkan akun bersama dan profil kontributor pasar pada menu admin', function () {
    $this->seed(UptdContributorSeeder::class);

    $admin = User::factory()->create();

    $this->actingAs($admin)
        ->get(route('admin.users.index', ['role' => 'uptd_pasar']))
        ->assertOk()
        ->assertSee('Kontributor UPTD Pasar')
        ->assertDontSee('Kontributor UPTD 01');

    $this->get(route('admin.users.uptd-contributors.index'))
        ->assertOk()
        ->assertSee('Kontributor UPTD 09')
        ->assertDontSee('Kontributor UPTD Pasar');
});
