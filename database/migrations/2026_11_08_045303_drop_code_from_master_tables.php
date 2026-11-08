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
        $this->dropCodeColumn('distributors');
        $this->dropCodeColumn('markets');
        $this->dropCodeColumn('recipient_stores');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $this->addCodeColumn('distributors');
        $this->addCodeColumn('markets');
        $this->addCodeColumn('recipient_stores');
    }

    /**
     * Hapus kolom kode penyimpanan karena ID data dihitung dari id.
     */
    private function dropCodeColumn(string $tableName): void
    {
        if (! Schema::hasColumn($tableName, 'code')) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table): void {
            $table->dropUnique(['code']);
            $table->dropColumn('code');
        });
    }

    /**
     * Kembalikan kolom kode saat rollback.
     */
    private function addCodeColumn(string $tableName): void
    {
        if (Schema::hasColumn($tableName, 'code')) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table): void {
            $table->string('code')->nullable()->unique()->after('id');
        });
    }
};
