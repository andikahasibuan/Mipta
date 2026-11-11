<?php

namespace Database\Seeders;

use App\Models\Distributor;
use App\Models\Market;
use App\Models\RecipientStore;
use Illuminate\Database\Seeder;

class RecipientStoreSeeder extends Seeder
{
    /**
     * Seed lima toko penerima untuk masing-masing distributor.
     */
    public function run(): void
    {
        $stores = [
            [
                'distributor' => 'Badan Urusan Logistik',
                'market' => 'Pasar Raya Padang',
                'name' => 'Toko Bulog 01',
                'alamat' => 'Jl. Raya No. 12',
                'nib' => '1000000000001',
            ],
            [
                'distributor' => 'Badan Urusan Logistik',
                'market' => 'Pasar Alai',
                'name' => 'Toko Bulog 02',
                'alamat' => 'Jl. Alai Baru No. 8',
                'nib' => '1000000000002',
            ],
            [
                'distributor' => 'Badan Urusan Logistik',
                'market' => 'Pasar Lubuk Buaya',
                'name' => 'Toko Bulog 03',
                'alamat' => 'Jl. Buaya No. 5',
                'nib' => '1000000000003',
            ],
            [
                'distributor' => 'Badan Urusan Logistik',
                'market' => 'Pasar Nanggalo',
                'name' => 'Toko Bulog 04',
                'alamat' => 'Jl. Perjuangan No. 21',
                'nib' => '1000000000004',
            ],
            [
                'distributor' => 'Badan Urusan Logistik',
                'market' => 'Pasar Bandar Buat',
                'name' => 'Toko Bulog 05',
                'alamat' => 'Jl. Baru No. 17',
                'nib' => '1000000000005',
            ],
            [
                'distributor' => 'PT Rajawali Nusantara Indonesia',
                'market' => 'Pasar Ulak Karang',
                'name' => 'Toko Rajawali 01',
                'alamat' => 'Jl. Teuku Umar No. 9',
                'nib' => '2000000000001',
            ],
            [
                'distributor' => 'PT Rajawali Nusantara Indonesia',
                'market' => 'Pasar Belimbing',
                'name' => 'Toko Rajawali 02',
                'alamat' => 'Jl. Samudra No. 14',
                'nib' => '2000000000002',
            ],
            [
                'distributor' => 'PT Rajawali Nusantara Indonesia',
                'market' => 'Pasar Tanah Kongsi',
                'name' => 'Toko Rajawali 03',
                'alamat' => 'Jl. Banteng Perjuangan No. 6',
                'nib' => '2000000000003',
            ],
            [
                'distributor' => 'PT Rajawali Nusantara Indonesia',
                'market' => 'Pasar Siteba',
                'name' => 'Toko Rajawali 04',
                'alamat' => 'Jl. Ujung Padang No. 11',
                'nib' => '2000000000004',
            ],
            [
                'distributor' => 'PT Rajawali Nusantara Indonesia',
                'market' => 'Pasar Raya Padang',
                'name' => 'Toko Rajawali 05',
                'alamat' => 'Jl. Raya No. 33',
                'nib' => '2000000000005',
            ],
            [
                'distributor' => 'PT Perusahaan Perdagangan Indonesia',
                'market' => 'Pasar Alai',
                'name' => 'Toko PPI 01',
                'alamat' => 'Jl. Alai Baru No. 25',
                'nib' => '3000000000001',
            ],
            [
                'distributor' => 'PT Perusahaan Perdagangan Indonesia',
                'market' => 'Pasar Lubuk Buaya',
                'name' => 'Toko PPI 02',
                'alamat' => 'Jl. Buaya No. 18',
                'nib' => '3000000000002',
            ],
            [
                'distributor' => 'PT Perusahaan Perdagangan Indonesia',
                'market' => 'Pasar Nanggalo',
                'name' => 'Toko PPI 03',
                'alamat' => 'Jl. Perjuangan No. 7',
                'nib' => '3000000000003',
            ],
            [
                'distributor' => 'PT Perusahaan Perdagangan Indonesia',
                'market' => 'Pasar Bandar Buat',
                'name' => 'Toko PPI 04',
                'alamat' => 'Jl. Baru No. 29',
                'nib' => '3000000000004',
            ],
            [
                'distributor' => 'PT Perusahaan Perdagangan Indonesia',
                'market' => 'Pasar Ulak Karang',
                'name' => 'Toko PPI 05',
                'alamat' => 'Jl. Teuku Umar No. 41',
                'nib' => '3000000000005',
            ],
        ];

        foreach ($stores as $store) {
            $distributor = Distributor::where('name', $store['distributor'])->firstOrFail();
            $market = Market::where('name', $store['market'])->firstOrFail();

            RecipientStore::updateOrCreate(
                ['nib' => $store['nib']],
                [
                    'distributor_id' => $distributor->id,
                    'market_id' => $market->id,
                    'name' => $store['name'],
                    'alamat' => $store['alamat'],
                    'is_active' => true,
                ],
            );
        }
    }
}
