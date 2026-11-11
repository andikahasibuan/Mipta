<?php

namespace Database\Seeders;

use App\Enums\FieldCondition;
use App\Enums\Role;
use App\Models\FieldDistributionReport;
use App\Models\Market;
use App\Models\MarketMonitoring;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    /**
     * Seed data contoh untuk kebutuhan pengujian.
     *
     * Seeder ini sengaja tidak dipanggil oleh DatabaseSeeder agar data demo
     * tidak masuk ke database aplikasi. Akun dan data distributor juga tidak
     * di-seed; distributor dibuat melalui menu Distributor agar akun
     * perusahaan dan data PT terbentuk bersamaan.
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

        $users = [
            [
                'name' => 'Andi Pratama',
                'username' => 'demo.uptd',
                'email' => null,
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
                ['username' => $user['username']],
                [
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'password' => 'password',
                    'role' => $user['role'],
                    'is_active' => true,
                ],
            );
        }

        $uptdUser = User::where('username', 'demo.uptd')->firstOrFail();
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
