<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transfer extends Model
{
    use HasFactory;

    protected $fillable = [
        'transfer_no',
        'source_warehouse',
        'target_warehouse',
        'item_id',
        'quantity',
        'status',
        'allocations',
        'requester_id',
        'requester_name',
        'receiver_id',
        'receiver_name',
        'notes',
        'sent_at',
        'received_at',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'allocations' => 'array',
            'sent_at' => 'datetime',
            'received_at' => 'datetime',
        ];
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'in_transit' => 'Dalam Pengiriman (In Transit)',
            'received' => 'Diterima',
            'cancelled' => 'Dibatalkan',
            default => ucfirst($this->status),
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'in_transit' => 'bg-amber-100 text-amber-800 border-amber-300',
            'received' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
            'cancelled' => 'bg-rose-100 text-rose-800 border-rose-300',
            default => 'bg-slate-100 text-slate-800 border-slate-300',
        };
    }

    public static function generateTransferNo(): string
    {
        $dateStr = now()->format('Ymd');
        $count = self::whereDate('created_at', now()->toDateString())->count() + 1;
        $seq = str_pad((string) $count, 3, '0', STR_PAD_LEFT);

        return "TRF-{$dateStr}-{$seq}";
    }
}
