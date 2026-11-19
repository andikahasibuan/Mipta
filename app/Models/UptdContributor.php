<?php

namespace App\Models;

use Database\Factories\UptdContributorFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[Fillable(['market_id', 'name', 'username', 'password', 'is_active'])]
#[Hidden(['password'])]
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
     * Inisial nama kontributor (maksimal dua kata pertama).
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->filter()
            ->take(2)
            ->map(fn (string $word) => mb_strtoupper(mb_substr($word, 0, 1)))
            ->implode('');
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }
}
