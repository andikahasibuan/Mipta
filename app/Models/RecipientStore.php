<?php

namespace App\Models;

use Database\Factories\RecipientStoreFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[Fillable(['distributor_id', 'market_id', 'name', 'alamat', 'nib', 'is_active'])]
class RecipientStore extends Model
{
    /** @use HasFactory<RecipientStoreFactory> */
    use HasFactory;

    /**
     * Distributor pemilik data toko.
     */
    public function distributor(): BelongsTo
    {
        return $this->belongsTo(Distributor::class);
    }

    /**
     * Pasar tempat toko penerima berada.
     */
    public function market(): BelongsTo
    {
        return $this->belongsTo(Market::class);
    }

    /**
     * Laporan penyaluran yang ditujukan ke toko ini.
     */
    public function distributions(): HasMany
    {
        return $this->hasMany(Distribution::class);
    }

    /**
     * Simpan detail alamat dalam Title Case.
     */
    protected function alamat(): Attribute
    {
        return Attribute::set(fn (?string $value): ?string => filled($value)
            ? Str::title($value)
            : null);
    }

    /**
     * Alamat lengkap: nama pasar ditambah detail alamat opsional.
     */
    protected function alamatLengkap(): Attribute
    {
        return Attribute::get(fn (): string => collect([
            filled($this->market?->name) ? Str::title($this->market->name) : null,
            filled($this->alamat) ? Str::title($this->alamat) : null,
        ])
            ->filter(fn (?string $value): bool => filled($value))
            ->implode(', '));
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
