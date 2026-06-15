<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pembayaran extends Model
{
    use HasFactory;

    protected $table = 'pembayaran';

    protected $fillable = [
        'kunjungan_id',
        'kode_pembayaran',
        'biaya_konsultasi',
        'biaya_obat',
        // 'total_bayar' is stored as computed in DB, but sometimes we need to fill it if not computed,
        // Wait, the migration says `storedAs('biaya_konsultasi + biaya_obat')`. So we don't insert it.
        'metode_pembayaran',
        'provider_pembayaran',
        'status_pembayaran',
        'waktu_pembayaran',
    ];

    protected $casts = [
        'biaya_konsultasi' => 'decimal:2',
        'biaya_obat' => 'decimal:2',
        'total_bayar' => 'decimal:2',
        'waktu_pembayaran' => 'datetime',
    ];

    /**
     * Get the kunjungan that owns the pembayaran.
     */
    public function kunjungan(): BelongsTo
    {
        return $this->belongsTo(Kunjungan::class);
    }
}
