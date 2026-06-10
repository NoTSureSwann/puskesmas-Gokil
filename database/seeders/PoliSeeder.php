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
                'deskripsi' => 'Layanan pemeriksaan kesehatan umum dan konsultasi medis.',
                'is_aktif' => true,
            ],
            [
                'kode_poli' => 'PL-GGI',
                'nama_poli' => 'Poli Gigi',
                'deskripsi' => 'Layanan perawatan kesehatan gigi dan mulut.',
                'is_aktif' => true,
            ],
            [
                'kode_poli' => 'PL-KIA',
                'nama_poli' => 'KIA',
                'deskripsi' => 'Layanan Kesehatan Ibu dan Anak serta Keluarga Berencana (KB).',
                'is_aktif' => true,
            ],
            [
                'kode_poli' => 'PL-LNS',
                'nama_poli' => 'Poli Lansia',
                'deskripsi' => 'Layanan kesehatan khusus lanjut usia.',
                'is_aktif' => true,
            ],
            [
                'kode_poli' => 'PL-ANK',
                'nama_poli' => 'Poli Anak',
                'deskripsi' => 'Layanan kesehatan dan tumbuh kembang anak.',
                'is_aktif' => true,
            ],
        ];

        foreach ($polis as $poli) {
            Poli::updateOrCreate(['kode_poli' => $poli['kode_poli']], $poli);
        }
    }
}
