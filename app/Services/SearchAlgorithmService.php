<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Class SearchAlgorithmService
 * Berisi penerapan algoritma pencarian teks eksplisit untuk keperluan komputasi 
 * string-matching dan analisis Big-O.
 */
class SearchAlgorithmService
{
    /**
     * Algoritma 1: Sequential Search / Linear Search
     * 
     * Mencari pola (pattern) di dalam teks menggunakan pendekatan linier O(N*M).
     * Sering digunakan sebagai pembanding baseline.
     * 
     * @param string $text
     * @param string $pattern
     * @return array Index di mana pattern ditemukan
     */
    public static function sequentialSearch(string $text, string $pattern): array
    {
        $textLen = strlen($text);
        $patternLen = strlen($pattern);
        $results = [];

        if ($patternLen === 0 || $textLen === 0 || $patternLen > $textLen) {
            return $results;
        }

        // Iterasi satu demi satu (O(N))
        for ($i = 0; $i <= $textLen - $patternLen; $i++) {
            $match = true;
            // Pengecekan substring (O(M))
            for ($j = 0; $j < $patternLen; $j++) {
                if (strtolower($text[$i + $j]) !== strtolower($pattern[$j])) {
                    $match = false;
                    break;
                }
            }
            if ($match) {
                $results[] = $i;
            }
        }

        return $results;
    }

    /**
     * Membangun tabel Bad Character Heuristic untuk Boyer-Moore.
     */
    private static function buildBadCharTable(string $pattern): array
    {
        $table = [];
        $patternLen = strlen($pattern);
        
        // Inisialisasi tabel dengan -1
        for ($i = 0; $i < 256; $i++) {
            $table[$i] = -1;
        }

        // Isi indeks terakhir setiap kemunculan karakter dalam pattern
        for ($i = 0; $i < $patternLen; $i++) {
            $ascii = ord($pattern[$i]);
            $table[$ascii] = $i;
        }

        return $table;
    }

    /**
     * Algoritma 2: Boyer-Moore Search Algorithm
     * 
     * Algoritma optimasi pencarian string berkinerja tinggi O(N/M) pada Best Case, 
     * memindai karakter dari Kanan ke Kiri menggunakan Bad Character Heuristic
     * untuk melompat alih-alih mengecek satu per satu.
     * 
     * @param string $text
     * @param string $pattern
     * @return array Index di mana pattern ditemukan
     */
    public static function boyerMooreSearch(string $text, string $pattern): array
    {
        // Konversi ke lowercase untuk case-insensitive search
        $text = strtolower($text);
        $pattern = strtolower($pattern);

        $textLen = strlen($text);
        $patternLen = strlen($pattern);
        $results = [];

        if ($patternLen === 0 || $textLen === 0 || $patternLen > $textLen) {
            return $results;
        }

        $badCharTable = self::buildBadCharTable($pattern);
        $shift = 0; // Shift of the pattern with respect to text

        while ($shift <= ($textLen - $patternLen)) {
            $j = $patternLen - 1;

            // Terus kurangi $j selama karakter pattern cocok dengan teks
            while ($j >= 0 && $pattern[$j] === $text[$shift + $j]) {
                $j--;
            }

            // Jika pattern ditemukan
            if ($j < 0) {
                $results[] = $shift;

                // Shift pattern agar tidak stack; cari occurence selanjutnya
                $nextCharAscii = ($shift + $patternLen < $textLen) ? ord($text[$shift + $patternLen]) : 0;
                $shift += ($shift + $patternLen < $textLen) ? $patternLen - $badCharTable[$nextCharAscii] : 1;
            } else {
                // Geser (Shift) berdasarkan heuristik karakter buruk
                $badCharValue = $badCharTable[ord($text[$shift + $j])];
                $shift += max(1, $j - $badCharValue);
            }
        }

        return $results;
    }
}
