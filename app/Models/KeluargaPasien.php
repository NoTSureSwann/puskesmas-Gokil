<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\Auditable;

class KeluargaPasien extends Model
{
    use HasFactory, Auditable;

    protected $fillable = [
        'pasien_id',
        'nama_lengkap',
        'hubungan',
        'nik',
        'tanggal_lahir',
        'jenis_kelamin',
    ];

    protected $casts = [
        'nik' => 'encrypted',
        'tanggal_lahir' => 'date',
    ];

    public function pasien(): BelongsTo
    {
        return $this->belongsTo(ProfilPasien::class, 'pasien_id');
    }
}
