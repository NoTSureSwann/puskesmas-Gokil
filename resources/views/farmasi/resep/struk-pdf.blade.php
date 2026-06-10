<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Struk Obat - {{ $resep->no_resep }}</title>
    <style>
        @page {
            size: 80mm 160mm;
            margin: 5mm;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 9pt;
            line-height: 1.3;
            color: #000;
            margin: 0;
            padding: 0;
        }
        .header {
            text-align: center;
            margin-bottom: 5px;
        }
        .header h2 {
            font-size: 11pt;
            margin: 0;
            font-weight: bold;
        }
        .header p {
            font-size: 7.5pt;
            margin: 2px 0 0 0;
            color: #555;
        }
        .divider {
            border-top: 1px dashed #000;
            margin: 8px 0;
        }
        .title {
            text-align: center;
            font-weight: bold;
            font-size: 10pt;
            margin: 5px 0;
            text-transform: uppercase;
        }
        .reprint-tag {
            text-align: center;
            font-weight: bold;
            font-size: 8pt;
            color: #ff0000;
            margin-bottom: 5px;
        }
        .info-table {
            width: 100%;
            margin-bottom: 10px;
        }
        .info-table td {
            padding: 1px 0;
            vertical-align: top;
            font-size: 8pt;
        }
        .info-table td.label {
            width: 35%;
            color: #333;
        }
        .info-table td.value {
            width: 65%;
            font-weight: bold;
        }
        .obat-item {
            margin-bottom: 8px;
            padding-bottom: 8px;
            border-bottom: 1px dotted #ccc;
        }
        .obat-item:last-child {
            border-bottom: none;
        }
        .obat-name {
            font-weight: bold;
            font-size: 8.5pt;
        }
        .obat-qty {
            float: right;
            font-weight: bold;
        }
        .obat-dosis {
            font-size: 9pt;
            font-weight: bold;
            background-color: #eee;
            padding: 2px 4px;
            margin-top: 2px;
            display: inline-block;
            border-radius: 3px;
        }
        .obat-aturan {
            font-size: 8pt;
            margin-top: 1px;
            font-style: italic;
        }
        .footer {
            text-align: center;
            margin-top: 15px;
            font-size: 7.5pt;
        }
        .footer p {
            margin: 2px 0;
        }
    </style>
</head>
<body>

    <div class="header">
        <h2>PUSKESMAS & KLINIK</h2>
        <p>Jl. Sehat Selalu No. 12, Jakarta</p>
        <p>Telp: (021) 4247854</p>
    </div>

    <div class="divider"></div>

    <div class="title">STRUK ETIKET OBAT</div>
    @if ($isReprint)
        <div class="reprint-tag">** DUKUMEN CETAK ULANG (SALINAN) **</div>
    @endif

    <table class="info-table">
        <tr>
            <td class="label">No. Resep</td>
            <td class="value">: {{ $resep->no_resep }}</td>
        </tr>
        <tr>
            <td class="label">No. Kunjungan</td>
            <td class="value">: {{ $resep->kunjungan->no_kunjungan }}</td>
        </tr>
        <tr>
            <td class="label">Pasien</td>
            <td class="value">: {{ $resep->kunjungan->pasien->user->name }}</td>
        </tr>
        <tr>
            <td class="label">Keluhan</td>
            <td class="value">: {{ $resep->kunjungan->keluhan }}</td>
        </tr>
        <tr>
            <td class="label">Umur / JK</td>
            <td class="value">: 
                @php
                    $birthDate = \Carbon\Carbon::parse($resep->kunjungan->pasien->tanggal_lahir);
                    $age = $birthDate->age;
                @endphp
                {{ $age }} Tahun / {{ $resep->kunjungan->pasien->jenis_kelamin }}
            </td>
        </tr>
        <tr>
            <td class="label">Poli / Dokter</td>
            <td class="value">: {{ $resep->kunjungan->poli->nama_poli }} / {{ $resep->dokter->user->name }}</td>
        </tr>
        <tr>
            <td class="label">Tanggal Cetak</td>
            <td class="value">: {{ now()->format('d-m-Y H:i') }}</td>
        </tr>
    </table>

    <div class="divider"></div>

    <div class="title" style="font-size: 8.5pt; margin-bottom: 8px;">Daftar Obat & Aturan Pakai</div>

    <div class="obat-list">
        @foreach ($resep->detailResep as $detail)
            <div class="obat-item">
                <span class="obat-qty">{{ $detail->jumlah }} {{ $detail->obat->satuan }}</span>
                <div class="obat-name">{{ $detail->obat->nama_obat }}</div>
                <div class="obat-dosis">{{ $detail->dosis }}</div>
                <div class="obat-aturan">{{ $detail->aturan_pakai }}</div>
                @if ($detail->keterangan)
                    <div class="small" style="font-size: 7.5pt; color: #555;">Keterangan: {{ $detail->keterangan }}</div>
                @endif
            </div>
        @endforeach
    </div>

    <div class="divider"></div>

    <div class="footer">
        <p><strong>Semoga Lekas Sembuh</strong></p>
        <p>Simpan obat di tempat sejuk & jauhkan dari anak-anak.</p>
        <p>Kelompok IV — BSI Jakarta 2026</p>
    </div>

</body>
</html>
