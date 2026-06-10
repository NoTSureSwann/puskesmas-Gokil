<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Kunjungan;
use Carbon\Carbon;

class KunjunganObserver
{
    /**
     * Handle the Kunjungan "creating" event.
     */
    public function creating(Kunjungan $kunjungan): void
    {
        $visitDate = $kunjungan->tanggal_kunjungan ? Carbon::parse($kunjungan->tanggal_kunjungan) : Carbon::today();
        $dateStr = $visitDate->format('Ymd');
        $dateStart = $visitDate->copy()->startOfDay();
        $dateEnd = $visitDate->copy()->endOfDay();

        // 1. Generate no_kunjungan: KJN-YYYYMMDD-XXXX
        $countToday = Kunjungan::whereBetween('tanggal_kunjungan', [$dateStart, $dateEnd])->count();
        $sequence = str_pad((string)($countToday + 1), 4, '0', STR_PAD_LEFT);
        $kunjungan->no_kunjungan = "KJN-{$dateStr}-{$sequence}";

        // 2. Generate no_antrian per day per poli (reset at midnight)
        $maxAntrian = Kunjungan::where('poli_id', $kunjungan->poli_id)
            ->whereBetween('tanggal_kunjungan', [$dateStart, $dateEnd])
            ->max('no_antrian');

        $kunjungan->no_antrian = ($maxAntrian ?? 0) + 1;

        // 3. Set jam_daftar if empty
        if (empty($kunjungan->jam_daftar)) {
            $kunjungan->jam_daftar = now();
        }
    }
}
