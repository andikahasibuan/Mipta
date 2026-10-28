<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    /**
     * Seed akun admin awal.
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
    }
}
