<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'status',
        'email_verified_at',
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
     * Relasi ke profil pasien.
     */
    public function profilPasien(): HasOne
    {
        return $this->hasOne(ProfilPasien::class, 'user_id');
    }

    /**
     * Relasi ke profil dokter.
     */
    public function profilDokter(): HasOne
    {
        return $this->hasOne(ProfilDokter::class, 'user_id');
    }

    /**
     * Relasi ke profil farmasi.
     */
    public function profilFarmasi(): HasOne
    {
        return $this->hasOne(ProfilFarmasi::class, 'user_id');
    }

    /**
     * Checks for user roles.
     */
    public function isPasien(): bool
    {
        return $this->role === 'pasien';
    }

    public function isDokter(): bool
    {
        return $this->role === 'dokter';
    }

    public function isFarmasi(): bool
    {
        return $this->role === 'farmasi';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
}
