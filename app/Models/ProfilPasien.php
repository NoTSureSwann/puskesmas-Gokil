<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Auditable;

class ProfilPasien extends Model
{
    use HasFactory, SoftDeletes, Auditable;

    protected $table = 'profil_pasien';

    protected $fillable = [
        'user_id',
        'nik',
        'no_bpjs',
        'no_kk',
        'jenis_kelamin',
        'tanggal_lahir',
        'tempat_lahir',
        'alamat',
        'kelurahan',
        'kecamatan',
        'jenis_pasien',
        'riwayat_alergi',
        'golongan_darah',
        'tinggi_badan',
        'berat_badan',
    ];

    protected $casts = [
        'nik' => 'encrypted',
        'alamat' => 'encrypted',
        'riwayat_alergi' => 'encrypted',
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
        return $this->hasMany(Kunjungan::class, 'pasien_id');
    }
}
