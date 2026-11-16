<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $contributors = DB::table('users')
            ->where('role', 'uptd_pasar')
            ->whereNotNull('market_id')
            ->orderBy('id')
            ->get();

        foreach ($contributors as $contributor) {
            DB::table('uptd_contributors')->insert([
                'id' => $contributor->id,
                'market_id' => $contributor->market_id,
                'name' => $contributor->name,
                'username' => $contributor->username,
                'is_active' => $contributor->is_active,
                'created_at' => $contributor->created_at,
                'updated_at' => $contributor->updated_at,
            ]);
        }

        Schema::table('field_distribution_reports', function (Blueprint $table) {
            $table->foreignId('uptd_contributor_id')
                ->nullable()
                ->after('market_id')
                ->constrained('uptd_contributors')
                ->nullOnDelete();
        });

        DB::table('field_distribution_reports')
            ->whereNotNull('uptd_user_id')
            ->update(['uptd_contributor_id' => DB::raw('uptd_user_id')]);

        Schema::table('field_distribution_reports', function (Blueprint $table) {
            $table->dropForeign(['uptd_user_id']);
            $table->dropColumn('uptd_user_id');
        });

        DB::table('users')
            ->where('role', 'uptd_pasar')
            ->whereNotNull('market_id')
            ->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Data akun kontributor yang sudah dipindahkan tidak dapat dikembalikan.
    }
};
