<?php

use App\Enums\Role;
use App\Models\Distribution;
use App\Models\Distributor;
use App\Models\Market;
use App\Models\RecipientStore;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

it('menampilkan daftar toko penerima beserta pasarnya', function () {
    $market = Market::factory()->create(['name' => 'Pasar Raya Padang']);

    RecipientStore::factory()->create([
        'market_id' => $market->id,
        'name' => 'Toko Berkah',
        'alamat' => 'Jln. Berkah No. 1',
    ]);
    RecipientStore::factory()->create([
        'market_id' => $market->id,
        'name' => 'Toko Nonaktif',
        'is_active' => false,
    ]);

    $this->actingAs(User::factory()->create())
        ->get(route('admin.recipient-stores.index'))
        ->assertOk()
        ->assertSeeInOrder(['NO', 'Nama Toko', 'Alamat', 'NIB', 'Distributor', 'Status', 'Aksi'])
        ->assertDontSee('>Pasar</th>', false)
        ->assertSee('Toko Berkah')
        ->assertSee('Pasar Raya Padang, Jln. Berkah No. 1')
        ->assertSee('Nonaktif');
});

it('menampilkan toko yang dibuat distributor pada halaman toko penerima admin', function () {
    $distributor = Distributor::factory()->create(['name' => 'PT Amanah Niaga Mandiri']);
    $market = Market::factory()->create(['name' => 'Pasar Raya Padang']);

    RecipientStore::factory()->create([
        'distributor_id' => $distributor->id,
        'market_id' => $market->id,
        'name' => 'Toko Distributor',
        'alamat' => 'Jln. Merdeka No. 1',
    ]);

    $this->actingAs(User::factory()->create())
        ->get(route('admin.recipient-stores.index'))
        ->assertOk()
        ->assertSee('Toko Distributor')
        ->assertSee('Pasar Raya Padang, Jln. Merdeka No. 1')
        ->assertSee('PT Amanah Niaga Mandiri');
});

it('memfilter toko berdasarkan distributor', function () {
    $firstDistributor = Distributor::factory()->create(['name' => 'PT Distributor Satu']);
    $secondDistributor = Distributor::factory()->create(['name' => 'PT Distributor Dua']);
    $market = Market::factory()->create();

    RecipientStore::factory()->create([
        'distributor_id' => $firstDistributor->id,
        'market_id' => $market->id,
        'name' => 'Toko Distributor Satu',
    ]);

    RecipientStore::factory()->create([
        'distributor_id' => $secondDistributor->id,
        'market_id' => $market->id,
        'name' => 'Toko Distributor Dua',
    ]);

    $this->actingAs(User::factory()->create())
        ->get(route('admin.recipient-stores.index', ['distributor' => $firstDistributor->id]))
        ->assertOk()
        ->assertSee('Toko Distributor Satu')
        ->assertDontSee('Toko Distributor Dua');
});

it('menyimpan alamat dan distributor saat admin menambah toko', function () {
    $distributor = Distributor::factory()->create();
    $market = Market::factory()->create();

    $this->actingAs(User::factory()->create())
        ->post(route('admin.recipient-stores.store'), [
            'market_id' => $market->id,
            'distributor_id' => $distributor->id,
            'name' => 'Toko Baru Admin',
            'alamat' => 'Jln. Baru No. 1',
            'nib' => '1234567890123',
            'is_active' => '1',
        ])
        ->assertRedirect(route('admin.recipient-stores.index'));

    $this->assertDatabaseHas('recipient_stores', [
        'distributor_id' => $distributor->id,
        'market_id' => $market->id,
        'name' => 'Toko Baru Admin',
        'alamat' => 'Jln. Baru No. 1',
    ]);
});

it('memperbarui alamat dan distributor toko dari admin', function () {
    $distributor = Distributor::factory()->create();
    $market = Market::factory()->create();
    $store = RecipientStore::factory()->create(['market_id' => $market->id]);

    $this->actingAs(User::factory()->create())
        ->put(route('admin.recipient-stores.update', $store), [
            'market_id' => $market->id,
            'distributor_id' => $distributor->id,
            'name' => $store->name,
            'alamat' => 'Alamat Baru',
            'nib' => '9876543210123',
            'is_active' => '1',
        ])
        ->assertRedirect(route('admin.recipient-stores.index'));

    $this->assertDatabaseHas('recipient_stores', [
        'id' => $store->id,
        'distributor_id' => $distributor->id,
        'alamat' => 'Alamat Baru',
        'nib' => '9876543210123',
    ]);
});

it('menyimpan toko admin tanpa detail alamat', function () {
    $market = Market::factory()->create(['name' => 'Pasar Tanpa Detail']);

    $this->actingAs(User::factory()->create())
        ->post(route('admin.recipient-stores.store'), [
            'market_id' => $market->id,
            'name' => 'Toko Tanpa Detail',
            'nib' => '1234567890123',
        ])
        ->assertRedirect(route('admin.recipient-stores.index'));

    $this->assertDatabaseHas('recipient_stores', [
        'name' => 'Toko Tanpa Detail',
        'market_id' => $market->id,
        'alamat' => null,
    ]);

    $this->get(route('admin.recipient-stores.index'))
        ->assertOk()
        ->assertSee('Pasar Tanpa Detail');
});

it('menampilkan form toko dengan petunjuk alamat yang ringkas', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('admin.recipient-stores.create'))
        ->assertOk()
        ->assertSee('Alamat (opsional)')
        ->assertSee('Tambahkan detail alamat bila diperlukan.')
        ->assertDontSee('Nama pasar otomatis menjadi alamat.')
        ->assertDontSee('Contoh: Jln. Merdeka No. 1, Gang Mawar');
});

it('menampilkan detail alamat lama dalam huruf besar', function () {
    $market = Market::factory()->create(['name' => 'Pasar Lama']);

    DB::table('recipient_stores')->insert([
        'market_id' => $market->id,
        'name' => 'Toko Lama',
        'alamat' => 'Jln. Lama No. 1',
        'nib' => '1234567890123',
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->actingAs(User::factory()->create())
        ->get(route('admin.recipient-stores.index'))
        ->assertOk()
        ->assertSee('Pasar Lama, Jln. Lama No. 1');
});

it('mewajibkan NIB saat admin menambah toko', function () {
    $market = Market::factory()->create();

    $this->actingAs(User::factory()->create())
        ->post(route('admin.recipient-stores.store'), [
            'market_id' => $market->id,
            'name' => 'Toko Tanpa NIB',
            'alamat' => 'Jln. Merdeka No. 1',
        ])
        ->assertSessionHasErrors('nib');
});

it('memvalidasi format nama, alamat, dan NIB toko dari admin', function () {
    $market = Market::factory()->create();

    $this->actingAs(User::factory()->create())
        ->post(route('admin.recipient-stores.store'), [
            'market_id' => $market->id,
            'name' => 'Toko !@#',
            'alamat' => 'Jln. Merdeka @ 1',
            'nib' => '12345ABC90123',
        ])
        ->assertSessionHasErrors(['name', 'alamat', 'nib']);

    $this->assertDatabaseCount('recipient_stores', 0);
});

it('menolak menghapus toko penerima yang memiliki riwayat penyaluran', function () {
    $store = RecipientStore::factory()->create();
    $distribution = Distribution::factory()->create([
        'recipient_store_id' => $store->id,
        'market_id' => $store->market_id,
    ]);

    $this->actingAs(User::factory()->create())
        ->from(route('admin.recipient-stores.index'))
        ->delete(route('admin.recipient-stores.destroy', $store))
        ->assertRedirect(route('admin.recipient-stores.index'))
        ->assertSessionHasErrors('delete');

    $this->assertDatabaseHas('recipient_stores', ['id' => $store->id]);
    $this->assertDatabaseHas('distributions', ['id' => $distribution->id]);
});

it('menghapus toko penerima yang belum memiliki riwayat penyaluran', function () {
    $store = RecipientStore::factory()->create();

    $this->actingAs(User::factory()->create())
        ->delete(route('admin.recipient-stores.destroy', $store))
        ->assertRedirect(route('admin.recipient-stores.index'));

    $this->assertDatabaseMissing('recipient_stores', ['id' => $store->id]);
});

it('menampilkan keadaan kosong saat belum ada toko penerima', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('admin.recipient-stores.index'))
        ->assertOk()
        ->assertSee('Belum ada data toko penerima');
});

it('menampilkan navigasi tab data master hingga toko penerima', function () {
    $response = $this->actingAs(User::factory()->create())
        ->get(route('admin.recipient-stores.index'));

    $response->assertOk();

    preg_match('/<nav[^>]*aria-label="Navigasi Data Master".*?<\/nav>/s', $response->getContent(), $matches);
    $tabs = $matches[0] ?? '';

    expect($tabs)
        ->toContain(route('admin.markets.index'))
        ->toContain(route('admin.recipient-stores.index'));

    expect($tabs)->not->toContain(route('admin.users.index'));
    expect($tabs)->not->toContain(route('admin.distributors.index'));
    expect($tabs)->not->toContain(route('admin.users.uptd-contributors.index'));
});

it('menampilkan sepuluh toko per halaman', function () {
    foreach (range(1, 11) as $number) {
        RecipientStore::factory()->create([
            'name' => 'Toko '.str_pad((string) $number, 2, '0', STR_PAD_LEFT),
        ]);
    }

    $this->actingAs(User::factory()->create())
        ->get(route('admin.recipient-stores.index'))
        ->assertOk()
        ->assertSee('Menampilkan 10 dari 11 toko')
        ->assertSee('?page=2', false)
        ->assertSeeInOrder(['Toko 11', 'Toko 10', 'Toko 09'])
        ->assertDontSee('Toko 01');

    $this->get(route('admin.recipient-stores.index', ['page' => 2]))
        ->assertOk()
        ->assertSee('Menampilkan 1 dari 11 toko')
        ->assertSee('Toko 01')
        ->assertDontSee('Toko 11');
});

it('mencari toko berdasarkan nama atau alamat', function () {
    $admin = User::factory()->create();

    RecipientStore::factory()->create(['name' => 'Toko Berkah', 'alamat' => 'Jln. Berkah No. 1']);
    RecipientStore::factory()->create(['name' => 'Toko Makmur', 'alamat' => 'Jln. Makmur No. 2']);

    $this->actingAs($admin)
        ->get(route('admin.recipient-stores.index', ['q' => 'Berkah']))
        ->assertOk()
        ->assertSee('Toko Berkah')
        ->assertDontSee('Toko Makmur');

    $this->actingAs($admin)
        ->get(route('admin.recipient-stores.index', ['q' => 'Jln. Makmur']))
        ->assertOk()
        ->assertSee('Toko Makmur')
        ->assertDontSee('Toko Berkah');
});

it('mencari toko berdasarkan nama pasar pada alamat', function () {
    $admin = User::factory()->create();
    $firstMarket = Market::factory()->create(['name' => 'Pasar Pencarian Alpha']);
    $secondMarket = Market::factory()->create(['name' => 'Pasar Pencarian Beta']);

    RecipientStore::factory()->create([
        'market_id' => $firstMarket->id,
        'name' => 'Toko Alpha',
    ]);
    RecipientStore::factory()->create([
        'market_id' => $secondMarket->id,
        'name' => 'Toko Beta',
    ]);

    $this->actingAs($admin)
        ->get(route('admin.recipient-stores.index', ['q' => 'Pencarian Alpha']))
        ->assertOk()
        ->assertSee('Toko Alpha')
        ->assertDontSee('Toko Beta');
});

it('memfilter toko berdasarkan pasar dan status', function () {
    $admin = User::factory()->create();
    $firstMarket = Market::factory()->create(['name' => 'Pasar Satu']);
    $secondMarket = Market::factory()->create(['name' => 'Pasar Dua']);

    RecipientStore::factory()->create([
        'name' => 'Toko Pasar Satu',
        'market_id' => $firstMarket->id,
    ]);
    RecipientStore::factory()->create([
        'name' => 'Toko Pasar Dua',
        'market_id' => $secondMarket->id,
        'is_active' => false,
    ]);

    $this->actingAs($admin)
        ->get(route('admin.recipient-stores.index', ['market' => $firstMarket->id]))
        ->assertOk()
        ->assertSee('Toko Pasar Satu')
        ->assertDontSee('Toko Pasar Dua');

    $this->actingAs($admin)
        ->get(route('admin.recipient-stores.index', ['status' => 'nonaktif']))
        ->assertOk()
        ->assertSee('Toko Pasar Dua')
        ->assertDontSee('Toko Pasar Satu');
});

it('menampilkan pesan kosong saat filter toko tidak menemukan data', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('admin.recipient-stores.index', ['q' => 'tidak-ada']))
        ->assertOk()
        ->assertSee('Tidak ada toko penerima yang sesuai filter')
        ->assertDontSee('Belum ada data toko penerima');
});

it('mempertahankan filter pasar pada tautan pagination toko', function () {
    $market = Market::factory()->create();
    RecipientStore::factory()->count(11)->create(['market_id' => $market->id]);

    $this->actingAs(User::factory()->create())
        ->get(route('admin.recipient-stores.index', ['market' => $market->id]))
        ->assertOk()
        ->assertSee('market='.$market->id, false);
});

it('membatasi halaman toko penerima hanya untuk admin', function () {
    $this->get(route('admin.recipient-stores.index'))->assertRedirect(route('login'));

    $this->actingAs(User::factory()->create(['role' => Role::Distributor]))
        ->get(route('admin.recipient-stores.index'))
        ->assertForbidden();
});

it('menolak memindahkan toko yang sudah memiliki riwayat penyaluran', function () {
    $market = Market::factory()->create();
    $otherMarket = Market::factory()->create();
    $store = RecipientStore::factory()->create([
        'market_id' => $market->id,
        'nib' => '1234567890123',
    ]);
    Distribution::factory()->create([
        'recipient_store_id' => $store->id,
        'market_id' => $store->market_id,
    ]);

    $this->actingAs(User::factory()->create())
        ->from(route('admin.recipient-stores.index'))
        ->put(route('admin.recipient-stores.update', $store), [
            'market_id' => $otherMarket->id,
            'distributor_id' => $store->distributor_id,
            'name' => $store->name,
            'alamat' => $store->alamat,
            'nib' => $store->nib,
            'is_active' => '1',
        ])
        ->assertRedirect(route('admin.recipient-stores.index'))
        ->assertSessionHasErrors('market_id');

    expect($store->fresh()->market_id)->toBe($market->id);
});

it('menolak NIB toko yang sudah digunakan', function () {
    $market = Market::factory()->create();
    RecipientStore::factory()->create([
        'market_id' => $market->id,
        'nib' => '1234567890123',
    ]);

    $this->actingAs(User::factory()->create())
        ->post(route('admin.recipient-stores.store'), [
            'market_id' => $market->id,
            'name' => 'Toko Baru',
            'alamat' => 'Jln. Baru No. 1',
            'nib' => '1234567890123',
        ])
        ->assertSessionHasErrors('nib');
});
