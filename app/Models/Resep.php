<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Resep extends Model
{
    use HasFactory;

    protected $table = 'resep';

    protected $fillable = [
        'kunjungan_id',
        'dokter_id',
        'no_resep',
        'catatan_dokter',
        'prioritas',
        'status',
        'jam_input_resep',
        'jam_selesai_farmasi',
    ];

    protected $casts = [
        'jam_input_resep' => 'datetime',
        'jam_selesai_farmasi' => 'datetime',
    ];

    /**
     * Relasi ke model Kunjungan.
     */
    public function kunjungan(): BelongsTo
    {
        return $this->belongsTo(Kunjungan::class, 'kunjungan_id');
    }

    /**
     * Relasi ke model ProfilDokter.
     */
    public function dokter(): BelongsTo
    {
        return $this->belongsTo(ProfilDokter::class, 'dokter_id');
    }

    /**
     * Relasi ke model DetailResep.
     */
    public function detailResep(): HasMany
    {
        return $this->hasMany(DetailResep::class, 'resep_id');
    }
}
