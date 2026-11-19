<?php

use App\Enums\Role;
use App\Models\Distributor;
use App\Models\Market;
use App\Models\RecipientStore;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('menolak nama dan username pengguna yang hanya berisi angka', function () {
    $this->actingAs(User::factory()->create());

    $this->post(route('admin.users.store'), [
        'name' => '12345',
        'username' => '67890',
        'password' => 'password123',
        'role' => Role::Admin->value,
    ])->assertSessionHasErrors(['name', 'username']);

    $this->assertDatabaseMissing('users', ['username' => '67890']);
});

it('menolak nama perusahaan dan penanggung jawab distributor yang hanya berisi angka', function () {
    $this->actingAs(User::factory()->create());

    $this->post(route('admin.distributors.store'), [
        'name' => '12345',
        'username' => 'pt.angka',
        'penanggung_jawab' => '67890',
        'telepon' => '081234567890',
        'password' => 'password123',
    ])->assertSessionHasErrors(['name', 'penanggung_jawab']);

    $this->assertDatabaseCount('distributors', 0);
});

it('menerima nama dan username yang mengandung huruf dan angka', function () {
    $this->actingAs(User::factory()->create());

    $this->post(route('admin.distributors.store'), [
        'name' => 'PT 123 Sejahtera',
        'username' => 'pt123.sejahtera',
        'penanggung_jawab' => 'Budi 2',
        'telepon' => '081234567890',
        'password' => 'password123',
    ])->assertRedirect(route('admin.distributors.index'));

    $this->assertDatabaseHas('distributors', ['name' => 'PT 123 Sejahtera']);
    $this->assertDatabaseHas('users', ['username' => 'pt123.sejahtera']);
});

it('menolak nama dan lokasi pasar yang hanya berisi angka', function () {
    $this->actingAs(User::factory()->create());

    $this->post(route('admin.markets.store'), [
        'name' => '12345',
        'lokasi' => '67890',
    ])->assertSessionHasErrors(['name', 'lokasi']);

    $this->assertDatabaseCount('markets', 0);
});

it('menolak nama dan alamat toko yang hanya berisi angka', function () {
    $market = Market::factory()->create();

    $this->actingAs(User::factory()->create());

    $this->post(route('admin.recipient-stores.store'), [
        'market_id' => $market->id,
        'name' => '12345',
        'alamat' => '67890',
        'nib' => '1234567890123',
    ])->assertSessionHasErrors(['name', 'alamat']);

    $this->assertDatabaseCount('recipient_stores', 0);
});

it('menolak nama dan username kontributor UPTD yang hanya berisi angka', function () {
    $market = Market::factory()->create();

    $this->actingAs(User::factory()->create());

    $this->post(route('admin.users.uptd-contributors.store'), [
        'name' => '12345',
        'username' => '67890',
        'password' => 'password123',
        'market_id' => $market->id,
    ])->assertSessionHasErrors(['name', 'username']);

    $this->assertDatabaseCount('uptd_contributors', 0);
});

it('menolak nama penanggung jawab dan alamat kantor distributor yang hanya berisi angka', function () {
    $distributor = Distributor::factory()->create();
    $user = User::factory()->create([
        'role' => Role::Distributor,
        'distributor_id' => $distributor->id,
    ]);

    $this->actingAs($user)
        ->from(route('distributor.profile.edit'))
        ->put(route('distributor.profile.update'), [
            'penanggung_jawab' => '12345',
            'alamat_kantor' => '67890',
        ])
        ->assertSessionHasErrors(['penanggung_jawab', 'alamat_kantor']);
});

it('menolak jumlah penyaluran lebih dari 100 karton', function () {
    $distributor = Distributor::factory()->create();
    $user = User::factory()->create([
        'role' => Role::Distributor,
        'distributor_id' => $distributor->id,
    ]);
    $store = RecipientStore::factory()->create([
        'distributor_id' => $distributor->id,
    ]);

    $this->actingAs($user)
        ->post(route('distributor.distribution-reports.store'), [
            'distribution_date' => now()->toDateString(),
            'recipient_store_id' => $store->id,
            'cartons' => 101,
        ])
        ->assertSessionHasErrors('cartons');

    $this->assertDatabaseCount('distributions', 0);
});
