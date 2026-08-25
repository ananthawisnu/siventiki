<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'asset_id',
        'action',
        'actor_id',
        'actor_name',
        'borrower_name',
        'return_estimate',
        'purpose',
        'damage_notes',
    ];

    protected function casts(): array
    {
        return [
            'return_estimate' => 'date',
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

    public function getActionLabelAttribute(): string
    {
        return match ($this->action) {
            'create' => 'Registrasi Aset',
            'borrow' => 'Peminjaman',
            'return' => 'Pengembalian',
            'damage' => 'Laporan Kerusakan',
            'repair' => 'Perbaikan Selesai',
            'cross_loan' => 'Pinjam Lintas Divisi',
            default => ucfirst($this->action),
        };
    }

    public function getActionBadgeClassAttribute(): string
    {
        return match ($this->action) {
            'create' => 'bg-blue-100 text-blue-800 border-blue-300',
            'borrow' => 'bg-amber-100 text-amber-800 border-amber-300',
            'return' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
            'damage' => 'bg-rose-100 text-rose-800 border-rose-300',
            'repair' => 'bg-teal-100 text-teal-800 border-teal-300',
            'cross_loan' => 'bg-purple-100 text-purple-800 border-purple-300',
            default => 'bg-slate-100 text-slate-800 border-slate-300',
        };
    }
}
