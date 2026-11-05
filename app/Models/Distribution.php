<?php

namespace App\Models;

use Database\Factories\DistributionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['distributor_id', 'market_id', 'uptd_user_id', 'distribution_date', 'cartons'])]
class Distribution extends Model
{
    /** @use HasFactory<DistributionFactory> */
    use HasFactory;

    /**
     * Distributor yang melakukan penyaluran.
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
     * Petugas UPTD yang mencatat penyaluran.
     */
    public function uptdUser(): BelongsTo
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
            'distribution_date' => 'date',
            'cartons' => 'integer',
        ];
    }
}
