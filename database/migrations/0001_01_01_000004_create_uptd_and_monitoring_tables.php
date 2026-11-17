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
            $table->string('password')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('market_monitorings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('market_id')->constrained()->cascadeOnDelete();
            $table->foreignId('uptd_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('monitored_at');
            $table->timestamps();
            $table->index('monitored_at');
        });

        Schema::create('field_distribution_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('market_id')->constrained()->cascadeOnDelete();
            $table->foreignId('uptd_contributor_id')->nullable()->constrained('uptd_contributors')->nullOnDelete();
            $table->date('report_date');
            $table->text('description');
            $table->string('condition');
            $table->timestamps();
            $table->index('report_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('field_distribution_reports');
        Schema::dropIfExists('market_monitorings');
        Schema::dropIfExists('uptd_contributors');
    }
};
