<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WabahGeospasial extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_penyakit',
        'latitude',
        'longitude',
        'kota',
        'radius_km',
        'tingkat_bahaya',
        'kasus_aktif',
        'rekomendasi_ai',
    ];
}
