<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('users')
            ->where('role', 'uptd_pasar')
            ->whereNotNull('market_id')
            ->orderBy('id')
            ->get(['id'])
            ->each(function (object $user): void {
                DB::table('users')
                    ->where('id', $user->id)
                    ->update(['password' => Hash::make(Str::password(32))]);
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Password lama tidak dapat dikembalikan.
    }
};
