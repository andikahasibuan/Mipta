<?php

namespace App\Models;

use Database\Factories\RecipientStoreFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['market_id', 'code', 'name', 'is_active'])]
class RecipientStore extends Model
{
    /** @use HasFactory<RecipientStoreFactory> */
    use HasFactory;

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
