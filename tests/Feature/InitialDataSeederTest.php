<?php

use App\Enums\Role;
use App\Models\Market;
use App\Models\UptdContributor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('membuat akun bersama dan sembilan profil kontributor tanpa akun login terpisah', function () {
    $this->seed();

    $shared = User::where('username', 'uptd.pasar')->first();

    expect($shared)->not->toBeNull()
        ->and($shared->isSharedUptdAccount())->toBeTrue();

    $contributors = UptdContributor::query()
        ->orderBy('username')
        ->get();

    expect(Market::count())->toBe(9)
        ->and($contributors)->toHaveCount(9)
        ->and($contributors->pluck('market_id')->unique())->toHaveCount(9)
        ->and(User::where('role', Role::UptdPasar)->whereNotNull('market_id')->count())->toBe(0)
        ->and($contributors->pluck('username'))->toContain('uptd.rayapadang');
});

it('memisahkan akun bersama dan profil kontributor pasar pada menu admin', function () {
    $this->seed();

    $admin = User::where('username', 'admin')->firstOrFail();

    $this->actingAs($admin)
        ->get(route('admin.users.index', ['role' => 'uptd_pasar']))
        ->assertOk()
        ->assertSee('UPTD Pasar')
        ->assertDontSee('Kontributor Raya Padang');

    $this->get(route('admin.users.uptd-contributors.index'))
        ->assertOk()
        ->assertSee('Kontributor Raya Padang')
        ->assertDontSee('UPTD Pasar');
});
