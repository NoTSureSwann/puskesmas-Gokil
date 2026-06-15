<?php

declare(strict_types=1);

namespace App\ViewModels;

use App\Models\Kunjungan;
use Carbon\Carbon;

/**
 * Class JurnalKesehatanViewModel
 * Menerapkan Arsitektur MVVM dengan membungkus Model dan menyediakan
 * data presentation logic untuk View/PDF tanpa membebani Controller.
 */
class JurnalKesehatanViewModel
{
    private Kunjungan $kunjungan;

    public function __construct(Kunjungan $kunjungan)
    {
        // Load relasi yang diperlukan secara Eager untuk mencegah N+1 Query
        $this->kunjungan = $kunjungan->loadMissing([
            'pasien.user', 
            'dokter.user', 
            'poli', 
            'resep.detailResep.obat', 
            'rekamMedis'
        ]);
    }

    /**
     * Data utama untuk dikonsumsi View (PDF).
     */
    public function toArray(): array
    {
        $pasien = $this->kunjungan->pasien;
        $userPasien = $pasien->user;

        return [
            'identitas_pasien' => [
                'nama_lengkap' => $userPasien->name,
                'nik' => $pasien->nik,
                'umur' => $this->hitungUmur($pasien->tanggal_lahir),
                'jenis_kelamin' => $pasien->jenis_kelamin === 'L' ? 'Laki-Laki' : 'Perempuan',
                'golongan_darah' => $pasien->golongan_darah ?? 'Tidak Diketahui',
                'tinggi_badan' => $pasien->tinggi_badan ? $pasien->tinggi_badan . ' cm' : 'Tidak Tercatat',
                'berat_badan' => $pasien->berat_badan ? $pasien->berat_badan . ' kg' : 'Tidak Tercatat',
                'imt_status' => $this->hitungIMT($pasien->tinggi_badan, $pasien->berat_badan),
            ],
            'informasi_kunjungan' => [
                'nomor_kunjungan' => $this->kunjungan->no_kunjungan,
                'tanggal_berobat' => Carbon::parse($this->kunjungan->tanggal_kunjungan)->translatedFormat('l, d F Y'),
                'poli_tujuan' => $this->kunjungan->poli->nama_poli,
                'dokter_pemeriksa' => 'Dr. ' . $this->kunjungan->dokter->user->name,
            ],
            'hasil_pemeriksaan' => $this->formatPemeriksaan(),
            'resep_obat' => $this->formatResep(),
        ];
    }

    /**
     * Format presentasi data pemeriksaan medis.
     */
    private function formatPemeriksaan(): array
    {
        $rekam = $this->kunjungan->rekamMedis;

        if (!$rekam) {
            return [
                'keluhan_awal' => $this->kunjungan->keluhan,
                'diagnosa' => 'Belum ada diagnosa tercatat.',
                'tindakan' => 'Belum ada tindakan tercatat.',
                'catatan_tambahan' => '-',
            ];
        }

        return [
            'keluhan_awal' => $rekam->keluhan,
            'pemeriksaan_fisik' => $rekam->pemeriksaan_fisik ?? 'Tidak ada catatan fisik khusus.',
            'diagnosa' => $rekam->diagnosa,
            'tindakan' => $rekam->tindakan ?? 'Observasi & Medikamentosa',
            'catatan_tambahan' => $rekam->resep_tambahan_catatan ?? '-',
        ];
    }

    /**
     * Format presentasi resep obat.
     */
    private function formatResep(): array
    {
        $resep = $this->kunjungan->resep;
        if (!$resep || $resep->detailResep->isEmpty()) {
            return [];
        }

        $obatList = [];
        foreach ($resep->detailResep as $detail) {
            $obatList[] = [
                'nama_obat' => $detail->obat->nama_obat,
                'jumlah' => $detail->jumlah . ' ' . $detail->obat->satuan,
                'dosis' => $detail->dosis,
                'aturan_pakai' => $detail->aturan_pakai,
            ];
        }

        return $obatList;
    }

    /**
     * Menghitung umur dinamis berdasarkan tanggal lahir.
     */
    private function hitungUmur(?string $tanggalLahir): string
    {
        if (!$tanggalLahir) return 'Tidak Tercatat';
        
        $dob = Carbon::parse($tanggalLahir);
        $umurTahun = $dob->diffInYears(now());
        $umurBulan = $dob->diffInMonths(now()) % 12;

        return "{$umurTahun} Tahun, {$umurBulan} Bulan";
    }

    /**
     * Menghitung Indeks Massa Tubuh (IMT / BMI)
     */
    private function hitungIMT(int|float|null $tinggiCm, int|float|null $beratKg): string
    {
        if (!$tinggiCm || !$beratKg) {
            return '-';
        }

        $tinggiM = $tinggiCm / 100;
        $bmi = $beratKg / ($tinggiM * $tinggiM);
        $bmi = round($bmi, 1);

        if ($bmi < 18.5) return "{$bmi} (Kekurangan Berat Badan)";
        if ($bmi >= 18.5 && $bmi < 24.9) return "{$bmi} (Normal)";
        if ($bmi >= 25 && $bmi < 29.9) return "{$bmi} (Kelebihan Berat Badan)";
        return "{$bmi} (Obesitas)";
    }
}
