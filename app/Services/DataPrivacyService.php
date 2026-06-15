<?php

declare(strict_types=1);

namespace App\Services;

class DataPrivacyService
{
    /**
     * Menyamarkan NIK dengan menyisakan 4 digit pertama dan 4 digit terakhir.
     * Contoh: 3271567890123456 -> 3271********3456
     */
    public static function maskNik(?string $nik): ?string
    {
        if (!$nik || strlen($nik) < 16) {
            return $nik;
        }
        
        return substr($nik, 0, 4) . str_repeat('*', 8) . substr($nik, -4);
    }

    /**
     * Menyamarkan Nomor BPJS dengan menyisakan 3 digit pertama dan 3 digit terakhir.
     * Contoh: 0001234567890 -> 000*******890
     */
    public static function maskBpjs(?string $bpjs): ?string
    {
        if (!$bpjs || strlen($bpjs) < 10) {
            return $bpjs;
        }

        return substr($bpjs, 0, 3) . str_repeat('*', strlen($bpjs) - 6) . substr($bpjs, -3);
    }

    /**
     * Menyamarkan nama (opsional, jika diperlukan).
     * Contoh: Budi Santoso -> Budi S******
     */
    public static function maskName(?string $name): ?string
    {
        if (!$name) {
            return $name;
        }

        $parts = explode(' ', $name);
        if (count($parts) > 1) {
            $firstName = $parts[0];
            $lastName = $parts[count($parts) - 1];
            return $firstName . ' ' . substr($lastName, 0, 1) . str_repeat('*', strlen($lastName) - 1);
        }

        return substr($name, 0, 2) . str_repeat('*', strlen($name) - 2);
    }
}
