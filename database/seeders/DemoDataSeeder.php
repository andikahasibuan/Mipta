<?php

namespace Database\Seeders;

use App\Enums\FieldCondition;
use App\Enums\Role;
use App\Models\FieldDistributionReport;
use App\Models\Market;
use App\Models\MarketMonitoring;
use App\Models\RecipientStore;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    /**
     * Seed data contoh untuk dashboard dan menu pengguna & data master.
     *
     * Akun dan data distributor sengaja tidak di-seed. Distributor dibuat
     * melalui menu Distributor agar akun perusahaan dan data PT terbentuk
     * bersamaan.
     */
    public function run(): void
    {
        $markets = [
            ['name' => 'Pasar Raya Padang', 'lokasi' => 'Jln. Merdeka'],
            ['name' => 'Pasar Bandar Buat', 'lokasi' => 'Jln. Pattimura'],
            ['name' => 'Pasar Lubuk Buaya', 'lokasi' => 'Jln. Adinegoro'],
        ];

        foreach ($markets as $market) {
            Market::updateOrCreate(
                ['name' => $market['name']],
                [
                    'lokasi' => $market['lokasi'],
                    'is_active' => true,
                ],
            );
        }

        $stores = [
            ['name' => 'Toko Atong', 'market' => 'Pasar Raya Padang'],
            ['name' => 'Toko Mak Itam', 'market' => 'Pasar Raya Padang'],
            ['name' => 'Toko Mail', 'market' => 'Pasar Bandar Buat'],
            ['name' => 'Toko Hisyam', 'market' => 'Pasar Lubuk Buaya'],
        ];

        foreach ($stores as $store) {
            $market = Market::where('name', $store['market'])->firstOrFail();

            RecipientStore::updateOrCreate(
                ['name' => $store['name']],
                [
                    'market_id' => $market->id,
                    'nib' => fake()->numerify('#############'),
                    'is_active' => true,
                ],
            );
        }

        $users = [
            [
                'name' => 'Andi Pratama',
                'username' => 'uptd',
                'email' => 'uptd@mipta.test',
                'role' => Role::UptdPasar,
            ],
            [
                'name' => 'Dewi Anggraini',
                'username' => 'kepala.dinas',
                'email' => 'kepala.dinas@mipta.test',
                'role' => Role::KepalaDinas,
            ],
        ];

        foreach ($users as $user) {
            User::updateOrCreate(
                ['email' => $user['email']],
                [
                    'name' => $user['name'],
                    'username' => $user['username'],
                    'password' => 'password',
                    'role' => $user['role'],
                    'is_active' => true,
                ],
            );
        }

        $uptdUser = User::where('email', 'uptd@mipta.test')->firstOrFail();
        $uptdUser->update(['market_id' => Market::where('name', 'Pasar Raya Padang')->value('id')]);
        $currentMonthStart = now()->startOfMonth();
        $previousMonthStart = now()->subMonthNoOverflow()->startOfMonth();

        foreach ([$currentMonthStart, $previousMonthStart] as $monthStart) {
            foreach (Market::whereIn('name', ['Pasar Raya Padang', 'Pasar Bandar Buat'])->get() as $market) {
                MarketMonitoring::updateOrCreate(
                    [
                        'market_id' => $market->id,
                        'monitored_at' => $monthStart->toDateString(),
                    ],
                    ['uptd_user_id' => $uptdUser->id],
                );
            }
        }

        $fieldReportSamples = [
            ['Pasar Raya Padang', 'Stok Minyakita tersedia, harga sesuai HET', FieldCondition::Normal],
            ['Pasar Bandar Buat', 'Stok Minyakita menipis di beberapa kios', FieldCondition::PerluPerhatian],
        ];

        foreach ($fieldReportSamples as [$marketName, $description, $condition]) {
            $market = Market::where('name', $marketName)->firstOrFail();

            FieldDistributionReport::updateOrCreate(
                [
                    'market_id' => $market->id,
                    'report_date' => now()->toDateString(),
                ],
                [
                    'uptd_user_id' => $uptdUser->id,
                    'description' => $description,
                    'condition' => $condition->value,
                ],
            );
        }
    }
}
