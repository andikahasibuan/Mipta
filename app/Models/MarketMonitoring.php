<?php

namespace App\Models;

use Database\Factories\MarketMonitoringFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['market_id', 'uptd_user_id', 'monitored_at'])]
class MarketMonitoring extends Model
{
    /** @use HasFactory<MarketMonitoringFactory> */
    use HasFactory;

    /**
     * Pasar yang dipantau.
     */
    public function market(): BelongsTo
    {
        return $this->belongsTo(Market::class);
    }

    /**
     * Petugas UPTD yang melakukan pemantauan.
     */
    public function uptdUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uptd_user_id');
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'monitored_at' => 'date',
        ];
    }
}
