<x-mail::message>
# 🚨 PEMBERITAHUAN RESEP PRIORITAS URGEN

Resep dengan prioritas **URGEN** telah diinput oleh Dokter dan sedang menunggu pemrosesan prioritas di bagian Farmasi.

<x-mail::panel>
### **Detail Informasi Resep:**
- **No. Resep:** {{ $resep->no_resep }}
- **Dokter Pengirim:** {{ $resep->dokter->user->name }} (Poli: {{ $resep->dokter->poli }})
- **Pasien Penerima:** {{ $resep->kunjungan->pasien->user->name }} (NIK: {{ $resep->kunjungan->pasien->nik }})
- **Waktu Input:** {{ $resep->jam_input_resep->format('d-m-Y H:i') }} WIB
- **Catatan Dokter:** *{{ $resep->catatan_dokter ?? 'Tidak ada catatan khusus' }}*
</x-mail::panel>

### **Daftar Obat:**
@foreach ($resep->detailResep as $detail)
- **{{ $detail->obat->nama_obat }}** - {{ $detail->jumlah }} {{ $detail->obat->satuan }}  
  *Aturan Pakai:* {{ $detail->dosis }}
@endforeach

*Harap bagian Farmasi memprioritaskan antrian obat ini untuk mempercepat penanganan medis darurat pasien.*

Salam Kerja Sama,<br>
**Sistem Informasi Puskesmas & Klinik**
</x-mail::message>
