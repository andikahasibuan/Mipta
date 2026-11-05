<?php

namespace App\Models;

use Database\Factories\MarketFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'lokasi', 'is_active'])]
class Market extends Model
{
    /** @use HasFactory<MarketFactory> */
    use HasFactory;

    /**
     * Toko penerima yang berada di pasar ini.
     */
    public function stores(): HasMany
    {
        return $this->hasMany(RecipientStore::class);
    }

    /**
     * Riwayat pemantauan pasar oleh petugas UPTD.
     */
    public function monitorings(): HasMany
    {
        return $this->hasMany(MarketMonitoring::class);
    }

    /**
     * Riwayat penyaluran Minyakita di pasar ini.
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
