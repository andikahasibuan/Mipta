<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\Market;
use App\Models\UptdContributor;
use App\Models\User;
use Illuminate\Database\Seeder;

class UptdContributorSeeder extends Seeder
{
    /**
     * Seed akun bersama dan sembilan profil kontributor UPTD per pasar.
     */
    public function run(): void
    {
        $markets = [
            ['name' => 'Pasar Raya Padang', 'lokasi' => 'Jln. Raya'],
            ['name' => 'Pasar Alai', 'lokasi' => 'Jln. Alai Baru'],
            ['name' => 'Pasar Lubuk Buaya', 'lokasi' => 'Jln. Buaya'],
            ['name' => 'Pasar Nanggalo', 'lokasi' => 'Jln. Perjuangan'],
            ['name' => 'Pasar Bandar Buat', 'lokasi' => 'Jln. Baru'],
            ['name' => 'Pasar Ulak Karang', 'lokasi' => 'Jln. Teuku Umar'],
            ['name' => 'Pasar Belimbing', 'lokasi' => 'Jln. Samudra'],
            ['name' => 'Pasar Tanah Kongsi', 'lokasi' => 'Jln. Banteng Perjuangan'],
            ['name' => 'Pasar Siteba', 'lokasi' => 'Jln. Ujung Padang'],
        ];

        $marketIds = [];

        foreach ($markets as $market) {
            $marketIds[$market['name']] = Market::firstOrCreate(
                ['name' => $market['name']],
                ['lokasi' => $market['lokasi'], 'is_active' => true],
            )->id;
        }

        $this->upsertSharedAccount();

        foreach ($markets as $index => $market) {
            $number = str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT);

            UptdContributor::updateOrCreate(
                ['market_id' => $marketIds[$market['name']]],
                [
                    'name' => 'Kontributor UPTD '.$number,
                    'username' => 'kontributor.uptd.'.$number,
                    'is_active' => true,
                ],
            );
        }
    }

    /**
     * Buat atau perbarui akun bersama UPTD tanpa menimpa password yang sudah diubah.
     */
    private function upsertSharedAccount(): void
    {
        $user = User::firstOrNew(['username' => 'uptd']);

        $user->fill([
            'name' => 'Kontributor UPTD Pasar',
            'email' => 'uptd@mipta.test',
            'role' => Role::UptdPasar->value,
            'market_id' => null,
            'is_active' => true,
        ]);

        if (! $user->exists) {
            $user->password = 'UptdPasar#2026';
        }

        $user->save();
    }
}
