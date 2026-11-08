<?php

namespace App\Models;

use Database\Factories\DistributionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['distributor_id', 'market_id', 'uptd_user_id', 'recipient_store_id', 'distribution_date', 'cartons'])]
class Distribution extends Model
{
    /** @use HasFactory<DistributionFactory> */
    use HasFactory;

    /**
     * ID laporan penyaluran yang ditampilkan, mis. LP-001.
     */
    protected function code(): Attribute
    {
        return Attribute::get(fn (): string => sprintf('LP-%03d', $this->id));
    }

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
     * Toko penerima yang menerima penyaluran.
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(RecipientStore::class, 'recipient_store_id');
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
