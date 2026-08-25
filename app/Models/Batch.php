<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Batch extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_id',
        'warehouse',
        'batch_no',
        'expiry_date',
        'quantity',
        'initial_quantity',
        'received_at',
        'invoice_no',
        'source',
    ];

    protected function casts(): array
    {
        return [
            'expiry_date' => 'date',
            'received_at' => 'date',
            'quantity' => 'integer',
            'initial_quantity' => 'integer',
        ];
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * FEFO ordering: Nearest expiry first (nulls last), then oldest received date
     */
    public function scopeFefoOrder($query)
    {
        return $query->orderByRaw('expiry_date IS NULL, expiry_date ASC, received_at ASC, id ASC');
    }

    public function scopeActive($query)
    {
        return $query->where('quantity', '>', 0);
    }

    public function scopeForWarehouse($query, ?string $warehouse)
    {
        if ($warehouse && $warehouse !== 'all') {
            return $query->where('warehouse', $warehouse);
        }

        return $query;
    }

    public function getDaysUntilExpiryAttribute(): ?int
    {
        if (! $this->expiry_date) {
            return null;
        }

        return (int) Carbon::today()->diffInDays($this->expiry_date, false);
    }

    public function getExpiryStatusAttribute(): string
    {
        if (! $this->expiry_date) {
            return 'no_expiry';
        }

        $days = $this->days_until_expiry;

        if ($days < 0) {
            return 'expired';
        }

        if ($days <= 30) {
            return 'critical_30';
        }

        if ($days <= 60) {
            return 'warning_60';
        }

        if ($days <= 90) {
            return 'warning_90';
        }

        return 'safe';
    }

    public function getExpiryStatusLabelAttribute(): string
    {
        return match ($this->expiry_status) {
            'expired' => 'Sudah Kedaluwarsa',
            'critical_30' => '≤ 30 Hari Lagi',
            'warning_60' => '≤ 60 Hari Lagi',
            'warning_90' => '≤ 90 Hari Lagi',
            'safe' => 'Stok Aman',
            'no_expiry' => 'Tanpa Kadaluwarsa',
        };
    }
}
