<?php

use App\Enums\Role;
use App\Models\Market;
use App\Models\UptdContributor;
use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;

uses(RefreshDatabase::class);

it('menampilkan formulir login untuk identitas terpilih', function () {
    $shared = sharedUptdAccount();
    $contributor = UptdContributor::factory()->create([
        'name' => 'Kontributor UPTD 04',
        'username' => 'kontributor.uptd.04',
        'market_id' => Market::factory()->create(['name' => 'Pasar Nanggalo']),
    ]);

    $this->actingAs($shared)
        ->withSession(['uptd_pending_contributor' => $contributor->id])
        ->get(route('uptd.contributors.login', $contributor))
        ->assertOk()
        ->assertSee('Masuk')
        ->assertDontSee('Masuk sebagai kontributor')
        ->assertSee('Kontributor UPTD 04')
        ->assertSee('Pasar Nanggalo')
        ->assertSee('Hubungi Admin')
        ->assertSee('name="username"', false)
        ->assertSee('name="password"', false)
        ->assertDontSee('kontributor.uptd.04');
});

it('masuk ke dashboard setelah username dan password identitas sesuai', function () {
    $shared = sharedUptdAccount();
    $contributor = UptdContributor::factory()->create([
        'name' => 'Kontributor UPTD 04',
        'username' => 'kontributor.uptd.04',
        'market_id' => Market::factory()->create(['name' => 'Pasar Nanggalo']),
    ]);

    $this->actingAs($shared)
        ->post(route('uptd.contributors.select'), ['contributor_id' => $contributor->id])
        ->assertRedirect(route('uptd.contributors.login', $contributor))
        ->assertSessionHas('uptd_pending_contributor', $contributor->id);

    $this->post(route('uptd.contributors.login.store', $contributor), [
        'username' => 'KONTRIBUTOR.UPTD.04',
        'password' => 'uptdpasar123',
    ])
        ->assertRedirect(route('uptd.dashboard'))
        ->assertSessionMissing('uptd_pending_contributor')
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

it('menolak username yang tidak sesuai dengan identitas terpilih', function () {
    $shared = sharedUptdAccount();
    $contributor = UptdContributor::factory()->create([
        'username' => 'kontributor.uptd.04',
        'market_id' => Market::factory(),
    ]);

    $response = $this->actingAs($shared)
        ->withSession(['uptd_pending_contributor' => $contributor->id])
        ->post(route('uptd.contributors.login.store', $contributor), [
            'username' => 'kontributor.uptd.05',
            'password' => 'uptdpasar123',
        ]);

    $response->assertSessionHasErrors('username')
        ->assertSessionMissing('uptd_selected_contributor');

    expect(session('errors')->first('username'))
        ->toContain('Username atau password tidak sesuai.');
});

it('menolak password yang salah', function () {
    $shared = sharedUptdAccount();
    $contributor = UptdContributor::factory()->create([
        'market_id' => Market::factory(),
    ]);

    $response = $this->actingAs($shared)
        ->withSession(['uptd_pending_contributor' => $contributor->id])
        ->post(route('uptd.contributors.login.store', $contributor), [
            'username' => $contributor->username,
            'password' => 'password-salah',
        ]);

    $response->assertSessionHasErrors('username')
        ->assertSessionMissing('uptd_selected_contributor');

    expect(session('errors')->first('username'))
        ->toContain('Username atau password tidak sesuai.');
});

it('menolak identitas kontributor yang nonaktif', function () {
    $shared = sharedUptdAccount();
    $contributor = UptdContributor::factory()->inactive()->create([
        'market_id' => Market::factory(),
    ]);

    $this->actingAs($shared)
        ->get(route('uptd.contributors.login', $contributor))
        ->assertNotFound();

    $this->post(route('uptd.contributors.login.store', $contributor), [
        'username' => $contributor->username,
        'password' => 'uptdpasar123',
    ])->assertNotFound();
});

it('menolak identitas pada pasar yang nonaktif', function () {
    $shared = sharedUptdAccount();
    $contributor = UptdContributor::factory()->create([
        'market_id' => Market::factory()->create(['is_active' => false]),
    ]);

    $this->actingAs($shared)
        ->get(route('uptd.contributors.login', $contributor))
        ->assertNotFound();
});

it('membatasi percobaan login identitas setelah tiga kali gagal', function () {
    $shared = sharedUptdAccount();
    $contributor = UptdContributor::factory()->create([
        'market_id' => Market::factory(),
    ]);

    $this->actingAs($shared)
        ->withSession(['uptd_pending_contributor' => $contributor->id]);

    foreach (range(1, 3) as $attempt) {
        $this->post(route('uptd.contributors.login.store', $contributor), [
            'username' => $contributor->username,
            'password' => 'password-salah',
        ])->assertSessionHasErrors('username');
    }

    $response = $this->post(route('uptd.contributors.login.store', $contributor), [
        'username' => $contributor->username,
        'password' => 'uptdpasar123',
    ]);

    $response->assertSessionHasErrors('username')
        ->assertSessionMissing('uptd_selected_contributor');

    expect(session('errors')->first('username'))
        ->toContain('Terlalu banyak percobaan masuk.')
        ->toContain('10 menit');

    $this->travel(11)->minutes();

    $this->post(route('uptd.contributors.login.store', $contributor), [
        'username' => $contributor->username,
        'password' => 'uptdpasar123',
    ])
        ->assertRedirect(route('uptd.dashboard'))
        ->assertSessionHas('uptd_selected_contributor.id', $contributor->id);
});

it('tidak mengunci identitas lain saat satu identitas gagal berkali-kali', function () {
    $shared = sharedUptdAccount();
    $firstContributor = UptdContributor::factory()->create([
        'market_id' => Market::factory(),
    ]);
    $secondContributor = UptdContributor::factory()->create([
        'market_id' => Market::factory(),
    ]);

    $this->actingAs($shared)
        ->withSession(['uptd_pending_contributor' => $firstContributor->id]);

    foreach (range(1, 3) as $attempt) {
        $this->post(route('uptd.contributors.login.store', $firstContributor), [
            'username' => $firstContributor->username,
            'password' => 'password-salah',
        ])->assertSessionHasErrors('username');
    }

    $this->withSession(['uptd_pending_contributor' => $secondContributor->id])
        ->post(route('uptd.contributors.login.store', $secondContributor), [
            'username' => $secondContributor->username,
            'password' => 'uptdpasar123',
        ])
        ->assertRedirect(route('uptd.dashboard'))
        ->assertSessionHas('uptd_selected_contributor.id', $secondContributor->id);
});

it('mengirim event lockout saat percobaan login identitas melewati batas', function () {
    Event::fake([Lockout::class]);

    $shared = sharedUptdAccount();
    $contributor = UptdContributor::factory()->create([
        'market_id' => Market::factory(),
    ]);

    $this->actingAs($shared)
        ->withSession(['uptd_pending_contributor' => $contributor->id]);

    foreach (range(1, 3) as $attempt) {
        $this->post(route('uptd.contributors.login.store', $contributor), [
            'username' => $contributor->username,
            'password' => 'password-salah',
        ])->assertSessionHasErrors('username');
    }

    $this->post(route('uptd.contributors.login.store', $contributor), [
        'username' => $contributor->username,
        'password' => 'uptdpasar123',
    ])->assertSessionHasErrors('username');

    Event::assertDispatched(Lockout::class);
});

it('mengalihkan ke pemilihan identitas saat formulir login dibuka tanpa memilih identitas', function () {
    $shared = sharedUptdAccount();
    $contributor = UptdContributor::factory()->create([
        'market_id' => Market::factory(),
    ]);

    $response = $this->actingAs($shared)
        ->get(route('uptd.contributors.login', $contributor));

    $response->assertRedirect(route('uptd.contributors.index'))
        ->assertSessionHasErrors('contributor_id');

    expect(session('errors')->first('contributor_id'))
        ->toContain('Pilih identitas kontributor terlebih dahulu.');
});

it('mengalihkan ke pemilihan identitas saat login diproses tanpa memilih identitas', function () {
    $shared = sharedUptdAccount();
    $contributor = UptdContributor::factory()->create([
        'market_id' => Market::factory(),
    ]);

    $response = $this->actingAs($shared)
        ->post(route('uptd.contributors.login.store', $contributor), [
            'username' => $contributor->username,
            'password' => 'uptdpasar123',
        ]);

    $response->assertRedirect(route('uptd.contributors.index'))
        ->assertSessionHasErrors('contributor_id')
        ->assertSessionMissing('uptd_selected_contributor');
});

it('menolak formulir login identitas yang tidak sesuai dengan pilihan terakhir', function () {
    $shared = sharedUptdAccount();
    $selectedContributor = UptdContributor::factory()->create([
        'market_id' => Market::factory(),
    ]);
    $otherContributor = UptdContributor::factory()->create([
        'market_id' => Market::factory(),
    ]);

    $this->actingAs($shared)
        ->post(route('uptd.contributors.select'), [
            'contributor_id' => $selectedContributor->id,
        ])
        ->assertSessionHas('uptd_pending_contributor', $selectedContributor->id);

    $this->get(route('uptd.contributors.login', $otherContributor))
        ->assertRedirect(route('uptd.contributors.index'))
        ->assertSessionHasErrors('contributor_id');
});

it('hanya mengizinkan akun bersama UPTD membuka formulir login identitas', function () {
    $contributor = UptdContributor::factory()->create([
        'market_id' => Market::factory(),
    ]);

    $nonShared = User::factory()->create([
        'role' => Role::UptdPasar,
        'market_id' => Market::factory(),
    ]);

    $this->actingAs($nonShared)
        ->get(route('uptd.contributors.login', $contributor))
        ->assertForbidden();
});
