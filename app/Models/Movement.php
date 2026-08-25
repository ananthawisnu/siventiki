<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Movement extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_id',
        'warehouse',
        'kind',
        'quantity',
        'reference_no',
        'source_destination',
        'actor_id',
        'actor_name',
        'notes',
        'allocations',
        'running_balance',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'running_balance' => 'integer',
            'allocations' => 'array',
        ];
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    public function getKindLabelAttribute(): string
    {
        return match ($this->kind) {
            'inbound' => 'Barang Masuk',
            'outbound' => 'Barang Keluar',
            'opname' => 'Stock Opname',
            default => ucfirst($this->kind),
        };
    }

    public function getKindBadgeClassAttribute(): string
    {
        return match ($this->kind) {
            'inbound' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
            'outbound' => 'bg-rose-100 text-rose-800 border-rose-300',
            'opname' => 'bg-amber-100 text-amber-800 border-amber-300',
            default => 'bg-slate-100 text-slate-800 border-slate-300',
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
