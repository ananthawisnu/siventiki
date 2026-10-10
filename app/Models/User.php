<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'nim',
        'name',
        'email',
        'password',
        'role',
        'warehouse',
        'is_active',
        'avatar',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isAdminKsr(): bool
    {
        return $this->role === 'admin_ksr';
    }

    public function isAdminPoliklinik(): bool
    {
        return $this->role === 'admin_poliklinik';
    }

    public function canAccessWarehouse(string $warehouse): bool
    {
        if ($this->isSuperAdmin() || $this->warehouse === 'all') {
            return true;
        }

        return $this->warehouse === $warehouse;
    }

    public function getRoleLabelAttribute(): string
    {
        return match ($this->role) {
            'super_admin' => 'Super Admin',
            'admin_ksr' => 'Admin Gudang Obat',
            'admin_poliklinik' => 'Admin Gudang Inventaris',
            default => ucfirst($this->role),
        };
    }

    public function getWarehouseLabelAttribute(): string
    {
        return match ($this->warehouse) {
            'all' => 'Semua Gudang',
            'ksr' => 'Gudang Obat',
            'poliklinik' => 'Gudang Inventaris',
            default => ucfirst($this->warehouse),
        };
    }
}
