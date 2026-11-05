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
        Schema::create('distributions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('distributor_id')->constrained()->restrictOnDelete();
            $table->foreignId('market_id')->constrained()->restrictOnDelete();
            $table->foreignId('uptd_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('distribution_date');
            $table->unsignedInteger('cartons');
            $table->timestamps();
            $table->index('distribution_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('distributions');
    }
};
