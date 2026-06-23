<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailDraftResep extends Model
{
    use HasFactory;

    protected $fillable = [
        'draft_resep_id',
        'obat_id',
        'jumlah',
        'dosis',
        'aturan_pakai',
        'keterangan',
    ];

    public function draftResep()
    {
        return $this->belongsTo(DraftResep::class, 'draft_resep_id');
    }

    public function obat()
    {
        return $this->belongsTo(Obat::class);
    }
}
