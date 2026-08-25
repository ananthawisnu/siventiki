<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'category',
        'warehouse',
        'unit',
        'min_stock',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'min_stock' => 'integer',
        ];
    }

    public function batches(): HasMany
    {
        return $this->hasMany(Batch::class);
    }

    public function activeBatches(): HasMany
    {
        return $this->hasMany(Batch::class)
            ->where('quantity', '>', 0)
            ->orderByRaw('expiry_date IS NULL, expiry_date ASC, received_at ASC, id ASC');
    }

    public function movements(): HasMany
    {
        return $this->hasMany(Movement::class)->orderBy('created_at', 'desc');
    }

    /**
     * Compute real-time current stock dynamically from batches
     */
    public function getCurrentStockAttribute(): int
    {
        if ($this->relationLoaded('batches')) {
            return (int) $this->batches->sum('quantity');
        }

        return (int) $this->batches()->sum('quantity');
    }

    public function getIsLowStockAttribute(): bool
    {
        if ($this->category === 'bhp' && $this->min_stock > 0) {
            return $this->current_stock <= $this->min_stock;
        }

        return false;
    }

    public function getCategoryLabelAttribute(): string
    {
        return match ($this->category) {
            'obat' => 'Obat Medis',
            'bhp' => 'BHP Medis',
            default => strtoupper($this->category),
        };
    }

    public function getWarehouseLabelAttribute(): string
    {
        return match ($this->warehouse) {
            'ksr' => 'Gudang KSR',
            'poliklinik' => 'Poliklinik',
            default => ucfirst($this->warehouse),
        };
    }

    public function scopeForWarehouse($query, ?string $warehouse)
    {
        if ($warehouse && $warehouse !== 'all') {
            return $query->where('warehouse', $warehouse);
        }

        return $query;
    }
}
