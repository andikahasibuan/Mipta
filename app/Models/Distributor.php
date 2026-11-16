<?php

namespace App\Models;

use Database\Factories\DistributorFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

#[Fillable(['name', 'penanggung_jawab', 'telepon', 'alamat_kantor', 'is_active'])]
class Distributor extends Model
{
    /** @use HasFactory<DistributorFactory> */
    use HasFactory;

    /**
     * Riwayat penyaluran distributor ini.
     */
    public function distributions(): HasMany
    {
        return $this->hasMany(Distribution::class);
    }

    /**
     * Akun login perusahaan yang mewakili distributor ini.
     */
    public function account(): HasOne
    {
        return $this->hasOne(User::class);
    }

    /**
     * Toko penerima milik distributor ini.
     */
    public function stores(): HasMany
    {
        return $this->hasMany(RecipientStore::class);
    }

    /**
     * Simpan nama penanggung jawab dalam Title Case.
     */
    protected function penanggungJawab(): Attribute
    {
        return Attribute::set(fn (?string $value): ?string => filled($value)
            ? Str::title($value)
            : null);
    }

    /**
     * Simpan alamat kantor dalam Title Case.
     */
    protected function alamatKantor(): Attribute
    {
        return Attribute::set(fn (?string $value): ?string => filled($value)
            ? Str::title($value)
            : null);
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
