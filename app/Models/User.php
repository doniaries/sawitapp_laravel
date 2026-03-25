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

use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements FilamentUser, HasTenants
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
        'perusahaan_id',
        'is_active',
        'photo',
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
     * Cek apakah user adalah admin atau super_admin (akses global).
     */
    public function isAdminOrSuperAdmin(): bool
    {
        if ($this->email === 'superadmin@gmail.com' || $this->email === 'yondra@gmail.com') {
            return true;
        }

        if (!\Illuminate\Support\Facades\Schema::hasTable('model_has_roles')) {
            return false;
        }

        return DB::table('model_has_roles')
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->where('model_has_roles.model_id', $this->id)
            ->where('model_has_roles.model_type', static::class)
            ->whereIn('roles.name', ['super_admin', 'admin', 'pimpinan'])
            ->exists();
    }

    /**
     * Cek super_admin saja.
     */
    public function isSuperAdmin(): bool
    {
        if ($this->email === 'superadmin@gmail.com') {
            return true;
        }

        if (!\Illuminate\Support\Facades\Schema::hasTable('model_has_roles')) {
            return false;
        }

        return DB::table('model_has_roles')
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->where('model_has_roles.model_id', $this->id)
            ->where('model_has_roles.model_type', static::class)
            ->where('roles.name', 'super_admin')
            ->exists();
    }

    /**
     * Filament: daftar tenant yang bisa diakses.
     */
    public function getTenants(Panel $panel): Collection
    {
        if ($this->isAdminOrSuperAdmin()) {
            return Perusahaan::withoutGlobalScopes()->get();
        }

        return $this->perusahaans;
    }

    /**
     * Filament: apakah user boleh akses tenant tertentu.
     */
    public function canAccessTenant(Model $tenant): bool
    {
        if ($this->isAdminOrSuperAdmin()) {
            return true;
        }

        return $this->perusahaans()->whereKey($tenant)->exists();
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

/*
        static::saving(function (User $user) {
            // Jika admin/superadmin, kosongkan perusahaan_id
            if ($user->isAdminOrSuperAdmin()) {
                $user->perusahaan_id = null;
            }
        });
*/

        static::saved(function (User $user) {
            // Sinkronkan perusahaan_id dengan item pertama di pivot jika bukan admin/superadmin
            if (!$user->isAdminOrSuperAdmin() && $user->perusahaans()->exists()) {
                $firstPerusahaanId = $user->perusahaans()->first()->id;
                
                // Gunakan query langsung untuk menghindari infinite loop saved event jika perusahaan_id berubah
                if ($user->perusahaan_id !== $firstPerusahaanId) {
                    DB::table('users')
                        ->where('id', $user->id)
                        ->update(['perusahaan_id' => $firstPerusahaanId]);
                }
            }
        });
    }
}
