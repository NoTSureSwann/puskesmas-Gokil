<x-mail::message>
# Halo, {{ $resep->kunjungan->pasien->user->name }}!

Resep obat elektronik Anda dengan nomor resep **{{ $resep->no_resep }}** telah selesai diproses oleh bagian Farmasi.

Berikut adalah daftar obat yang siap Anda ambil di Apotek:

<x-mail::panel>
### **Daftar Obat:**
@foreach ($resep->detailResep as $detail)
- **{{ $detail->obat->nama_obat }}** - {{ $detail->jumlah }} {{ $detail->obat->satuan }}  
  *Aturan Pakai:* {{ $detail->dosis }} ({{ $detail->aturan_pakai }})
@endforeach
</x-mail::panel>

### **Instruksi Pengambilan:**
1. Silakan menuju ke **Loket Antrean Farmasi / Apotek Puskesmas & Klinik**.
2. Tunjukkan nomor resep **{{ $resep->no_resep }}** atau tunjukkan email ini kepada petugas.
3. Petugas kami akan melakukan serah terima obat beserta penjelasan detail cara pakainya.

Semoga lekas sembuh!

Salam Sehat,<br>
**Puskesmas & Klinik**
</x-mail::message>
