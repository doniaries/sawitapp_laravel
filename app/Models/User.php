<?php

namespace App\Models;

use Filament\Panel;
use App\Models\Perusahaan;
use Illuminate\Notifications\Notifiable;
use Filament\Models\Contracts\FilamentUser;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Filament\Models\Contracts\HasTenants;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Traits\HasRoles;


class User extends Authenticatable implements FilamentUser, HasTenants
{
    use HasFactory, Notifiable, SoftDeletes, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
        'perusahaan_id',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
    ];

    /**
     * Akses ke panel Filament.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->is_active && $this->email;
    }

    /**
     * Relasi BelongsTo (untuk backward compatibility & kolom perusahaan_id default).
     */
    public function perusahaan(): BelongsTo
    {
        return $this->belongsTo(Perusahaan::class, 'perusahaan_id');
    }

    /**
     * Relasi BelongsToMany (digunakan Filament HasTenants).
     */
    public function perusahaans(): BelongsToMany
    {
        return $this->belongsToMany(Perusahaan::class, 'perusahaan_user');
    }

    /**
     * Cek super_admin langsung dari DB (tanpa team context).
     */
    protected function isSuperAdmin(): bool
    {
        return DB::table('model_has_roles')
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->where('model_has_roles.model_id', $this->id)
            ->where('model_has_roles.model_type', static::class)
            ->where('roles.name', 'super_admin')
            ->exists();
    }

    /**
     * Filament: daftar tenant yang bisa diakses.
     * Superadmin → semua perusahaan, user biasa → dari pivot.
     */
    public function getTenants(Panel $panel): Collection
    {
        if ($this->isSuperAdmin()) {
            return Perusahaan::withoutGlobalScopes()->get();
        }

        return $this->perusahaans;
    }

    /**
     * Filament: apakah user boleh akses tenant tertentu.
     */
    public function canAccessTenant(Model $tenant): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        return $this->perusahaans()->whereKey($tenant)->exists();
    }
}
