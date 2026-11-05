<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\Distribution;
use App\Models\Distributor;
use App\Models\Market;
use App\Models\MarketMonitoring;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    /**
     * Seed data contoh untuk dashboard dan menu pengguna & data master.
     */
    public function run(): void
    {
        $distributors = [
            ['code' => 'DST-001', 'name' => 'PT Amanah Niaga Mandiri'],
            ['code' => 'DST-002', 'name' => 'PT Sinar Pangan Nusantara'],
            ['code' => 'DST-003', 'name' => 'PT Berkah Distribusi Padang'],
        ];

        foreach ($distributors as $distributor) {
            Distributor::updateOrCreate(
                ['code' => $distributor['code']],
                [
                    'name' => $distributor['name'],
                    'is_active' => true,
                ],
            );
        }

        $markets = [
            ['code' => 'PAS-001', 'name' => 'Pasar Raya Padang'],
            ['code' => 'PAS-002', 'name' => 'Pasar Bandar Buat'],
            ['code' => 'PAS-003', 'name' => 'Pasar Lubuk Buaya'],
        ];

        foreach ($markets as $market) {
            Market::updateOrCreate(
                ['code' => $market['code']],
                [
                    'name' => $market['name'],
                    'is_active' => true,
                ],
            );
        }

        $users = [
            [
                'name' => 'Rina Marlina',
                'username' => 'distributor',
                'email' => 'distributor@mipta.test',
                'role' => Role::Distributor,
            ],
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
        $currentMonthStart = now()->startOfMonth();
        $previousMonthStart = now()->subMonthNoOverflow()->startOfMonth();

        foreach ([$currentMonthStart, $previousMonthStart] as $monthStart) {
            foreach (Market::whereIn('code', ['PAS-001', 'PAS-002'])->get() as $market) {
                MarketMonitoring::updateOrCreate(
                    [
                        'market_id' => $market->id,
                        'monitored_at' => $monthStart->toDateString(),
                    ],
                    ['uptd_user_id' => $uptdUser->id],
                );
            }
        }

        $distributionSamples = [
            [$currentMonthStart, 'DST-001', 'PAS-001', 250, 1],
            [$currentMonthStart, 'DST-002', 'PAS-002', 180, 2],
            [$currentMonthStart, 'DST-003', 'PAS-001', 120, 3],
            [$previousMonthStart, 'DST-001', 'PAS-001', 200, 5],
            [$previousMonthStart, 'DST-002', 'PAS-002', 150, 12],
        ];

        foreach ($distributionSamples as [$monthStart, $distributorCode, $marketCode, $cartons, $day]) {
            $distributor = Distributor::where('code', $distributorCode)->firstOrFail();
            $market = Market::where('code', $marketCode)->firstOrFail();

            Distribution::updateOrCreate(
                [
                    'distributor_id' => $distributor->id,
                    'market_id' => $market->id,
                    'distribution_date' => $monthStart->copy()->addDays($day - 1)->toDateString(),
                ],
                [
                    'uptd_user_id' => $uptdUser->id,
                    'cartons' => $cartons,
                ],
            );
        }
    }
}
