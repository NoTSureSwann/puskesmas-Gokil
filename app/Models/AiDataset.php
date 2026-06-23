<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiDataset extends Model
{
    use HasFactory;

    protected $table = 'ai_datasets';

    protected $fillable = [
        'kunjungan_id',
        'keluhan',
        'kemungkinan_penyakit',
        'tingkat_urgensi',
        'rekomendasi_poli_nama',
        'saran_tindakan',
        'is_printed',
        'dicetak_pada',
        'model_version',
        'nlp_confidence_score',
        'is_synthetic',
        'needs_annotation',
    ];

    protected $casts = [
        'kemungkinan_penyakit' => 'array',
        'is_printed' => 'boolean',
        'dicetak_pada' => 'datetime',
    ];

    /**
     * Relasi ke model Kunjungan.
     */
    public function kunjungan(): BelongsTo
    {
        return $this->belongsTo(Kunjungan::class, 'kunjungan_id');
    }

    /**
     * Relasi ke model AiFeedback.
     */
    public function feedbacks(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(AiFeedback::class, 'ai_dataset_id');
    }
}
