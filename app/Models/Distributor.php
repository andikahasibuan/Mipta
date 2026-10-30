<?php

namespace App\Models;

use Database\Factories\DistributorFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['code', 'name', 'is_active'])]
class Distributor extends Model
{
    /** @use HasFactory<DistributorFactory> */
    use HasFactory;

    /**
     * Laporan penyaluran dari distributor ini.
     */
    public function reports(): HasMany
    {
        return $this->hasMany(DistributionReport::class);
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
