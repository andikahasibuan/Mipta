<?php

namespace App\Models;

use Database\Factories\UptdContributorFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['market_id', 'name', 'username', 'is_active'])]
class UptdContributor extends Model
{
    /** @use HasFactory<UptdContributorFactory> */
    use HasFactory;

    /**
     * Pasar tempat kontributor bertugas.
     */
    public function market(): BelongsTo
    {
        return $this->belongsTo(Market::class);
    }

    /**
     * Laporan pengawasan yang dibuat kontributor ini.
     */
    public function fieldReports(): HasMany
    {
        return $this->hasMany(FieldDistributionReport::class, 'uptd_contributor_id');
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}
