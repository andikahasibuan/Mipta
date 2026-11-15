<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('distributions')->where('status', 'draft')->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Data draft yang sudah dihapus tidak dapat dikembalikan.
    }
};
