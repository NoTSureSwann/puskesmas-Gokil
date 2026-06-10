<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Jurnal RME Pasien</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; line-height: 1.5; color: #333; }
        .header { text-align: center; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 20px; }
        .legal { border: 1px solid #d32f2f; background: #ffebee; padding: 10px; margin-bottom: 20px; font-size: 11px; }
        .content { margin-bottom: 20px; }
        .chart-container { text-align: center; margin-top: 20px; }
        .chart-container img { max-width: 45%; margin: 0 10px; border: 1px solid #ddd; }
        .footer { font-size: 10px; color: #777; margin-top: 40px; text-align: justify; }
        .hash-box { background: #eee; padding: 5px; font-family: monospace; word-break: break-all; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    </style>
</head>
<body>

    <div class="header">
        <h2>JURNAL REKAM MEDIS ELEKTRONIK (RME)</h2>
        <p>Dicetak pada: {{ $patient['tanggal_cetak'] }}</p>
    </div>

    <div class="legal">
        <strong>PENTING (Klausul UU ITE):</strong>
        Berdasarkan Pasal 5 ayat (1) dan Pasal 6 UU ITE, Informasi Elektronik dan/atau Dokumen Elektronik beserta hasil cetaknya merupakan alat bukti hukum yang sah. 
        Sesuai Pasal 32 ayat (1) UU ITE, dilarang keras mengubah, merusak, atau menyembunyikan keaslian dokumen ini.
    </div>

    <div class="content">
        <h3>1. Biodata & Klasifikasi Medis Global</h3>
        <table>
            <tr>
                <th width="30%">Nama Pasien</th>
                <td>{{ $patient['nama'] }}</td>
            </tr>
            <tr>
                <th>Email Terdaftar</th>
                <td>{{ $patient['email'] }}</td>
            </tr>
            <tr>
                <th>Kesimpulan Diagnosis AI</th>
                <td>{{ $patient['diagnosis_ai_summary'] }}</td>
            </tr>
            <tr style="background-color: #f0f8ff;">
                <th>Kategori Triage (CDC)</th>
                <td><strong>{{ $patient['cdc_triage'] ?? 'GREEN TAG (Non-Urgent)' }}</strong></td>
            </tr>
            <tr style="background-color: #f0f8ff;">
                <th>Kode Penyakit (WHO ICD-10)</th>
                <td>{{ $patient['icd10_code'] ?? 'R68.89 (Gejala umum lainnya)' }}</td>
            </tr>
            <tr style="background-color: #ffebee;">
                <th>Status Darurat (IHR)</th>
                <td><strong style="color: #d32f2f;">{{ $patient['ihr_status'] ?? 'Non-PHEIC' }}</strong></td>
            </tr>
        </table>
    </div>

    <div class="content">
        <h3>2. Visualisasi Grafik Kondisi (Metrik AI)</h3>
        <p>Berikut adalah representasi visual riwayat kesehatan berdasarkan sentimen AI:</p>
        <div class="chart-container">
            <!-- Menampilkan gambar chart dari QuickChart (Base64 atau Direct URL jika dompdf mendownloadnya) -->
            <img src="{{ $pieChartUrl }}" alt="Pie Chart">
            <img src="{{ $lineChartUrl }}" alt="Line Chart">
        </div>
    </div>

    <div class="content">
        <h3>3. AI Lifestyle Prescriptions (Rekomendasi Gaya Hidup Cerdas)</h3>
        <table>
            <tr>
                <th width="30%">Tips Sehat</th>
                <td>{{ $patient['ai_tips'] }}</td>
            </tr>
            <tr>
                <th>Rekomendasi Makanan</th>
                <td>{{ $patient['ai_food'] }}</td>
            </tr>
            <tr>
                <th>Rekomendasi Minuman</th>
                <td>{{ $patient['ai_drink'] }}</td>
            </tr>
        </table>
    </div>

    <div class="content">
        <h3>4. Rincian Tagihan & Struk Obat (Billing)</h3>
        <table>
            <thead>
                <tr>
                    <th>Komponen Biaya</th>
                    <th>Qty</th>
                    <th>Harga Satuan</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>Jasa Pemeriksaan</strong><br><small>{{ $billing['jasa_dokter']['nama_dokter'] }}</small></td>
                    <td>1</td>
                    <td>Rp {{ number_format($billing['jasa_dokter']['biaya'], 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($billing['jasa_dokter']['biaya'], 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td colspan="4" style="background-color: #f9f9f9;"><strong>Resep Obat Farmasi</strong></td>
                </tr>
                @foreach($billing['obat'] as $obat)
                <tr>
                    <td>{{ $obat['nama'] }}</td>
                    <td>{{ $obat['qty'] }}</td>
                    <td>Rp {{ number_format($obat['harga_satuan'], 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($obat['subtotal'], 0, ',', '.') }}</td>
                </tr>
                @endforeach
                <tr>
                    <th colspan="3" style="text-align: right;">Total Keseluruhan</th>
                    <th>Rp {{ number_format($billing['total'], 0, ',', '.') }}</th>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="footer">
        <strong>Tanda Tangan Digital / Digital Fingerprint (SHA-256)</strong><br>
        <div class="hash-box">
            Hash: {{ $digitalHash }}
        </div>
        <p>Dokumen ini ditandatangani secara elektronik. Hash ini digunakan untuk memverifikasi keaslian dokumen di dalam *Database Audit Logs* rumah sakit. Jika dokumen diretas atau diubah meskipun hanya satu spasi, maka *Digital Hash* ini akan terbukti tidak valid (Anti-Tampering).</p>
    </div>

</body>
</html>
