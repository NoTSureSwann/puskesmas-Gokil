<x-mail::message>
# 📊 Laporan Kunjungan Harian

**Puskesmas & Klinik**  
**Tanggal:** {{ $reportDate }}

Berikut adalah rangkuman transaksi pendaftaran dan pelayanan kunjungan pasien untuk hari ini:

<x-mail::panel>
### **Ringkasan Aktivitas Pelayanan:**
- **Total Kunjungan Masuk:** {{ $reportData['total_visits'] }}
- **Pelayanan Selesai:** {{ $reportData['visits_completed'] }}
- **Pasien Sedang Diperiksa/Menunggu:** {{ $reportData['visits_in_progress'] }}
- **Kunjungan Dibatalkan (Batal):** {{ $reportData['visits_cancelled'] }}
</x-mail::panel>

### **Rincian Jumlah Kunjungan Per Klinik (Poli):**
@foreach ($reportData['visits_per_poli'] as $poliName => $count)
- **{{ $poliName }}:** {{ $count }} pasien
@endforeach

### **Statistik Berdasarkan Tipe Pembayaran:**
- **Pasien Umum:** {{ $reportData['visits_umum'] }} pasien
- **Pasien BPJS:** {{ $reportData['visits_bpjs'] }} pasien

*Laporan harian ini dikirimkan secara otomatis oleh sistem scheduler puskesmas.*

Salam Kerja Sama,<br>
**Layanan TI Puskesmas & Klinik**
</x-mail::message>
