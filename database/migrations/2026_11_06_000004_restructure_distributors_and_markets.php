<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('distributors', function (Blueprint $table) {
            $table->dropUnique('distributors_code_unique');
            $table->dropColumn('code');
            $table->string('wilayah')->nullable();
        });

        Schema::table('markets', function (Blueprint $table) {
            $table->dropUnique('markets_code_unique');
            $table->dropColumn('code');
            $table->string('lokasi')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('distributors', function (Blueprint $table) {
            $table->string('code')->unique();
            $table->dropColumn('wilayah');
        });

        Schema::table('markets', function (Blueprint $table) {
            $table->string('code')->unique();
            $table->dropColumn('lokasi');
        });
    }
};
