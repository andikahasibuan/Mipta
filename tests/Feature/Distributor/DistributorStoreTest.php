<?php

use App\Enums\Role;
use App\Models\Distribution;
use App\Models\Distributor;
use App\Models\Market;
use App\Models\RecipientStore;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Buat distributor beserta akun login-nya.
 *
 * @return array{0: Distributor, 1: User}
 */
function storeDistributorAccount(string $name = 'PT Pemilik Toko'): array
{
    $distributor = Distributor::factory()->create(['name' => $name]);

    $user = User::factory()->create([
        'role' => Role::Distributor,
        'distributor_id' => $distributor->id,
    ]);

    return [$distributor, $user];
}

it('menampilkan daftar toko milik distributor dengan subjudul', function () {
    [$distributor, $user] = storeDistributorAccount();
    [, $otherUser] = storeDistributorAccount('PT Distributor Lain');
    $market = Market::factory()->create(['name' => 'Pasar Raya Padang']);

    $store = RecipientStore::factory()->create([
        'distributor_id' => $distributor->id,
        'market_id' => $market->id,
        'name' => 'Toko Milik Sendiri',
        'alamat' => 'Jln. Pasar Raya No. 1',
    ]);

    RecipientStore::factory()->create([
        'distributor_id' => $otherUser->distributor_id,
        'market_id' => $market->id,
        'name' => 'Toko Distributor Lain',
    ]);

    $this->actingAs($user)
        ->get(route('distributor.stores.index'))
        ->assertOk()
        ->assertSee('Data Toko')
        ->assertSee('Tambah data')
        ->assertSee(route('distributor.stores.create'), false)
        ->assertSeeInOrder(['Nama Toko', 'Alamat', 'NIB', 'Aksi'])
        ->assertDontSee('>Pasar</th>', false)
        ->assertDontSee('ID Data')
        ->assertDontSee('Status')
        ->assertSee('Toko Milik Sendiri')
        ->assertSee('Pasar Raya Padang, Jln. Pasar Raya No. 1')
        ->assertDontSee('Toko Distributor Lain')
        ->assertSee(route('distributor.stores.edit', $store), false)
        ->assertDontSee('aria-label="Hapus"', false);
});

it('memfilter daftar toko berdasarkan nama, alamat, NIB, dan pasar', function () {
    [$distributor, $user] = storeDistributorAccount();
    $firstMarket = Market::factory()->create(['name' => 'Pasar Alpha']);
    $secondMarket = Market::factory()->create(['name' => 'Pasar Beta']);

    RecipientStore::factory()->create([
        'distributor_id' => $distributor->id,
        'market_id' => $firstMarket->id,
        'name' => 'Toko Alpha',
        'alamat' => 'Jln. Alpha No. 1',
        'nib' => '1111111111111',
    ]);

    RecipientStore::factory()->create([
        'distributor_id' => $distributor->id,
        'market_id' => $secondMarket->id,
        'name' => 'Toko Beta',
        'alamat' => 'Jln. Beta No. 2',
        'nib' => '2222222222222',
    ]);

    $this->actingAs($user);

    $this->get(route('distributor.stores.index', ['q' => 'Alpha']))
        ->assertOk()
        ->assertSee('Toko Alpha')
        ->assertDontSee('Toko Beta');

    $this->get(route('distributor.stores.index', ['q' => '2222222222222']))
        ->assertOk()
        ->assertSee('Toko Beta')
        ->assertDontSee('Toko Alpha');

    $this->get(route('distributor.stores.index', ['q' => 'Pasar Beta']))
        ->assertOk()
        ->assertSee('Toko Beta')
        ->assertDontSee('Toko Alpha');

    $this->get(route('distributor.stores.index', ['market' => $secondMarket->id]))
        ->assertOk()
        ->assertSee('Toko Beta')
        ->assertDontSee('Toko Alpha');
});

it('menampilkan sepuluh toko per halaman dengan pagination', function () {
    [$distributor, $user] = storeDistributorAccount();
    $market = Market::factory()->create();

    foreach (range(1, 11) as $index) {
        RecipientStore::factory()->create([
            'distributor_id' => $distributor->id,
            'market_id' => $market->id,
            'name' => 'Toko '.str_pad((string) $index, 2, '0', STR_PAD_LEFT),
        ]);
    }

    $this->actingAs($user)
        ->get(route('distributor.stores.index'))
        ->assertOk()
        ->assertSee('Menampilkan 10 dari 11 toko')
        ->assertSee('?page=2', false)
        ->assertSeeInOrder(['Toko 11', 'Toko 10', 'Toko 09'])
        ->assertDontSee('Toko 01');

    $this->get(route('distributor.stores.index', ['page' => 2]))
        ->assertOk()
        ->assertSee('Toko 01')
        ->assertDontSee('Toko 11');
});

it('menampilkan formulir tambah toko penerima', function () {
    [, $user] = storeDistributorAccount();

    $this->actingAs($user)
        ->get(route('distributor.stores.create'))
        ->assertOk()
        ->assertSee('Tambah toko penerima')
        ->assertSee('Pasar')
        ->assertSee('Nama Toko')
        ->assertSee('Alamat (opsional)')
        ->assertSee('Tambahkan detail alamat bila diperlukan.')
        ->assertDontSee('Nama pasar otomatis menjadi alamat.')
        ->assertDontSee('Contoh: Jln. Merdeka No. 1, Gang Mawar')
        ->assertSee('NIB')
        ->assertDontSee('Status aktif')
        ->assertSee('action="'.route('distributor.stores.store').'"', false);
});

it('menyimpan toko baru untuk distributor yang login', function () {
    [$distributor, $user] = storeDistributorAccount();
    $market = Market::factory()->create();

    $this->actingAs($user)
        ->post(route('distributor.stores.store'), [
            'market_id' => $market->id,
            'name' => 'Toko Baru',
            'alamat' => 'Jln. Baru No. 10',
            'nib' => '1234567890123',
        ])
        ->assertRedirect(route('distributor.stores.index'));

    $this->assertDatabaseHas('recipient_stores', [
        'distributor_id' => $distributor->id,
        'market_id' => $market->id,
        'name' => 'Toko Baru',
        'alamat' => 'Jln. Baru No. 10',
        'nib' => '1234567890123',
        'is_active' => true,
    ]);
});

it('mewajibkan pasar, nama, dan NIB saat menyimpan toko', function () {
    [, $user] = storeDistributorAccount();

    $this->actingAs($user)
        ->post(route('distributor.stores.store'), [
            'market_id' => '',
            'name' => '',
            'nib' => '',
        ])
        ->assertSessionHasErrors(['market_id', 'name', 'nib']);
});

it('menyimpan toko tanpa detail alamat', function () {
    [$distributor, $user] = storeDistributorAccount();
    $market = Market::factory()->create(['name' => 'Pasar Tanpa Detail']);

    $this->actingAs($user)
        ->post(route('distributor.stores.store'), [
            'market_id' => $market->id,
            'name' => 'Toko Tanpa Detail',
            'nib' => '1234567890123',
        ])
        ->assertRedirect(route('distributor.stores.index'));

    $this->assertDatabaseHas('recipient_stores', [
        'distributor_id' => $distributor->id,
        'market_id' => $market->id,
        'name' => 'Toko Tanpa Detail',
        'alamat' => null,
    ]);

    $this->get(route('distributor.stores.index'))
        ->assertOk()
        ->assertSee('Pasar Tanpa Detail');
});

it('memvalidasi format nama, alamat, dan NIB toko distributor', function () {
    [, $user] = storeDistributorAccount();
    $market = Market::factory()->create();

    $this->actingAs($user)
        ->post(route('distributor.stores.store'), [
            'market_id' => $market->id,
            'name' => 'Toko !@#',
            'alamat' => 'Jln. Merdeka @ 1',
            'nib' => '12345ABC90123',
        ])
        ->assertSessionHasErrors(['name', 'alamat', 'nib']);

    $this->assertDatabaseCount('recipient_stores', 0);
});

it('memperbarui toko milik sendiri', function () {
    [$distributor, $user] = storeDistributorAccount();
    $market = Market::factory()->create();
    $store = RecipientStore::factory()->create([
        'distributor_id' => $distributor->id,
        'market_id' => $market->id,
        'name' => 'Nama Lama',
        'alamat' => 'Alamat Lama',
        'is_active' => false,
    ]);

    $this->actingAs($user)
        ->get(route('distributor.stores.edit', $store))
        ->assertOk()
        ->assertSee('Nama Lama')
        ->assertSee('Alamat Lama');

    $this->put(route('distributor.stores.update', $store), [
        'market_id' => $market->id,
        'name' => 'Nama Baru',
        'alamat' => 'Alamat Baru',
        'nib' => '9876543210123',
    ])->assertRedirect(route('distributor.stores.index'));

    $this->assertDatabaseHas('recipient_stores', [
        'id' => $store->id,
        'distributor_id' => $distributor->id,
        'name' => 'Nama Baru',
        'alamat' => 'Alamat Baru',
        'nib' => '9876543210123',
        'is_active' => false,
    ]);
});

it('menolak mengakses toko milik distributor lain', function () {
    [, $user] = storeDistributorAccount();
    [, $otherUser] = storeDistributorAccount('PT Distributor Lain');
    $store = RecipientStore::factory()->create([
        'distributor_id' => $otherUser->distributor_id,
        'name' => 'Toko Distributor Lain',
    ]);

    $this->actingAs($user)
        ->get(route('distributor.stores.edit', $store))
        ->assertNotFound();

    $this->put(route('distributor.stores.update', $store), [
        'market_id' => $store->market_id,
        'name' => 'Diubah',
        'alamat' => 'Diubah',
    ])->assertNotFound();

    $this->assertDatabaseHas('recipient_stores', [
        'id' => $store->id,
        'name' => 'Toko Distributor Lain',
    ]);
});

it('mengirim notifikasi penambahan toko ke admin saja', function () {
    [, $user] = storeDistributorAccount('PT Rajawali Nusantara');
    $market = Market::factory()->create(['name' => 'Pasar Raya Padang']);

    $admin = User::factory()->create(['role' => Role::Admin]);
    $kepalaDinas = User::factory()->create(['role' => Role::KepalaDinas]);
    $inactiveAdmin = User::factory()->inactive()->create(['role' => Role::Admin]);
    $uptd = User::factory()->create(['role' => Role::UptdPasar]);
    $otherDistributor = User::factory()->distributor()->create();

    $this->actingAs($user)
        ->post(route('distributor.stores.store'), [
            'market_id' => $market->id,
            'name' => 'Toko Baru',
            'alamat' => 'Jln. Baru No. 1',
            'nib' => '1234567890123',
        ])
        ->assertRedirect(route('distributor.stores.index'));

    $expectedMessage = 'Toko Toko Baru telah ditambahkan oleh PT Rajawali Nusantara.';

    $this->assertDatabaseCount('notifications', 1);

    expect($admin->notifications()->first()?->data['title'])->toBe('Toko penerima baru')
        ->and($admin->notifications()->first()?->data['message'])->toBe($expectedMessage)
        ->and($kepalaDinas->notifications()->count())->toBe(0)
        ->and($inactiveAdmin->notifications()->count())->toBe(0)
        ->and($uptd->notifications()->count())->toBe(0)
        ->and($otherDistributor->notifications()->count())->toBe(0);
});

it('mengirim notifikasi perubahan toko ke admin saja', function () {
    [$distributor, $user] = storeDistributorAccount('PT Rajawali Nusantara');
    $market = Market::factory()->create(['name' => 'Pasar Raya Padang']);
    $store = RecipientStore::factory()->create([
        'distributor_id' => $distributor->id,
        'market_id' => $market->id,
        'name' => 'Nama Lama',
        'alamat' => 'Alamat Lama',
        'nib' => '1111111111111',
    ]);

    $admin = User::factory()->create(['role' => Role::Admin]);
    $kepalaDinas = User::factory()->create(['role' => Role::KepalaDinas]);
    $inactiveAdmin = User::factory()->inactive()->create(['role' => Role::Admin]);

    $this->actingAs($user)
        ->put(route('distributor.stores.update', $store), [
            'market_id' => $market->id,
            'name' => 'Nama Baru',
            'alamat' => 'Alamat Baru',
            'nib' => '9876543210123',
        ])
        ->assertRedirect(route('distributor.stores.index'));

    $expectedMessage = 'Data pada toko Nama Baru telah diperbarui oleh PT Rajawali Nusantara.';

    $this->assertDatabaseCount('notifications', 1);

    expect($admin->notifications()->first()?->data['title'])->toBe('Toko penerima diperbarui')
        ->and($admin->notifications()->first()?->data['message'])->toBe($expectedMessage)
        ->and($kepalaDinas->notifications()->count())->toBe(0)
        ->and($inactiveAdmin->notifications()->count())->toBe(0);
});

it('tidak mengirim notifikasi saat data toko diedit tanpa perubahan', function () {
    [$distributor, $user] = storeDistributorAccount();
    $market = Market::factory()->create();
    $store = RecipientStore::factory()->create([
        'distributor_id' => $distributor->id,
        'market_id' => $market->id,
        'name' => 'Nama Tetap',
        'alamat' => 'Alamat Tetap',
        'nib' => '1111111111111',
    ]);

    User::factory()->create(['role' => Role::Admin]);

    $this->actingAs($user)
        ->put(route('distributor.stores.update', $store), [
            'market_id' => $market->id,
            'name' => 'Nama Tetap',
            'alamat' => 'Alamat Tetap',
            'nib' => '1111111111111',
        ])
        ->assertRedirect(route('distributor.stores.index'));

    $this->assertDatabaseCount('notifications', 0);
});

it('membatasi halaman manajemen toko hanya untuk distributor', function () {
    $this->get(route('distributor.stores.index'))->assertRedirect(route('login'));

    $this->actingAs(User::factory()->create())
        ->get(route('distributor.stores.index'))
        ->assertForbidden();
});

it('menolak memindahkan toko yang sudah memiliki riwayat penyaluran', function () {
    [$distributor, $user] = storeDistributorAccount();
    $market = Market::factory()->create();
    $otherMarket = Market::factory()->create();
    $store = RecipientStore::factory()->create([
        'distributor_id' => $distributor->id,
        'market_id' => $market->id,
        'nib' => '1234567890123',
    ]);
    Distribution::factory()->create([
        'distributor_id' => $distributor->id,
        'recipient_store_id' => $store->id,
        'market_id' => $store->market_id,
    ]);

    $this->actingAs($user)
        ->from(route('distributor.stores.index'))
        ->put(route('distributor.stores.update', $store), [
            'market_id' => $otherMarket->id,
            'name' => $store->name,
            'alamat' => $store->alamat,
            'nib' => $store->nib,
        ])
        ->assertRedirect(route('distributor.stores.index'))
        ->assertSessionHasErrors('market_id');

    expect($store->fresh()->market_id)->toBe($market->id);
});

it('menolak NIB toko yang sudah digunakan', function () {
    [$distributor, $user] = storeDistributorAccount();
    $market = Market::factory()->create();
    RecipientStore::factory()->create([
        'market_id' => $market->id,
        'nib' => '1234567890123',
    ]);

    $this->actingAs($user)
        ->post(route('distributor.stores.store'), [
            'market_id' => $market->id,
            'name' => 'Toko Baru',
            'alamat' => 'Jln. Baru No. 1',
            'nib' => '1234567890123',
        ])
        ->assertSessionHasErrors('nib');
});
