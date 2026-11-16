<?php

use App\Enums\Role;
use App\Models\Market;
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
    $shared = User::factory()->create([
        'name' => 'Akun Bersama UPTD',
        'role' => Role::UptdPasar,
    ]);

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

    $contributors = collect($marketNames)->map(function (string $marketName, int $index): User {
        $number = str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT);
        $market = Market::factory()->create(['name' => $marketName]);

        return User::factory()->create([
            'name' => 'Kontributor UPTD '.$number,
            'username' => 'kontributor.uptd.'.$number,
            'role' => Role::UptdPasar,
            'market_id' => $market->id,
        ]);
    });

    User::factory()->inactive()->create([
        'name' => 'Kontributor Nonaktif',
        'role' => Role::UptdPasar,
        'market_id' => Market::factory(),
    ]);

    User::factory()->create([
        'name' => 'Kontributor Pasar Nonaktif',
        'role' => Role::UptdPasar,
        'market_id' => Market::factory()->create(['is_active' => false]),
    ]);

    $this->actingAs($shared)
        ->get(route('uptd.contributors.index'))
        ->assertOk()
        ->assertSee('Pilih identitas kontributor')
        ->assertSee('Identitas dipilih')
        ->assertSee('Lanjut ke dashboard')
        ->assertSeeInOrder($contributors->pluck('name')->all())
        ->assertSeeInOrder($marketNames)
        ->assertDontSee('Akun Bersama UPTD')
        ->assertDontSee('Kontributor Nonaktif')
        ->assertDontSee('Kontributor Pasar Nonaktif');
});

it('menyimpan identitas terpilih dan langsung membuka dashboard tanpa login kedua', function () {
    $shared = User::factory()->create(['role' => Role::UptdPasar]);
    $contributor = User::factory()->create([
        'name' => 'Kontributor UPTD 04',
        'username' => 'kontributor.uptd.04',
        'role' => Role::UptdPasar,
        'market_id' => Market::factory()->create(['name' => 'Pasar Nanggalo']),
    ]);

    $this->actingAs($shared)
        ->post(route('uptd.contributors.select'), ['contributor_id' => $contributor->id])
        ->assertRedirect(route('uptd.dashboard'))
        ->assertSessionHas('uptd_selected_contributor', [
            'id' => $contributor->id,
            'username' => 'kontributor.uptd.04',
            'name' => 'Kontributor UPTD 04',
            'market' => 'Pasar Nanggalo',
        ]);

    $this->assertAuthenticatedAs($shared);

    $this->get(route('uptd.dashboard'))
        ->assertOk()
        ->assertSee('Kontributor UPTD 04')
        ->assertSee('Pasar Nanggalo');
});

it('menolak pemilihan identitas yang tidak valid', function (mixed $value, string $message) {
    $shared = User::factory()->create(['role' => Role::UptdPasar]);

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
    $shared = User::factory()->create(['role' => Role::UptdPasar]);
    $otherShared = User::factory()->create(['role' => Role::UptdPasar]);

    $response = $this->actingAs($shared)
        ->post(route('uptd.contributors.select'), ['contributor_id' => $otherShared->id]);

    $response->assertSessionHasErrors('contributor_id')
        ->assertSessionMissing('uptd_selected_contributor');

    expect(session('errors')->first('contributor_id'))
        ->toContain('Identitas kontributor tidak valid atau sudah tidak aktif.');

    $this->assertAuthenticatedAs($shared);
});

it('menolak identitas kontributor yang nonaktif', function () {
    $shared = User::factory()->create(['role' => Role::UptdPasar]);
    $inactiveContributor = User::factory()->inactive()->create([
        'role' => Role::UptdPasar,
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

it('mengizinkan akun bersama berganti identitas tanpa login ulang', function () {
    $shared = User::factory()->create(['role' => Role::UptdPasar]);
    $firstContributor = User::factory()->create([
        'name' => 'Kontributor UPTD 01',
        'role' => Role::UptdPasar,
        'market_id' => Market::factory()->create(['name' => 'Pasar Raya Padang']),
    ]);
    $secondContributor = User::factory()->create([
        'name' => 'Kontributor UPTD 02',
        'role' => Role::UptdPasar,
        'market_id' => Market::factory()->create(['name' => 'Pasar Alai']),
    ]);

    $this->actingAs($shared);

    $this->post(route('uptd.contributors.select'), ['contributor_id' => $firstContributor->id])
        ->assertRedirect(route('uptd.dashboard'));

    $this->post(route('uptd.contributors.select'), ['contributor_id' => $secondContributor->id])
        ->assertRedirect(route('uptd.dashboard'))
        ->assertSessionHas('uptd_selected_contributor.id', $secondContributor->id);

    $this->assertAuthenticatedAs($shared);
});

it('mengarahkan kontributor pasar langsung ke dashboard dari halaman pemilihan', function () {
    $contributor = User::factory()->create([
        'role' => Role::UptdPasar,
        'market_id' => Market::factory(),
    ]);

    $this->actingAs($contributor)
        ->get(route('uptd.contributors.index'))
        ->assertRedirect(route('uptd.dashboard'));
});

it('menolak kontributor pasar memilih identitas lain secara langsung', function () {
    $contributor = User::factory()->create([
        'role' => Role::UptdPasar,
        'market_id' => Market::factory(),
    ]);
    $otherContributor = User::factory()->create([
        'role' => Role::UptdPasar,
        'market_id' => Market::factory(),
    ]);

    $this->actingAs($contributor)
        ->post(route('uptd.contributors.select'), ['contributor_id' => $otherContributor->id])
        ->assertForbidden();
});
