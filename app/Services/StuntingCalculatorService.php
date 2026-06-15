<?php

namespace App\Services;

class StuntingCalculatorService
{
    /**
     * Data Standar Pertumbuhan WHO untuk Tinggi Badan Berdasarkan Umur (Height-for-Age)
     * Format: [ Umur_Bulan => [Median, SD] ]
     */
    private array $whoBoys = [
        0 => [49.9, 1.9], 1 => [54.7, 2.0], 2 => [58.4, 2.1], 3 => [61.4, 2.1],
        4 => [63.9, 2.1], 5 => [65.9, 2.1], 6 => [67.6, 2.1], 7 => [69.2, 2.1],
        8 => [70.6, 2.2], 9 => [72.0, 2.2], 10 => [73.3, 2.2], 11 => [74.5, 2.2],
        12 => [75.7, 2.3], 13 => [76.9, 2.3], 14 => [78.0, 2.3], 15 => [79.1, 2.4],
        16 => [80.2, 2.4], 17 => [81.2, 2.4], 18 => [82.3, 2.4], 19 => [83.2, 2.5],
        20 => [84.2, 2.5], 21 => [85.1, 2.5], 22 => [86.0, 2.6], 23 => [86.9, 2.6],
        24 => [87.8, 2.6], 25 => [88.0, 2.7], 26 => [88.8, 2.7], 27 => [89.6, 2.7],
        28 => [90.4, 2.8], 29 => [91.2, 2.8], 30 => [91.9, 2.8], 31 => [92.7, 2.9],
        32 => [93.4, 2.9], 33 => [94.1, 2.9], 34 => [94.8, 3.0], 35 => [95.5, 3.0],
        36 => [96.1, 3.0], 37 => [96.8, 3.1], 38 => [97.5, 3.1], 39 => [98.1, 3.1],
        40 => [98.7, 3.2], 41 => [99.4, 3.2], 42 => [100.0, 3.2], 43 => [100.6, 3.3],
        44 => [101.2, 3.3], 45 => [101.8, 3.3], 46 => [102.4, 3.4], 47 => [103.0, 3.4],
        48 => [103.5, 3.4], 49 => [104.1, 3.5], 50 => [104.7, 3.5], 51 => [105.2, 3.5],
        52 => [105.8, 3.6], 53 => [106.3, 3.6], 54 => [106.9, 3.6], 55 => [107.4, 3.7],
        56 => [107.9, 3.7], 57 => [108.5, 3.7], 58 => [109.0, 3.8], 59 => [109.5, 3.8],
        60 => [110.0, 3.8]
    ];

    private array $whoGirls = [
        0 => [49.1, 1.8], 1 => [53.7, 1.9], 2 => [57.1, 2.0], 3 => [59.8, 2.1],
        4 => [62.1, 2.1], 5 => [64.0, 2.1], 6 => [65.7, 2.2], 7 => [67.3, 2.2],
        8 => [68.7, 2.3], 9 => [70.1, 2.3], 10 => [71.5, 2.3], 11 => [72.8, 2.4],
        12 => [74.0, 2.4], 13 => [75.2, 2.4], 14 => [76.4, 2.5], 15 => [77.5, 2.5],
        16 => [78.6, 2.5], 17 => [79.7, 2.6], 18 => [80.7, 2.6], 19 => [81.7, 2.6],
        20 => [82.7, 2.7], 21 => [83.7, 2.7], 22 => [84.6, 2.7], 23 => [85.5, 2.8],
        24 => [86.4, 2.8], 25 => [86.6, 2.9], 26 => [87.4, 2.9], 27 => [88.3, 2.9],
        28 => [89.1, 3.0], 29 => [89.9, 3.0], 30 => [90.7, 3.0], 31 => [91.4, 3.1],
        32 => [92.2, 3.1], 33 => [92.9, 3.1], 34 => [93.6, 3.2], 35 => [94.4, 3.2],
        36 => [95.1, 3.2], 37 => [95.7, 3.3], 38 => [96.4, 3.3], 39 => [97.1, 3.3],
        40 => [97.7, 3.4], 41 => [98.4, 3.4], 42 => [99.0, 3.4], 43 => [99.7, 3.5],
        44 => [100.3, 3.5], 45 => [100.9, 3.5], 46 => [101.5, 3.6], 47 => [102.1, 3.6],
        48 => [102.7, 3.6], 49 => [103.3, 3.7], 50 => [103.9, 3.7], 51 => [104.5, 3.7],
        52 => [105.0, 3.8], 53 => [105.6, 3.8], 54 => [106.2, 3.8], 55 => [106.7, 3.9],
        56 => [107.3, 3.9], 57 => [107.8, 3.9], 58 => [108.4, 4.0], 59 => [108.9, 4.0],
        60 => [109.4, 4.0]
    ];

    /**
     * Hitung Height-for-Age Z-Score (HAZ)
     */
    public function calculateZScore(int $ageMonths, string $gender, float $heightCm): array
    {
        // Batas maksimal umur yang didukung adalah 60 bulan (5 tahun)
        if ($ageMonths < 0 || $ageMonths > 60) {
            return [
                'error' => 'Kalkulator ini hanya diperuntukkan untuk balita usia 0 hingga 60 bulan.',
            ];
        }

        $reference = $gender === 'L' ? $this->whoBoys[$ageMonths] : $this->whoGirls[$ageMonths];
        $median = $reference[0];
        $sd = $reference[1];

        // Rumus sederhana Z-Score
        $zScore = round(($heightCm - $median) / $sd, 2);

        // Klasifikasi WHO
        $status = '';
        $color = '';
        $icon = '';
        $advice = '';

        if ($zScore < -3.0) {
            $status = 'Severely Stunted (Sangat Pendek)';
            $color = 'danger';
            $icon = 'fa-triangle-exclamation';
            $advice = 'Anak Anda berisiko tinggi kekurangan gizi kronis. Segera konsultasikan dengan dokter spesialis anak atau ahli gizi puskesmas untuk intervensi segera.';
        } elseif ($zScore >= -3.0 && $zScore < -2.0) {
            $status = 'Stunted (Pendek)';
            $color = 'warning';
            $icon = 'fa-exclamation-circle';
            $advice = 'Anak Anda terindikasi pendek (stunted) untuk usianya. Evaluasi asupan nutrisi protein hewani dan rutin periksakan ke Posyandu/Puskesmas.';
        } elseif ($zScore >= -2.0 && $zScore <= 3.0) {
            $status = 'Normal';
            $color = 'success';
            $icon = 'fa-check-circle';
            $advice = 'Pertumbuhan tinggi badan anak Anda berada dalam rentang normal. Pertahankan asupan gizi seimbang dan stimulasi yang baik.';
        } else {
            $status = 'Tinggi';
            $color = 'info';
            $icon = 'fa-arrow-up-right-dots';
            $advice = 'Anak Anda tergolong tinggi untuk usianya. Pastikan perkembangan motorik dan berat badannya juga seimbang.';
        }

        return [
            'z_score' => $zScore,
            'median' => $median,
            'status' => $status,
            'color' => $color,
            'icon' => $icon,
            'advice' => $advice,
        ];
    }
}
