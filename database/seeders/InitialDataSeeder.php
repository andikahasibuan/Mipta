<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\Distributor;
use App\Models\Market;
use App\Models\UptdContributor;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InitialDataSeeder extends Seeder
{
    /**
     * Seed data awal aplikasi.
     *
     * Seeder ini idempoten: data yang sudah ada tidak ditimpa, termasuk password.
     */
    public function run(): void
    {
        DB::transaction(function (): void {
            $markets = $this->seedMarkets();
            $this->seedInternalUsers();
            $this->seedDistributors();
            $this->seedUptdContributors($markets);
        });

        $this->command?->warn(
            'Data awal memakai password default untuk tahap pengembangan. '
            .'Ganti seluruh password sebelum aplikasi digunakan di produksi.',
        );
    }

    /**
     * @return array<string, Market>
     */
    private function seedMarkets(): array
    {
        $markets = [
            ['name' => 'Pasar Raya Padang', 'lokasi' => 'Kampung Jao'],
            ['name' => 'Pasar Alai', 'lokasi' => 'Jl. Alai'],
            ['name' => 'Pasar Lubuk Buaya', 'lokasi' => 'Jl. Adinegoro'],
            ['name' => 'Pasar Nanggalo', 'lokasi' => 'Jl. Raya Siteba'],
            ['name' => 'Pasar Bandar Buat', 'lokasi' => 'Jl. Raya Bandar Buat'],
            ['name' => 'Pasar Ulak Karang', 'lokasi' => 'Jl. Paus No. 5'],
            ['name' => 'Pasar Belimbing', 'lokasi' => 'Jl. Pepaya Raya, Kuranji, Kec. Kuranji'],
            ['name' => 'Pasar Tanah Kongsi', 'lokasi' => 'Jl. Tanah Kongsi, Kampung Pondok'],
            ['name' => 'Pasar Siteba', 'lokasi' => 'Jl. Raya Siteba'],
        ];

        $seeded = [];

        foreach ($markets as $market) {
            $seeded[$market['name']] = Market::firstOrCreate(
                ['name' => $market['name']],
                ['lokasi' => $market['lokasi'], 'is_active' => true],
            );
        }

        return $seeded;
    }

    private function seedInternalUsers(): void
    {
        User::firstOrCreate(
            ['username' => 'admin'],
            [
                'name' => 'Administrator',
                'email' => null,
                'password' => 'admin123',
                'role' => Role::Admin->value,
                'market_id' => null,
                'distributor_id' => null,
                'is_active' => true,
            ],
        );

        User::firstOrCreate(
            ['username' => 'kepala.dinas'],
            [
                'name' => 'Kepala Dinas',
                'email' => null,
                'password' => 'kepala123',
                'role' => Role::KepalaDinas->value,
                'market_id' => null,
                'distributor_id' => null,
                'is_active' => true,
            ],
        );

        User::firstOrCreate(
            ['username' => 'uptd.pasar'],
            [
                'name' => 'UPTD Pasar',
                'email' => null,
                'password' => 'uptdpasar123',
                'role' => Role::UptdPasar->value,
                'market_id' => null,
                'distributor_id' => null,
                'is_active' => true,
            ],
        );
    }

    private function seedDistributors(): void
    {
        $distributors = [
            [
                'name' => 'Badan Urusan Logistik',
                'username' => 'bulog',
                'penanggung_jawab' => 'Hasan Rashid',
                'telepon' => '093464534576',
            ],
            [
                'name' => 'PT Rajawali Nusantara Indonesia',
                'username' => 'rajawalinusido',
                'penanggung_jawab' => 'Omar Dhani Piliang',
                'telepon' => '034343234356',
            ],
            [
                'name' => 'PT Perusahaan Perdagangan Indonesia',
                'username' => 'perdari',
                'penanggung_jawab' => 'Ismail Bin Mail',
                'telepon' => '073452764728',
            ],
            [
                'name' => 'PT Inti Nusa Distribusi',
                'username' => 'intinusa',
                'penanggung_jawab' => 'Sulaiman',
                'telepon' => '073425431234',
            ],
        ];

        foreach ($distributors as $data) {
            $distributor = Distributor::firstOrCreate(
                ['name' => $data['name']],
                [
                    'penanggung_jawab' => $data['penanggung_jawab'],
                    'telepon' => $data['telepon'],
                    'alamat_kantor' => null,
                    'is_active' => true,
                ],
            );

            User::firstOrCreate(
                ['distributor_id' => $distributor->id],
                [
                    'name' => $distributor->name,
                    'username' => $data['username'],
                    'email' => null,
                    'password' => 'distributor123',
                    'role' => Role::Distributor->value,
                    'market_id' => null,
                    'is_active' => true,
                ],
            );
        }
    }

    /**
     * @param  array<string, Market>  $markets
     */
    private function seedUptdContributors(array $markets): void
    {
        $contributors = [
            ['market' => 'Pasar Raya Padang', 'name' => 'Kontributor Raya Padang', 'username' => 'uptd.rayapadang'],
            ['market' => 'Pasar Alai', 'name' => 'Kontributor Alai', 'username' => 'uptd.alai'],
            ['market' => 'Pasar Lubuk Buaya', 'name' => 'Kontributor Lubuk Buaya', 'username' => 'uptd.lubukbuaya'],
            ['market' => 'Pasar Nanggalo', 'name' => 'Kontributor Nanggalo', 'username' => 'uptd.nanggalo'],
            ['market' => 'Pasar Bandar Buat', 'name' => 'Kontributor Bandar Buat', 'username' => 'uptd.bandarbuat'],
            ['market' => 'Pasar Ulak Karang', 'name' => 'Kontributor Ulak Karang', 'username' => 'uptd.ulakkarang'],
            ['market' => 'Pasar Belimbing', 'name' => 'Kontributor Belimbing', 'username' => 'uptd.belimbing'],
            ['market' => 'Pasar Tanah Kongsi', 'name' => 'Kontributor Tanah Kongsi', 'username' => 'uptd.tanahkongsi'],
            ['market' => 'Pasar Siteba', 'name' => 'Kontributor Siteba', 'username' => 'uptd.siteba'],
        ];

        foreach ($contributors as $data) {
            UptdContributor::firstOrCreate(
                ['market_id' => $markets[$data['market']]->id],
                [
                    'name' => $data['name'],
                    'username' => $data['username'],
                    'password' => 'uptdpasar123',
                    'is_active' => true,
                ],
            );
        }
    }
}
