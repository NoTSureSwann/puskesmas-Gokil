<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Kunjungan extends Model
{
    use HasFactory;

    protected $table = 'kunjungan';

    protected $fillable = [
        'pasien_id',
        'poli_id',
        'dokter_id',
        'loket_user_id',
        'no_kunjungan',
        'no_antrian',
        'tanggal_kunjungan',
        'keluhan',
        'status',
        'jenis_kunjungan',
        'jam_daftar',
        'jam_panggil',
        'jam_selesai',
    ];

    protected $casts = [
        'tanggal_kunjungan' => 'date',
        'jam_daftar' => 'datetime',
        'jam_panggil' => 'datetime',
        'jam_selesai' => 'datetime',
        'no_antrian' => 'integer',
    ];

    /**
     * Relasi ke profil pasien.
     */
    public function pasien(): BelongsTo
    {
        return $this->belongsTo(ProfilPasien::class, 'pasien_id');
    }

    /**
     * Relasi ke poli.
     */
    public function poli(): BelongsTo
    {
        return $this->belongsTo(Poli::class, 'poli_id');
    }

    /**
     * Relasi ke profil dokter.
     */
    public function dokter(): BelongsTo
    {
        return $this->belongsTo(ProfilDokter::class, 'dokter_id');
    }

    /**
     * Relasi ke loket user (admin/staff).
     */
    public function loketUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'loket_user_id');
    }

    /**
     * Relasi ke resep.
     */
    public function resep(): HasOne
    {
        return $this->hasOne(Resep::class, 'kunjungan_id');
    }

    public function pembayaran(): HasOne
    {
        return $this->hasOne(Pembayaran::class, 'kunjungan_id');
    }
}
