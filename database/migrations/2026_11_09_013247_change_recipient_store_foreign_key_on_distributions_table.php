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
        Schema::table('distributions', function (Blueprint $table) {
            $table->dropForeign(['recipient_store_id']);
        });

        Schema::table('distributions', function (Blueprint $table) {
            $table->foreign('recipient_store_id')
                ->references('id')
                ->on('recipient_stores')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('distributions', function (Blueprint $table) {
            $table->dropForeign(['recipient_store_id']);
        });

        Schema::table('distributions', function (Blueprint $table) {
            $table->foreign('recipient_store_id')
                ->references('id')
                ->on('recipient_stores')
                ->nullOnDelete();
        });
    }
};
