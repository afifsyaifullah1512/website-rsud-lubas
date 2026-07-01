<?php

declare(strict_types=1);

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

/**
 * Pengguna terautentikasi (Admin_User) yang mengakses Admin_Panel.
 *
 * Memuat trait {@see HasRoles} dari Spatie Permission untuk
 * mendukung RBAC (Requirements 15.1–15.4) dan relasi `news()`
 * sebagai author dari berita yang ditulis (Requirements 5.2, 19.1).
 *
 * Mengimplementasikan {@see FilamentUser} agar hanya pengguna dengan
 * salah satu role panel admin yang dapat masuk ke `/admin`
 * (Requirement 14.1, 15.1, 15.2).
 */
class User extends Authenticatable implements FilamentUser
{
    use HasFactory;
    use HasRoles;
    use Notifiable;

    /**
     * Daftar role yang diizinkan untuk login ke panel `admin`.
     *
     * Selaras dengan Requirement 15.1 (super-admin, admin, editor, humas,
     * petugas-pengaduan, viewer).
     *
     * @var array<int, string>
     */
    public const ADMIN_PANEL_ROLES = [
        'super-admin',
        'admin',
        'editor',
        'humas',
        'petugas-pengaduan',
        'viewer',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
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
        ];
    }

    /**
     * Berita yang ditulis oleh user ini (FK `news.author_id`).
     */
    public function news(): HasMany
    {
        return $this->hasMany(News::class, 'author_id');
    }

    /**
     * Tentukan apakah pengguna dapat mengakses panel Filament tertentu.
     *
     * Hanya pengguna yang memiliki minimal salah satu role pada
     * {@see self::ADMIN_PANEL_ROLES} yang diizinkan masuk ke panel
     * `admin` (Requirements 14.1, 15.2, 15.3).
     */
    public function canAccessPanel(Panel $panel): bool
    {
        if ($panel->getId() !== 'admin') {
            return true;
        }

        return $this->hasAnyRole(self::ADMIN_PANEL_ROLES);
    }
}
