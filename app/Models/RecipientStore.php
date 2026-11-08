<?php

namespace App\Models;

use Database\Factories\RecipientStoreFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['market_id', 'name', 'nib', 'is_active'])]
class RecipientStore extends Model
{
    /** @use HasFactory<RecipientStoreFactory> */
    use HasFactory;

    /**
     * ID data toko penerima yang ditampilkan, mis. TKS-001.
     */
    protected function code(): Attribute
    {
        return Attribute::get(fn (): string => sprintf('TKS-%03d', $this->id));
    }

    /**
     * Pasar tempat toko penerima berada.
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
            'is_active' => 'boolean',
        ];
    }
}
