# Product Requirements Document (PRD) & UI/UX Specifications
**Proyek E-Health Puskesmas Enterprise Berbasis AI**
*Mata Kuliah Metopen UBSI - Swandaru Tirta Sandhika*

---

## 1. PRODUCT OVERVIEW (PRD)

### 1.1 Latar Belakang & Tujuan
Platform ini dibangun sebagai solusi rekam medis elektronik (RME) masa depan yang cerdas, aman, dan mematuhi regulasi ketat skala nasional maupun internasional. Sistem ini mendigitalisasi layanan kesehatan puskesmas dengan menggabungkan komputasi Kecerdasan Buatan (AI) berbasis matematika terapan (Kalkulus, Aljabar Linear, Statistika) dengan kapabilitas *Enterprise* seperti pelacakan *real-time* dan kepatuhan perundang-undangan (UU ITE & UU PDP).

### 1.2 Target Pengguna (User Personas)
- **Pasien**: Masyarakat (termasuk Daerah 3T) yang membutuhkan diagnosis awal cerdas, pemantauan riwayat/jurnal kesehatan keluarga, serta laporan rekam medis bersertifikat.
- **Dokter Spesialis**: Tenaga medis (termasuk Spesialis Tropis 3T, Bidan, dan Ahli Gizi) yang membutuhkan rujukan cerdas dari AI serta dasbor rekam medis berstandar WHO.
- **Admin Faskes**: Pengelola rumah sakit yang menuntut sistem pencatatan (*Audit Trail*) yang kebal sabotase dan mematuhi aturan ISO 27001.

### 1.3 Spesifikasi Fitur Utama (Core Features)
1. **AI Medical Diagnostic Engine (kBot)**:
   - Terletak di parameter terpisah. Memanfaatkan Aljabar Linear (pencocokan vektor sentimen), Integral (menghitung luasan area keparahan / *Severity Area*), dan Statistika (Kuartil).
   - Menghasilkan rekomendasi gaya hidup medis (Dietary & Lifestyle) secara otomatis.
2. **Algoritma Pencarian Tingkat Lanjut**: Menggunakan algoritma *Boyer-Moore* dan *Sequential Searching* untuk pencarian teks yang sangat efisien.
3. **Kepatuhan Regulasi Nasional & Global**:
   - **UU PDP & ISO 27001**: Enkripsi NIK *at-rest* dan sistem *Audit Log* mutlak.
   - **UU ITE (Pasal 5, 6, 31, 32)**: Tanda Tangan Digital (SHA-256 Hash) pada dokumen PDF cetak dan *Signed URLs* untuk anti-penyadapan.
   - **Standar Medis Dunia**: HIPAA (PII Anonymization), CDC (*Triage Tagging*), WHO (*ICD-10 Code*), dan IHR (*PHEIC Pandemic Alert*).
4. **Real-Time Monitoring & Family Tree**:
   - Skalabilitas pengawasan hingga 99+ *Concurrent Users* menggunakan *polling* dan/atau *Pusher WebSocket*.
   - Relasi *Sub-profil* untuk pemantauan istri, anak, atau orang tua oleh Kepala Keluarga.
5. **Ekspor Jurnal Paripurna (Billing & Chart)**: Mengekspor visualisasi *Chart.js* (diagram garis dan lingkaran), diagnosa AI, dan struk obat/dokter ke dalam satu berkas PDF berkekuatan hukum.

---

## 2. UI/UX DESIGN SPECIFICATIONS

### 2.1 Konsep & Estetika Dasar (Design Philosophy)
- **Clean & Trustworthy**: Mewakili entitas medis yang bersih, profesional, dan menenangkan (mengurangi kecemasan pasien).
- **Invisible Complexity**: Meski di baliknya berjalan logika matematika rumit dan sistem enkripsi ganda, antarmuka di depan dijamin tetap *user-friendly* dan sederhana.
- **Performance First**: Mengutamakan teknik *Lazy Loading* (memuat elemen saat di-*scroll*) dan *Pagination* untuk mempercepat *load-time*.

### 2.2 Color Palette (Skema Warna)
- **Primary Color (Medical Blue)**: `#0D6EFD` - Untuk tombol *Call-to-Action* (CTA) dan warna dominan *header*.
- **Emergency / Red Tag (Crimson)**: `#DC3545` - Untuk peringatan kritis, status darurat IHR (PHEIC), atau Triage CDC Merah.
- **Success / Green Tag (Emerald)**: `#28A745` - Untuk status aman, verifikasi Hash (UU ITE), dan grafik metrik ringan.
- **Observation / Yellow Tag (Amber)**: `#FFC107` - Untuk status pasien butuh pemantauan lanjut.
- **Background (Light Gray/White)**: `#F8F9FA` / `#FFFFFF` - Latar belakang utama agar teks rekam medis mudah dibaca (kontras tinggi).

### 2.3 Typography
- Menggunakan *Font* **Inter** atau **Roboto** (Keluarga Sans-Serif) demi keterbacaan (*readability*) angka, dosis obat, dan rekam diagnosis berparagraf panjang, baik pada layar web maupun format cetak PDF.

### 2.4 Layout & Architecture (Wireframing Logic)

**A. Halaman Beranda (Homepage)**
- **Hidden UI Rule**: Sesuai arahan PRD, **TIDAK ADA** formulir register atau struktur navigasi pasien yang terekspos di UI beranda publik.
- Beranda dibuat sangat *clean*, difokuskan pada profil Puskesmas semata (menghindari bypass keamanan).
- Pendaftaran (*Registration*) bersifat tertutup atau dialihkan ke rute rahasia khusus pasien, dokter, dan admin.

**B. Antarmuka KBot AI (Floating Widget)**
- Posisi: Dipertahankan di **Sebelah Kanan Bawah** layar (mengambang / *sticky*).
- Desain *Icon*: Ikon bot bersahabat (kBot).
- UX: Saat di-*klik*, *chatbot* akan menggeser ke atas (pop-up) tanpa meninggalkan halaman utama. Respons AI ditampilkan dengan balasan gaya percakapan (*Conversational UI*) namun dibalik layar memecah `Parameter 1` (Teks/UI) dan `Parameter 2` (Mesin Analisis Kalkulus/Data).

**C. Dasbor Jurnal Visual (Pasien)**
- **Header Section**: Judul Jurnal dengan Indikator *Live System Monitor* di bagian paling atas (Menampilkan *99+ Users* secara *real-time*).
- **Data Visualization Section**: Grid *Responsive* (Flexbox/Bootstrap). Kiri untuk *Pie Chart* tingkat keparahan, Kanan untuk *Line Chart* histori.
- **Action Bar**: Menyediakan tombol ber-UX jelas (warna hijau untuk "Import CSV", warna merah untuk "Cetak Bukti PDF Hukum").

**D. UX Flow Pemantauan Keluarga (Family Feature)**
- Terdapat halaman "Anggota Keluarga" berbentuk sistem kartu (*Card UI*). Kepala keluarga dapat meng-klik kartu anak atau istri, lalu antarmuka menggunakan transisi AJAX untuk menarik data metrik anak tersebut ke layar secara langsung tanpa perpindahan halaman yang mengganggu fokus visual.

---
*Dokumen ini merupakan properti intelektual perancangan platform yang siap disertakan sebagai lampiran spesifikasi teknis (Technical Specs) atau Bab III/IV dalam tugas penelitian metodologi.*
