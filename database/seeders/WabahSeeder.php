<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class WabahSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\WabahGeospasial::insert([
            [
                'nama_penyakit' => 'Demam Berdarah (DBD)',
                'latitude' => -6.2588,
                'longitude' => 106.8456, // Sekitar Jakarta Selatan
                'kota' => 'Jakarta Selatan',
                'radius_km' => 12,
                'tingkat_bahaya' => 'Tinggi',
                'kasus_aktif' => 145,
                'rekomendasi_ai' => 'Awas! Puncak musim penghujan menyebabkan genangan air di kawasan padat penduduk Jakarta Selatan. Rekomendasi kBot: Segera lakukan Fogging massal dan gerakan 3M Plus.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_penyakit' => 'Typus (Tifus)',
                'latitude' => -6.3927,
                'longitude' => 106.8227, // Sekitar Depok
                'kota' => 'Depok',
                'radius_km' => 8,
                'tingkat_bahaya' => 'Sedang',
                'kasus_aktif' => 56,
                'rekomendasi_ai' => 'Kualitas sanitasi air di beberapa wilayah Depok terpantau menurun. kBot merekomendasikan: Rebus air minum hingga mendidih dan hindari jajan sembarangan.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_penyakit' => 'Malaria',
                'latitude' => -2.2155,
                'longitude' => 113.9161, // Kalimantan Tengah (Palangkaraya area)
                'kota' => 'Palangka Raya',
                'radius_km' => 30,
                'tingkat_bahaya' => 'Rendah',
                'kasus_aktif' => 12,
                'rekomendasi_ai' => 'Pantauan kBot di area hutan tropis Kalimantan Tengah stabil. Tetap gunakan kelambu berinsektisida saat malam hari.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_penyakit' => 'ISPA',
                'latitude' => -6.9147,
                'longitude' => 107.6098, // Bandung
                'kota' => 'Bandung',
                'radius_km' => 15,
                'tingkat_bahaya' => 'Sedang',
                'kasus_aktif' => 89,
                'rekomendasi_ai' => 'Polusi udara dan cuaca dingin ekstrem meningkatkan kasus ISPA. Pengguna publik diimbau menggunakan masker standar KN95 saat beraktivitas di luar.',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }
}
