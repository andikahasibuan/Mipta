<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('uptd_contributors', function (Blueprint $table) {
            $table->string('password')->nullable()->after('username');
        });

        DB::table('uptd_contributors')
            ->orderBy('id')
            ->get(['id'])
            ->each(function (object $contributor): void {
                DB::table('uptd_contributors')
                    ->where('id', $contributor->id)
                    ->update(['password' => Hash::make('uptdpasar123')]);
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('uptd_contributors', function (Blueprint $table) {
            $table->dropColumn('password');
        });
    }
};
