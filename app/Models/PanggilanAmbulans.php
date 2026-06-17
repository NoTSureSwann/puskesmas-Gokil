<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PanggilanAmbulans extends Model
{
    use HasFactory;

    protected $table = 'panggilan_ambulans';

    protected $fillable = [
        'pasien_id',
        'alamat_jemput',
        'no_telepon',
        'keluhan_darurat',
        'status',
    ];

    public function pasien()
    {
        return $this->belongsTo(ProfilPasien::class, 'pasien_id');
    }
}
