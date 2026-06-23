<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DraftResep extends Model
{
    use HasFactory;

    protected $fillable = [
        'kunjungan_id',
        'farmasi_id',
        'catatan_farmasi',
        'status',
    ];

    public function kunjungan()
    {
        return $this->belongsTo(Kunjungan::class);
    }

    public function farmasi()
    {
        return $this->belongsTo(ProfilFarmasi::class, 'farmasi_id');
    }

    public function detailDraftResep()
    {
        return $this->hasMany(DetailDraftResep::class, 'draft_resep_id');
    }
}
