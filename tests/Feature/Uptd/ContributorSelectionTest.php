<?php

use App\Enums\Role;
use App\Models\Market;
use App\Models\UptdContributor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('mengarahkan tamu dari halaman pemilihan identitas ke login', function () {
    $this->get(route('uptd.contributors.index'))->assertRedirect(route('login'));
});

it('menolak peran selain kontributor UPTD dari halaman pemilihan identitas', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('uptd.contributors.index'))
        ->assertForbidden();
});

it('menampilkan sembilan identitas kontributor UPTD yang aktif', function () {
    $shared = sharedUptdAccount();
    $shared->update(['name' => 'Akun Bersama UPTD']);

    $marketNames = [
        'Pasar Raya Padang',
        'Pasar Alai',
        'Pasar Lubuk Buaya',
        'Pasar Nanggalo',
        'Pasar Bandar Buat',
        'Pasar Ulak Karang',
        'Pasar Belimbing',
        'Pasar Tanah Kongsi',
        'Pasar Siteba',
    ];

    $contributors = collect($marketNames)->map(function (string $marketName, int $index): UptdContributor {
        $number = str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT);
        $market = Market::factory()->create(['name' => $marketName]);

        return UptdContributor::factory()->create([
            'name' => 'Kontributor UPTD '.$number,
            'username' => 'kontributor.uptd.'.$number,
            'market_id' => $market->id,
        ]);
    });

    UptdContributor::factory()->inactive()->create([
        'name' => 'Kontributor Nonaktif',
        'market_id' => Market::factory(),
    ]);

    UptdContributor::factory()->create([
        'name' => 'Kontributor Pasar Nonaktif',
        'market_id' => Market::factory()->create(['is_active' => false]),
    ]);

    $this->actingAs($shared)
        ->get(route('uptd.contributors.index'))
        ->assertOk()
        ->assertSee('Pilih identitas kontributor')
        ->assertSee('Identitas dipilih')
        ->assertSee('Lanjut')
        ->assertSeeInOrder($contributors->pluck('name')->all())
        ->assertSeeInOrder($marketNames)
        ->assertDontSee('Akun Bersama UPTD')
        ->assertDontSee('Kontributor Nonaktif')
        ->assertDontSee('Kontributor Pasar Nonaktif');
});

it('mengarahkan ke halaman login identitas setelah memilih pasar', function () {
    $shared = sharedUptdAccount();
    $contributor = UptdContributor::factory()->create([
        'name' => 'Kontributor UPTD 04',
        'username' => 'kontributor.uptd.04',
        'market_id' => Market::factory()->create(['name' => 'Pasar Nanggalo']),
    ]);

    $this->actingAs($shared)
        ->post(route('uptd.contributors.select'), ['contributor_id' => $contributor->id])
        ->assertRedirect(route('uptd.contributors.login', $contributor))
        ->assertSessionHas('uptd_pending_contributor', $contributor->id)
        ->assertSessionMissing('uptd_selected_contributor');

    $this->assertAuthenticatedAs($shared);

    $this->get(route('uptd.dashboard'))
        ->assertRedirect(route('uptd.contributors.index'));
});

it('menolak pemilihan identitas yang tidak valid', function (mixed $value, string $message) {
    $shared = sharedUptdAccount();

    $response = $this->actingAs($shared)->post(
        route('uptd.contributors.select'),
        $value === null ? [] : ['contributor_id' => $value],
    );

    $response->assertSessionHasErrors('contributor_id');

    expect(session('errors')->first('contributor_id'))->toContain($message);
    $this->assertAuthenticatedAs($shared);
})->with([
    'kosong' => [null, 'Pilih salah satu identitas kontributor.'],
    'bukan angka' => ['abc', 'Identitas kontributor tidak valid.'],
]);

it('menolak identitas yang bukan kontributor UPTD pasar', function () {
    $shared = sharedUptdAccount();
    $user = User::factory()->create();

    $response = $this->actingAs($shared)
        ->post(route('uptd.contributors.select'), ['contributor_id' => $user->id]);

    $response->assertSessionHasErrors('contributor_id')
        ->assertSessionMissing('uptd_selected_contributor');

    expect(session('errors')->first('contributor_id'))
        ->toContain('Identitas kontributor tidak valid atau sudah tidak aktif.');

    $this->assertAuthenticatedAs($shared);
});

it('menolak identitas kontributor yang nonaktif', function () {
    $shared = sharedUptdAccount();
    $inactiveContributor = UptdContributor::factory()->inactive()->create([
        'market_id' => Market::factory(),
    ]);

    $response = $this->actingAs($shared)
        ->post(route('uptd.contributors.select'), ['contributor_id' => $inactiveContributor->id]);

    $response->assertSessionHasErrors('contributor_id')
        ->assertSessionMissing('uptd_selected_contributor');

    expect(session('errors')->first('contributor_id'))
        ->toContain('Identitas kontributor tidak valid atau sudah tidak aktif.');

    $this->assertAuthenticatedAs($shared);
});

it('meminta login identitas baru saat akun bersama berganti identitas', function () {
    $shared = sharedUptdAccount();
    $firstContributor = UptdContributor::factory()->create([
        'name' => 'Kontributor UPTD 01',
        'market_id' => Market::factory()->create(['name' => 'Pasar Raya Padang']),
    ]);
    $secondContributor = UptdContributor::factory()->create([
        'name' => 'Kontributor UPTD 02',
        'market_id' => Market::factory()->create(['name' => 'Pasar Alai']),
    ]);

    $this->actingAs($shared);

    $this->post(route('uptd.contributors.select'), ['contributor_id' => $firstContributor->id])
        ->assertRedirect(route('uptd.contributors.login', $firstContributor));

    $this->post(route('uptd.contributors.login.store', $firstContributor), [
        'username' => $firstContributor->username,
        'password' => 'uptdpasar123',
    ])->assertRedirect(route('uptd.dashboard'));

    $this->post(route('uptd.contributors.select'), ['contributor_id' => $secondContributor->id])
        ->assertRedirect(route('uptd.contributors.login', $secondContributor));

    $this->post(route('uptd.contributors.login.store', $secondContributor), [
        'username' => $secondContributor->username,
        'password' => 'uptdpasar123',
    ])->assertRedirect(route('uptd.dashboard'))
        ->assertSessionHas('uptd_selected_contributor.id', $secondContributor->id);

    $this->assertAuthenticatedAs($shared);
});

it('mengarahkan langsung ke dashboard saat memilih identitas yang sedang aktif', function () {
    $shared = sharedUptdAccount();
    $contributor = UptdContributor::factory()->create([
        'name' => 'Kontributor UPTD 01',
        'market_id' => Market::factory()->create(['name' => 'Pasar Raya Padang']),
    ]);

    $this->actingAs($shared)
        ->withSession([
            'uptd_selected_contributor' => [
                'id' => $contributor->id,
                'username' => $contributor->username,
                'name' => $contributor->name,
                'market' => 'Pasar Raya Padang',
            ],
            'uptd_pending_contributor' => 999,
        ])
        ->post(route('uptd.contributors.select'), ['contributor_id' => $contributor->id])
        ->assertRedirect(route('uptd.dashboard'))
        ->assertSessionMissing('uptd_pending_contributor')
        ->assertSessionHas('uptd_selected_contributor.id', $contributor->id);

    $this->assertAuthenticatedAs($shared);
});

it('mengizinkan akun bersama membuka halaman pemilihan walau sudah memilih identitas', function () {
    $shared = sharedUptdAccount();
    $contributor = UptdContributor::factory()->create(['market_id' => Market::factory()]);

    $this->actingAs($shared)
        ->withSession(uptdContributorSession($contributor))
        ->get(route('uptd.contributors.index'))
        ->assertOk()
        ->assertSee('Pilih identitas kontributor');
});

it('menolak akun UPTD yang terkait pasar memilih identitas lain secara langsung', function () {
    $contributor = User::factory()->create([
        'role' => Role::UptdPasar,
        'market_id' => Market::factory(),
    ]);
    $otherContributor = UptdContributor::factory()->create(['market_id' => Market::factory()]);

    $this->actingAs($contributor)
        ->post(route('uptd.contributors.select'), ['contributor_id' => $otherContributor->id])
        ->assertForbidden();
});
