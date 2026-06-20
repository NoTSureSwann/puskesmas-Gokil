# Panduan Dosen: Evaluasi Proyek Metopen (Local Environment)

Dokumen ini berisi panduan langkah demi langkah untuk menginstal, menjalankan, dan mengevaluasi **Sistem Informasi Puskesmas & Klinik (E-Health Enterprise)** berserta integrasi **AI Engine (Groq Llama-3)** dan **Scanner Module** di komputer lokal.

## 📌 Poin Evaluasi Utama (Fitur Unggulan)

Untuk mempermudah proses penilaian, berikut adalah fitur utama yang dapat langsung diuji:

1. **Modul KBot Triase (AI)**: Login sebagai Admin/Dokter, coba masukkan keluhan pasien, AI akan menentukan prioritas (Triase) dan rekomendasi poli.
2. **Sistem Pengecekan Interaksi Obat (AI)**: Login sebagai Farmasi, gunakan fitur ini untuk memastikan 2 obat atau lebih tidak memiliki interaksi berbahaya.
3. **Pindai Obat (Scanner)**: Modul untuk memindai Barcode/QR obat menggunakan kamera (lihat `SCANNER_APP_GUIDE.md` untuk pengujian via smartphone).

## 🔑 Kredensial Akses (Wajib Seeding)

Gunakan kredensial berikut untuk login dan menguji hak akses masing-masing peran:
- **Admin/Resepsionis:** `admin@puskesmas.com` (Pass: `password`)
- **Dokter:** `dokter@puskesmas.com` (Pass: `password`)
- **Farmasi:** `farmasi@puskesmas.com` (Pass: `password`)

---

### Environment & Versi Sistem
Proyek ini dikembangkan dan berjalan stabil dengan spesifikasi lokal (Laragon) berikut:
- **Framework:** Laravel v11.54.0
- **PHP:** v8.2+
- **Database:** MySQL v8.4+
- **Local Web Server:** Laragon Full / XAMPP

---

## Tahap 1: Persiapan Sistem
Pastikan perangkat lunak berikut sudah terinstal di komputer Bapak/Ibu:
1. **Laragon / XAMPP** (Termasuk PHP 8.2+ dan MySQL)
2. **Composer** (Package manager untuk PHP)
3. **Node.js & npm** (Package manager untuk JavaScript/Frontend)
4. **Python** (Versi 3.10 atau lebih baru)
5. **Git**

---

## Tahap 2: Clone Repositori
Buka terminal (Git Bash, Command Prompt, atau PowerShell), arahkan ke folder web root Anda (misal: `C:\laragon\www`), lalu jalankan:

```bash
git clone https://github.com/NoTSureSwann/puskesmas-Gokil.git
cd puskesmas-Gokil
```

---

## Tahap 3: Instalasi Dependensi Backend & Frontend (Laravel)

Di dalam folder `puskesmas-Gokil`, jalankan perintah berikut secara berurutan untuk menginstal library PHP dan kompilasi aset CSS/JS:

```bash
# Menginstal library PHP
composer install

# Menginstal package Frontend
npm install

# Mengkompilasi aset Frontend (Vite)
npm run build
```

---

## Tahap 4: Konfigurasi Database dan Environment

1. Buka aplikasi Laragon/XAMPP dan **Start All** (pastikan Apache/Nginx dan MySQL berjalan).
2. Buka HeidiSQL / phpMyAdmin, lalu **buat database kosong** dengan nama: `puskesmas_johar_baru`
3. Kembali ke terminal, copy template konfigurasi environment:

```bash
cp .env.example .env
```
*(Catatan: Jika error menggunakan perintah cp di CMD Windows, silakan copy file `.env.example` secara manual lewat File Explorer dan ubah namanya menjadi `.env`)*

4. Buka file `.env` di code editor (seperti VS Code), cari baris konfigurasi database dan pastikan sesuai:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=puskesmas_johar_baru
DB_USERNAME=root
DB_PASSWORD=
```

5. Scroll ke bagian paling bawah file `.env`, lalu **tambahkan konfigurasi AI Engine** berikut:
```env
# Integrasi Groq Llama-3 AI
GROQ_API_KEY=masukkan_api_key_groq_anda_disini
GROQ_MODEL=llama-3.3-70b-versatile

# Koneksi ke Sidecar Python
AI_ENGINE_URL=http://127.0.0.1:5000
AI_ENGINE_SECRET=metopen-ai-secret-2026
```
*(Catatan: API Key Groq bisa menggunakan milik tim mahasiswa yang sudah disiapkan sebelumnya, atau generate baru di https://console.groq.com)*

6. Di terminal, jalankan perintah untuk generate kunci keamanan aplikasi:
```bash
php artisan key:generate
```

---

## Tahap 5: Migrasi Database & Seeding (Wajib)

Jalankan perintah berikut untuk membuat semua tabel database dan mengisi data dummy (akun admin, dokter, dll):

```bash
php artisan migrate:fresh --seed
```

> **⚠️ PENTING: Jangan Melewati Langkah Seeding!**  
> Perintah `--seed` wajib dijalankan karena sistem akan men-generate data akun (Admin, Dokter, Farmasi) beserta _password hash_ yang sudah tervalidasi. Jika Anda melewati langkah ini, Anda akan kesulitan login.

---

## Tahap 6: Instalasi Python AI Engine

1. Di terminal yang sama, masuk ke folder AI Engine:
```bash
cd ai_engine
```

2. Buat Virtual Environment Python:
```bash
python -m venv venv
```

3. Aktifkan Virtual Environment:
   - **CMD Windows:** `venv\Scripts\activate.bat`
   - **PowerShell:** `.\venv\Scripts\Activate.ps1`
   - **Mac/Linux:** `source venv/bin/activate`

4. Instal library Machine Learning:
```bash
pip install -r requirements.txt
```

---

## Tahap 7: Menjalankan Aplikasi (Mulai)

Untuk mengevaluasi sistem secara penuh, Anda wajib membuka **2 jendela terminal yang berbeda**.

### Terminal 1: Menjalankan Backend Laravel
Pastikan Anda berada di root folder `puskesmas-Gokil`, lalu jalankan:
```bash
php artisan serve
```
*(Sistem utama berjalan di: http://127.0.0.1:8000)*

### Terminal 2: Menjalankan AI Engine Python
Arahkan ke folder `ai_engine`, pastikan virtual environment aktif `(venv)`, lalu jalankan server Flask:

**Jika menggunakan Command Prompt (CMD):**
```cmd
set AI_ENGINE_SECRET=metopen-ai-secret-2026
python api_server.py
```
**Jika menggunakan PowerShell:**
```powershell
$env:AI_ENGINE_SECRET="metopen-ai-secret-2026"
python api_server.py
```
*(AI Engine berjalan di: http://127.0.0.1:5000)*

---

## Tahap 8: Mulai Penilaian
1. Buka browser dan akses **http://127.0.0.1:8000**
2. Silakan login dengan akun yang disediakan pada bagian atas dokumen ini.
3. Lakukan pengujian pada modul-modul prioritas (KBot Triase, Pengecekan Interaksi Obat, Scanner) yang menjadi fokus penelitian ini.
