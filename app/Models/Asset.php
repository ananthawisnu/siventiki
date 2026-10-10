<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Asset extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'warehouse',
        'acquisition_year',
        'funding_source',
        'location',
        'description',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'acquisition_year' => 'integer',
        ];
    }

    public function logs(): HasMany
    {
        return $this->hasMany(AssetLog::class)->orderBy('created_at', 'desc');
    }

    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class)->orderBy('created_at', 'desc');
    }

    public function activeLoan()
    {
        return $this->hasOne(Loan::class)->where('status', 'dipinjam')->latestOfMany();
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'tersedia' => 'Tersedia',
            'digunakan' => 'Sedang Digunakan',
            'rusak' => 'Rusak',
            default => ucfirst($this->status),
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'tersedia' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
            'digunakan' => 'bg-amber-100 text-amber-800 border-amber-300',
            'rusak' => 'bg-rose-100 text-rose-800 border-rose-300',
            default => 'bg-slate-100 text-slate-800 border-slate-300',
        };
    }

    public function getWarehouseLabelAttribute(): string
    {
        return match ($this->warehouse) {
            'ksr' => 'Gudang Obat',
            'poliklinik' => 'Gudang Inventaris',
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

    /**
     * Auto generate unique asset code: ASET-{KSR|POL}-{SLUG_NAMA}-{seq:03d}
     */
    public static function generateCode(string $name, string $warehouse): string
    {
        $prefix = $warehouse === 'poliklinik' ? 'POL' : 'KSR';
        $slug = strtoupper(Str::slug(substr($name, 0, 10), ''));
        if (empty($slug)) {
            $slug = 'ITEM';
        }

        $baseCode = "ASET-{$prefix}-{$slug}";
        $count = self::where('code', 'LIKE', "{$baseCode}-%")->count() + 1;
        $seq = str_pad((string) $count, 3, '0', STR_PAD_LEFT);

        return "{$baseCode}-{$seq}";
    }
}
