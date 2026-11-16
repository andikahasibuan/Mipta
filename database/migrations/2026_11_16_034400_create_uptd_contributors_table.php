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
        Schema::create('uptd_contributors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('market_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('username')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('uptd_contributors');
    }
};
