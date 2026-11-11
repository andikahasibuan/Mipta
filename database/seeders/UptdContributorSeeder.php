<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\Market;
use App\Models\User;
use Illuminate\Database\Seeder;

class UptdContributorSeeder extends Seeder
{
    /**
     * Seed akun bersama dan sembilan kontributor UPTD per pasar.
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

        $this->upsertAccount(
            name: 'Kontributor UPTD Pasar',
            username: 'uptd',
            email: 'uptd@mipta.test',
            password: 'UptdPasar#2026',
            marketId: null,
        );

        foreach ($markets as $index => $market) {
            $number = str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT);

            $this->upsertAccount(
                name: 'Kontributor UPTD '.$number,
                username: 'kontributor.uptd.'.$number,
                email: null,
                password: 'KontributorUptd#'.$number,
                marketId: $marketIds[$market['name']],
            );
        }
    }

    /**
     * Buat atau perbarui akun UPTD tanpa menimpa password yang sudah diubah.
     */
    private function upsertAccount(
        string $name,
        string $username,
        ?string $email,
        string $password,
        ?int $marketId,
    ): void {
        $user = User::firstOrNew(['username' => $username]);

        $user->fill([
            'name' => $name,
            'email' => $email,
            'role' => Role::UptdPasar->value,
            'market_id' => $marketId,
            'is_active' => true,
        ]);

        if (! $user->exists) {
            $user->password = $password;
        }

        $user->save();
    }
}
