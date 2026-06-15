<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Jurnal Kesehatan Pasien</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 14px;
            color: #333;
            line-height: 1.5;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #10b981;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            color: #0f172a;
            font-size: 24px;
        }
        .header p {
            margin: 5px 0 0;
            color: #64748b;
            font-size: 12px;
        }
        .section-title {
            background-color: #f1f5f9;
            padding: 8px 12px;
            font-weight: bold;
            color: #0f172a;
            border-left: 4px solid #10b981;
            margin-top: 25px;
            margin-bottom: 15px;
            font-size: 16px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        th, td {
            padding: 8px 0;
            vertical-align: top;
        }
        .label-col {
            width: 35%;
            font-weight: bold;
            color: #475569;
        }
        .val-col {
            width: 65%;
        }
        .info-table td {
            border-bottom: 1px dashed #e2e8f0;
        }
        .prescription-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .prescription-table th, .prescription-table td {
            border: 1px solid #cbd5e1;
            padding: 8px;
            text-align: left;
        }
        .prescription-table th {
            background-color: #f8fafc;
            color: #334155;
        }
        .footer {
            margin-top: 50px;
            text-align: right;
        }
        .signature-line {
            display: inline-block;
            border-top: 1px solid #333;
            padding-top: 5px;
            margin-top: 60px;
            width: 200px;
            text-align: center;
        }
    </style>
</head>
<body>

    <div class="header">
        <h1>SI PUSKESMAS & KLINIK</h1>
        <p>Jl. Sehat Sejahtera No. 45, Jakarta Pusat | Telp: (021) 4247854</p>
        <p style="font-weight:bold; margin-top:10px; color:#10b981;">JURNAL KESEHATAN (REKAM MEDIS PASIEN)</p>
    </div>

    <!-- IDENTITAS PASIEN -->
    <div class="section-title">A. IDENTITAS PASIEN</div>
    <table class="info-table">
        <tr>
            <td class="label-col">Nama Lengkap</td>
            <td class="val-col">: {{ $identitas_pasien['nama_lengkap'] }}</td>
        </tr>
        <tr>
            <td class="label-col">Nomor NIK</td>
            <td class="val-col">: {{ $identitas_pasien['nik'] }}</td>
        </tr>
        <tr>
            <td class="label-col">Umur</td>
            <td class="val-col">: {{ $identitas_pasien['umur'] }}</td>
        </tr>
        <tr>
            <td class="label-col">Jenis Kelamin</td>
            <td class="val-col">: {{ $identitas_pasien['jenis_kelamin'] }}</td>
        </tr>
        <tr>
            <td class="label-col">Golongan Darah</td>
            <td class="val-col">: {{ $identitas_pasien['golongan_darah'] }}</td>
        </tr>
        <tr>
            <td class="label-col">Tinggi / Berat Badan</td>
            <td class="val-col">: {{ $identitas_pasien['tinggi_badan'] }} / {{ $identitas_pasien['berat_badan'] }}</td>
        </tr>
        <tr>
            <td class="label-col">IMT (Status Gizi)</td>
            <td class="val-col">: {{ $identitas_pasien['imt_status'] }}</td>
        </tr>
    </table>

    <!-- INFORMASI KUNJUNGAN -->
    <div class="section-title">B. INFORMASI KUNJUNGAN</div>
    <table class="info-table">
        <tr>
            <td class="label-col">Nomor Kunjungan</td>
            <td class="val-col">: <strong>{{ $informasi_kunjungan['nomor_kunjungan'] }}</strong></td>
        </tr>
        <tr>
            <td class="label-col">Tanggal Berobat</td>
            <td class="val-col">: {{ $informasi_kunjungan['tanggal_berobat'] }}</td>
        </tr>
        <tr>
            <td class="label-col">Poli Tujuan</td>
            <td class="val-col">: {{ $informasi_kunjungan['poli_tujuan'] }}</td>
        </tr>
        <tr>
            <td class="label-col">Dokter Pemeriksa</td>
            <td class="val-col">: {{ $informasi_kunjungan['dokter_pemeriksa'] }}</td>
        </tr>
    </table>

    <!-- HASIL PEMERIKSAAN MEDIS -->
    <div class="section-title">C. HASIL PEMERIKSAAN MEDIS</div>
    <table class="info-table">
        <tr>
            <td class="label-col">Keluhan Pasien</td>
            <td class="val-col">: {{ $hasil_pemeriksaan['keluhan_awal'] }}</td>
        </tr>
        <tr>
            <td class="label-col">Pemeriksaan Fisik</td>
            <td class="val-col">: {{ $hasil_pemeriksaan['pemeriksaan_fisik'] }}</td>
        </tr>
        <tr>
            <td class="label-col">Diagnosa Utama</td>
            <td class="val-col">: <strong>{{ $hasil_pemeriksaan['diagnosa'] }}</strong></td>
        </tr>
        <tr>
            <td class="label-col">Tindakan Medis</td>
            <td class="val-col">: {{ $hasil_pemeriksaan['tindakan'] }}</td>
        </tr>
    </table>

    <!-- TERAPI & RESEP OBAT -->
    <div class="section-title">D. TERAPI & RESEP OBAT</div>
    @if(empty($resep_obat))
        <p style="color: #64748b; font-style: italic;">Tidak ada resep obat yang diberikan pada kunjungan ini.</p>
    @else
        <table class="prescription-table">
            <thead>
                <tr>
                    <th width="5%">No</th>
                    <th width="40%">Nama Obat</th>
                    <th width="15%">Jumlah</th>
                    <th width="40%">Aturan Pakai & Dosis</th>
                </tr>
            </thead>
            <tbody>
                @foreach($resep_obat as $idx => $obat)
                <tr>
                    <td>{{ $idx + 1 }}</td>
                    <td><strong>{{ $obat['nama_obat'] }}</strong></td>
                    <td>{{ $obat['jumlah'] }}</td>
                    <td>{{ $obat['aturan_pakai'] }} ({{ $obat['dosis'] }})</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <p style="font-size: 12px; margin-top: 5px;"><strong>Catatan Tambahan:</strong> {{ $hasil_pemeriksaan['catatan_tambahan'] }}</p>
    @endif

    <!-- TANDA TANGAN -->
    <div class="footer">
        <p>Jakarta, {{ date('d F Y') }}</p>
        <p>Dokter Pemeriksa,</p>
        <div class="signature-line">
            {{ $informasi_kunjungan['dokter_pemeriksa'] }}
        </div>
        <p style="font-size: 10px; color: #94a3b8; text-align: center; margin-top: 30px;">
            Dokumen ini dicetak secara otomatis dari Sistem Informasi Puskesmas.<br>
            Rahasia Medis - Hanya untuk Pasien yang bersangkutan.
        </p>
    </div>

</body>
</html>
