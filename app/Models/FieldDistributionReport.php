<?php

namespace App\Models;

use App\Enums\FieldCondition;
use Database\Factories\FieldDistributionReportFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['market_id', 'uptd_user_id', 'report_date', 'description', 'condition'])]
class FieldDistributionReport extends Model
{
    /** @use HasFactory<FieldDistributionReportFactory> */
    use HasFactory;

    /**
     * Pasar yang kondisinya dilaporkan.
     */
    public function market(): BelongsTo
    {
        return $this->belongsTo(Market::class);
    }

    /**
     * Kontributor UPTD Pasar yang melaporkan kondisi lapangan.
     */
    public function contributor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uptd_user_id');
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
            'condition' => FieldCondition::class,
        ];
    }
}
