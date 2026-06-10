<x-mail::message>
# Halo, {{ $kunjungan->pasien->user->name }}!

Pendaftaran kunjungan Anda di **Puskesmas & Klinik** telah berhasil dikonfirmasi.

Berikut adalah kartu antrian digital Anda:

<x-mail::panel>
## **NOMOR ANTRIAN**
# **{{ str_pad((string)$kunjungan->no_antrian, 3, '0', STR_PAD_LEFT) }}**

**Poli Tujuan:** {{ $kunjungan->poli->nama_poli }}  
**Tanggal Kunjungan:** {{ $kunjungan->tanggal_kunjungan->format('d-m-Y') }}  
**Nomor Kunjungan:** {{ $kunjungan->no_kunjungan }}  
**Jenis Pasien:** {{ strtoupper($kunjungan->jenis_kunjungan) }}  
</x-mail::panel>

### QR Code Scan Antrian:
![QR Code](https://api.qrserver.com/v1/create-qr-code/?size=150x150&data={{ urlencode($kunjungan->no_kunjungan) }})

*Silakan tunjukkan QR Code ini atau sebutkan nomor antrian Anda kepada petugas ketika dipanggil di loket / poli.*

Salam Sehat,<br>
**Puskesmas & Klinik**
</x-mail::message>
