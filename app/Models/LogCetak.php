<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogCetak extends Model
{
    use HasFactory;

    /**
     * Database connection used by the model.
     *
     * @var string
     */
    protected $connection = 'sqlite_log';

    /**
     * Table name.
     *
     * @var string
     */
    protected $table = 'log_cetak';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'resep_id',
        'farmasi_user_id',
        'no_resep',
        'nama_pasien',
        'filename_pdf',
        'path_pdf',
        'dicetak_pada',
        'is_reprint',
    ];

    /**
     * Casting rules.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'dicetak_pada' => 'datetime',
        'is_reprint' => 'boolean',
    ];
}
