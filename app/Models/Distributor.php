<?php

namespace App\Models;

use Database\Factories\DistributorFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'wilayah', 'is_active'])]
class Distributor extends Model
{
    /** @use HasFactory<DistributorFactory> */
    use HasFactory;

    /**
     * ID data distributor yang ditampilkan, mis. DST-001.
     */
    protected function code(): Attribute
    {
        return Attribute::get(fn (): string => sprintf('DST-%03d', $this->id));
    }

    /**
     * Riwayat penyaluran distributor ini.
     */
    public function distributions(): HasMany
    {
        return $this->hasMany(Distribution::class);
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
