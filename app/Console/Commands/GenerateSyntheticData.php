<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AiFeedback;
use App\Models\AiDataset;

class GenerateSyntheticData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ai:generate-synthetic';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Menjalankan Synthetic Data Pipeline untuk memperluas korpus AI secara otomatis (Data-Centric AI)';

    // Kamus sinonim sederhana untuk simulasi Augmentasi Data Medis
    private array $synonyms = [
        'pusing' => ['nyeri kepala', 'kepala terasa berputar', 'sakit kepala berat', 'pening'],
        'demam' => ['suhu tubuh naik', 'panas tinggi', 'menggigil', 'meriang'],
        'batuk' => ['gatal tenggorokan', 'batuk kering', 'batuk berdahak', 'tersedak'],
        'sakit perut' => ['nyeri lambung', 'kram perut', 'mulas', 'perut perih'],
        'lemas' => ['tidak bertenaga', 'letih', 'lesu', 'mudah lelah']
    ];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Memulai Eksekusi Pipeline Data Sintetis (Data-Centric AI)...");

        // Ambil data yang sudah divalidasi oleh manusia (RLHF: Reward = 1 atau telah dikoreksi)
        // Kita gunakan ini sebagai *Seed* (Benih) untuk membuat data sintetis
        $feedbacks = AiFeedback::with('dataset')
            ->where(function($query) {
                $query->where('reward_score', 1)
                      ->orWhereNotNull('corrected_poli');
            })->get();

        if ($feedbacks->isEmpty()) {
            $this->warn("Tidak ada data yang telah dianotasi (RLHF) oleh manusia. Pipeline menunggu data benih.");
            return;
        }

        $syntheticCount = 0;

        foreach ($feedbacks as $feedback) {
            $baseDataset = $feedback->dataset;
            if (!$baseDataset) continue;

            $originalKeluhan = strtolower($baseDataset->keluhan);
            $targetPoli = $feedback->corrected_poli ?? $baseDataset->rekomendasi_poli_nama;

            // Lakukan Augmentasi Kata (Word Replacement)
            $syntheticVariants = [];
            foreach ($this->synonyms as $word => $replacements) {
                if (str_contains($originalKeluhan, $word)) {
                    foreach ($replacements as $replacement) {
                        $newKeluhan = str_replace($word, $replacement, $originalKeluhan);
                        // Hindari duplikasi yang sama persis
                        if ($newKeluhan !== $originalKeluhan && !in_array($newKeluhan, $syntheticVariants)) {
                            $syntheticVariants[] = $newKeluhan;
                        }
                    }
                }
            }

            // Simpan Varian Sintetis ke AiDataset
            foreach ($syntheticVariants as $variantKeluhan) {
                // Cek apakah varian ini sudah pernah disintesis sebelumnya agar tidak infinite loop
                $exists = AiDataset::query()->where('keluhan', '=', $variantKeluhan)->where('is_synthetic', '=', true)->exists();
                if (!$exists) {
                    AiDataset::create([
                        'kunjungan_id' => null, // Data artifisial, bukan pasien asli
                        'keluhan' => ucfirst($variantKeluhan) . " (Data Sintetis Automasi)",
                        'kemungkinan_penyakit' => $baseDataset->kemungkinan_penyakit,
                        'tingkat_urgensi' => $baseDataset->tingkat_urgensi,
                        'rekomendasi_poli_nama' => $targetPoli,
                        'saran_tindakan' => $baseDataset->saran_tindakan,
                        'needs_annotation' => false, // Sudah berbasis data RLHF yang valid
                        'is_synthetic' => true,
                    ]);
                    $syntheticCount++;
                }
            }
        }

        $this->info("Pipeline Selesai! Berhasil menciptakan {$syntheticCount} variasi gejala pasien sintetis dari data yang divalidasi.");
        $this->line("Data ini akan digunakan untuk melatih iterasi AI berikutnya agar lebih kebal terhadap bias linguistik.");
    }
}
