<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProfilDokter extends Model
{
    use HasFactory;

    protected $table = 'profil_dokter';

    protected $fillable = [
        'user_id',
        'nip',
        'sip',
        'spesialisasi',
        'poli',
        'harga_konsultasi',
        'jam_kerja',
    ];

    /**
     * Relasi ke model User.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relasi ke model Kunjungan.
     */
    public function kunjungan(): HasMany
    {
        return $this->hasMany(Kunjungan::class, 'dokter_id');
    }

    /**
     * Relasi ke model Resep.
     */
    public function resep(): HasMany
    {
        return $this->hasMany(Resep::class, 'dokter_id');
    }
}
