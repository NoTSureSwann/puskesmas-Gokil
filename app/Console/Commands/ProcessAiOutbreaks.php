<?php

namespace App\Console\Commands;

use App\Models\AiDataset;
use App\Models\WabahGeospasial;
use Illuminate\Console\Command;
use Carbon\Carbon;

class ProcessAiOutbreaks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ai:process-outbreaks';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mesin ML Latar Belakang untuk menganalisis dan memetakan wabah spasial dari AI Datasets';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Memulai AI Engine Processing...');

        // 1. Ambil data dari 30 hari terakhir
        $recentData = AiDataset::query()->where('created_at', '>=', Carbon::now()->subDays(30))->get();

        if ($recentData->isEmpty()) {
            $this->warn('Tidak ada dataset baru untuk diproses.');
            return;
        }

        $diseaseCounts = [];
        
        // 2. Ekstraksi dan Hitung Penyakit
        foreach ($recentData as $data) {
            $penyakits = is_array($data->kemungkinan_penyakit) ? $data->kemungkinan_penyakit : json_decode($data->kemungkinan_penyakit, true);
            if (!empty($penyakits)) {
                foreach ($penyakits as $penyakit) {
                    $penyakit = trim($penyakit);
                    if (!isset($diseaseCounts[$penyakit])) {
                        $diseaseCounts[$penyakit] = [
                            'count' => 0,
                            'urgency' => []
                        ];
                    }
                    $diseaseCounts[$penyakit]['count']++;
                    $diseaseCounts[$penyakit]['urgency'][] = $data->tingkat_urgensi;
                }
            }
        }

        // 3. Deteksi Wabah (Jika >= 2 kasus untuk simulasi)
        $outbreaks = [];
        foreach ($diseaseCounts as $penyakit => $data) {
            // 3. Evaluasi Statistical Noise vs Real Effect
            // Jika kasus sangat jarang (< 3 dalam sebulan), kita anggap ini statistical noise, bukan outbreak nyata
            if ($data['count'] < 3) {
                // Abaikan pembentukan klaster untuk mengurangi False Positives (ML Fundamentals)
                continue;
            }

            // 4. Hitung Radius Penyebaran (Simplified: 1km per 10 kasus) untuk simulasi)
            // Tentukan tingkat bahaya berdasarkan frekuensi urgensi tinggi
            $tinggiCount = count(array_filter($data['urgency'], fn($u) => strtolower($u) === 'tinggi'));
                
                $bahaya = 'Rendah';
                if ($data['count'] >= 5 || $tinggiCount >= 2) {
                    $bahaya = 'Tinggi';
                } elseif ($data['count'] >= 3 || $tinggiCount >= 1) {
                    $bahaya = 'Sedang';
                }

                $outbreaks[] = [
                    'nama_penyakit' => $penyakit,
                    'kasus_aktif' => $data['count'],
                    'tingkat_bahaya' => $bahaya
                ];
        }

        // 4. Update ke Tabel Wabah Geospasial
        WabahGeospasial::truncate(); // Kosongkan data lama agar selalu fresh sesuai periode
        
        // Titik tengah simulasi (Bisa diubah ke titik spesifik jika pasien punya lat/lng)
        $baseLat = -6.200000; // Jakarta
        $baseLng = 106.816666;

        foreach ($outbreaks as $outbreak) {
            // Sebar koordinat secara acak di sekitar base coordinate untuk visualisasi
            $lat = $baseLat + (rand(-100, 100) / 10000);
            $lng = $baseLng + (rand(-100, 100) / 10000);
            
            $radius = $outbreak['kasus_aktif'] * 500; // Radius membesar sesuai jumlah kasus

            WabahGeospasial::create([
                'nama_penyakit' => $outbreak['nama_penyakit'],
                'latitude' => $lat,
                'longitude' => $lng,
                'kota' => 'Jakarta Raya',
                'radius_km' => $radius,
                'tingkat_bahaya' => $outbreak['tingkat_bahaya'],
                'kasus_aktif' => $outbreak['kasus_aktif'],
                'rekomendasi_ai' => 'Pantau peningkatan kasus ' . $outbreak['nama_penyakit'] . ' di area ini. Distribusikan obat-obatan terkait.'
            ]);
        }

        $this->info('Selesai! ' . count($outbreaks) . ' klaster wabah berhasil dipetakan ke sistem Geospasial.');
    }
}
