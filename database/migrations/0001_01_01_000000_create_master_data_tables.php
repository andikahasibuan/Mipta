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
        Schema::create('distributors', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('penanggung_jawab')->nullable();
            $table->string('telepon')->nullable();
            $table->string('alamat_kantor')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('markets', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('lokasi')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('recipient_stores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('market_id')->constrained()->restrictOnDelete();
            $table->foreignId('distributor_id')->nullable()->constrained('distributors')->nullOnDelete();
            $table->string('name');
            $table->string('alamat')->nullable();
            $table->string('nib', 20)->nullable()->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recipient_stores');
        Schema::dropIfExists('markets');
        Schema::dropIfExists('distributors');
    }
};
