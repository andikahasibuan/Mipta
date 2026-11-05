<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Seed akun pengguna awal untuk pengembangan lokal.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@mipta.test'],
            [
                'name' => 'Administrator',
                'username' => 'admin',
                'password' => 'admin123',
                'role' => Role::Admin,
            ],
        );

        User::updateOrCreate(
            ['email' => 'kepala.dinas@mipta.test'],
            [
                'name' => 'Kepala Dinas',
                'username' => 'kepala.dinas',
                'password' => 'kepaladinas123',
                'role' => Role::KepalaDinas,
            ],
        );
    }
}
