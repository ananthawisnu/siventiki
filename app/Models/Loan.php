<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Loan extends Model
{
    use HasFactory;

    protected $fillable = [
        'asset_id',
        'source_warehouse',
        'target_division',
        'borrower_name',
        'return_estimate',
        'purpose',
        'status',
        'actor_id',
        'actor_name',
        'returned_at',
    ];

    protected function casts(): array
    {
        return [
            'return_estimate' => 'date',
            'returned_at' => 'datetime',
        ];
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'dipinjam' => 'Sedang Dipinjam',
            'selesai' => 'Selesai Dikembalikan',
            default => ucfirst($this->status),
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'dipinjam' => 'bg-amber-100 text-amber-800 border-amber-300',
            'selesai' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
            default => 'bg-slate-100 text-slate-800 border-slate-300',
        };
    }

    public function scopeForWarehouse($query, ?string $warehouse)
    {
        if ($warehouse && $warehouse !== 'all') {
            return $query->where('source_warehouse', $warehouse);
        }

        return $query;
    }
}
