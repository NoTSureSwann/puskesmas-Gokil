<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @mixin \Illuminate\Database\Eloquent\Builder
 * @mixin \Illuminate\Database\Eloquent\Model
 */
class Obat extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'obat';

    protected $fillable = [
        'kode_obat',
        'nama_obat',
        'satuan',
        'kategori',
        'stok',
        'stok_minimum',
        'harga_satuan',
        'tanggal_kadaluarsa',
        'deskripsi',
        'is_aktif',
    ];

    protected $casts = [
        'is_aktif' => 'boolean',
        'harga_satuan' => 'decimal:2',
        'stok' => 'integer',
        'stok_minimum' => 'integer',
        'tanggal_kadaluarsa' => 'date',
    ];

    /**
     * Scope untuk obat yang aktif.
     */
    public function scopeAktif(Builder $query): Builder
    {
        return $query->where('is_aktif', true);
    }

    /**
     * Scope untuk obat yang stoknya rendah.
     */
    public function scopeStokRendah(Builder $query): Builder
    {
        return $query->whereColumn('stok', '<=', 'stok_minimum');
    }

    /**
     * Relasi ke model DetailResep.
     */
    public function detailResep(): HasMany
    {
        return $this->hasMany(DetailResep::class, 'obat_id');
    }
}
