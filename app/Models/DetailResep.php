<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetailResep extends Model
{
    use HasFactory;

    protected $table = 'detail_resep';

    protected $fillable = [
        'resep_id',
        'obat_id',
        'jumlah',
        'dosis',
        'aturan_pakai',
        'keterangan',
    ];

    /**
     * Relasi ke model Resep.
     */
    public function resep(): BelongsTo
    {
        return $this->belongsTo(Resep::class, 'resep_id');
    }

    /**
     * Relasi ke model Obat.
     */
    public function obat(): BelongsTo
    {
        return $this->belongsTo(Obat::class, 'obat_id');
    }
}
