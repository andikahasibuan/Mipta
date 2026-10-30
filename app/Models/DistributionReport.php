<?php

namespace App\Models;

use App\Enums\ReportStatus;
use Database\Factories\DistributionReportFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['code', 'distributor_id', 'market_id', 'report_date', 'cartons', 'status'])]
class DistributionReport extends Model
{
    /** @use HasFactory<DistributionReportFactory> */
    use HasFactory;

    /**
     * Distributor yang menyampaikan laporan.
     */
    public function distributor(): BelongsTo
    {
        return $this->belongsTo(Distributor::class);
    }

    /**
     * Pasar tujuan penyaluran.
     */
    public function market(): BelongsTo
    {
        return $this->belongsTo(Market::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'report_date' => 'date',
            'cartons' => 'integer',
            'status' => ReportStatus::class,
        ];
    }
}
