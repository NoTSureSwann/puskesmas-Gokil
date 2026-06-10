<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Poli;
use Illuminate\Database\Seeder;

class PoliSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $polis = [
            [
                'kode_poli' => 'PL-UMM',
                'nama_poli' => 'Poli Umum',
                'deskripsi' => 'Layanan pemeriksaan kesehatan umum dan konsultasi medis awal.',
                'is_aktif' => true,
            ],
            [
                'kode_poli' => 'PL-GGI',
                'nama_poli' => 'Poli Gigi',
                'deskripsi' => 'Layanan perawatan kesehatan, pencabutan, dan bedah mulut dasar.',
                'is_aktif' => true,
            ],
            [
                'kode_poli' => 'PL-DLM',
                'nama_poli' => 'Poli Penyakit Dalam',
                'deskripsi' => 'Layanan spesialis diagnosa dan penanganan keluhan organ dalam dan sistemik.',
                'is_aktif' => true,
            ],
            [
                'kode_poli' => 'PL-ANK',
                'nama_poli' => 'Poli Anak',
                'deskripsi' => 'Layanan kesehatan, imunisasi, dan pemantauan tumbuh kembang anak dan bayi.',
                'is_aktif' => true,
            ],
            [
                'kode_poli' => 'PL-OBG',
                'nama_poli' => 'Poli Kandungan (Obgyn)',
                'deskripsi' => 'Layanan spesialis kebidanan, kandungan, dan konsultasi reproduksi wanita.',
                'is_aktif' => true,
            ],
            [
                'kode_poli' => 'PL-BDH',
                'nama_poli' => 'Poli Bedah',
                'deskripsi' => 'Layanan konsultasi dan persiapan operasi bedah umum maupun minor.',
                'is_aktif' => true,
            ],
            [
                'kode_poli' => 'PL-SRF',
                'nama_poli' => 'Poli Saraf',
                'deskripsi' => 'Layanan keluhan neurologi seperti sakit kepala kronis, saraf terjepit, dan stroke.',
                'is_aktif' => true,
            ],
        ];

        foreach ($polis as $poli) {
            Poli::updateOrCreate(['kode_poli' => $poli['kode_poli']], $poli);
        }
    }
}
