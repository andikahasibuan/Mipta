<?php

use App\Enums\ReportStatus;
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
        Schema::create('distribution_reports', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->foreignId('distributor_id')->constrained()->restrictOnDelete();
            $table->foreignId('market_id')->constrained()->restrictOnDelete();
            $table->date('report_date');
            $table->unsignedInteger('cartons');
            $table->string('status')->default(ReportStatus::Menunggu->value);
            $table->timestamps();

            $table->index(['report_date', 'id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('distribution_reports');
    }
};
