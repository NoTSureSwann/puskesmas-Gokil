# SI Puskesmas & Klinik (E-Health Enterprise)

Proyek Sistem Informasi Puskesmas dan Klinik yang terintegrasi dengan **AI Engine (Groq Llama-3 & Python Flask)** untuk fitur triase otomatis, prediksi antrian, dan cek interaksi obat.

### Environment & Versi Sistem
Proyek ini dikembangkan dan berjalan stabil dengan spesifikasi lokal (Laragon) berikut:
- **Framework:** Laravel v11.54.0
- **PHP:** v8.4.12
- **Database:** MySQL v8.4.3 (Community Server)
- **Local Web Server:** Laragon Full

## Persyaratan Sistem
Pastikan Anda telah menginstal perangkat lunak berikut di komputer lokal Anda:
- **PHP** >= 8.2 (Disarankan menggunakan [Laragon](https://laragon.org/) untuk Windows)
- **Composer**
- **Node.js** & **npm**
- **MySQL** (Termasuk dalam Laragon/XAMPP)
- **Python** >= 3.10
- **Git**

---

## Langkah-langkah Instalasi (Untuk Dosen / Penguji)

### 1. Clone Repositori atau Download ZIP
Buka terminal (Git Bash / PowerShell / Command Prompt) dan jalankan perintah clone berikut:
```bash
git clone https://github.com/NoTSureSwann/puskesmas-Gokil.git
cd puskesmas-Gokil
```
*(Alternatif: Anda juga bisa men-download *repository* ini sebagai file ZIP dari GitHub, lalu ekstrak ke dalam folder C:\laragon\www dan masuk ke dalam folder tersebut melalui terminal).*

### 2. Instalasi Dependensi Laravel (Backend & Frontend)
Instal semua package PHP dan JavaScript yang dibutuhkan:
```bash
composer install
npm install
npm run build
```

### 3. Konfigurasi Environment (`.env`)
Salin file `.env.example` menjadi `.env`:
```bash
cp .env.example .env
```
*(Di Windows, Anda juga bisa melakukan copy-paste file secara manual via File Explorer dan me-rename menjadi `.env`)*

Buka file `.env` di text editor (VS Code, Notepad, dll.) dan sesuaikan koneksi database Anda:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=puskesmas_johar_baru  # Pastikan Anda telah membuat database kosong bernama 'puskesmas_johar_baru' di phpMyAdmin/HeidiSQL
DB_USERNAME=root
DB_PASSWORD=
```

**Konfigurasi Tambahan untuk AI Engine:**
Tambahkan konfigurasi AI di bagian bawah file `.env`:
```env
GROQ_API_KEY=masukkan_api_key_groq_anda_disini
GROQ_MODEL=llama-3.3-70b-versatile

AI_ENGINE_URL=http://127.0.0.1:5000
AI_ENGINE_SECRET=metopen-ai-secret-2026
```
*(Catatan: Dapatkan Groq API Key secara gratis di [console.groq.com](https://console.groq.com))*

### 4. Generate Key & Migrasi Database (Wajib)
Jalankan perintah berikut untuk mengenerate application key, membuat tabel database, dan mengisi data awal (dummy data/akun):
```bash
php artisan key:generate
php artisan migrate:fresh --seed
```
> **⚠️ PENTING: Mengapa langkah ini wajib?**
> Perintah `--seed` sangat krusial untuk dijalankan karena sistem akan men-generate data akun (Admin, Dokter, Farmasi) beserta _password hash_ yang sudah tervalidasi. Jika Anda tidak melakukan *seed* atau membuat akun secara manual dari phpMyAdmin, **Anda akan mengalami Credential Error (Gagal Login)** karena password tidak terenkripsi dengan standar keamanan Laravel (Bcrypt).


### 5. Instalasi Python AI Engine (Sidecar)
Buka terminal baru (biarkan terminal Laravel tetap ada), arahkan ke folder `ai_engine`, dan buat Virtual Environment:
```bash
cd ai_engine
python -m venv venv
```
Aktifkan Virtual Environment:
- **Windows:** `venv\Scripts\activate`
- **Mac/Linux:** `source venv/bin/activate`

Instal library Python yang dibutuhkan:
```bash
pip install -r requirements.txt
```

---

## Cara Menjalankan Aplikasi Lokal

Anda perlu menjalankan dua server secara bersamaan (buka 2 jendela terminal):

### Terminal 1: Menjalankan Laravel
Pastikan Anda berada di root folder proyek, lalu jalankan:
```bash
php artisan serve
```
Aplikasi Laravel akan berjalan di `http://localhost:8000`.

### Terminal 2: Menjalankan Python AI Engine
Pastikan Anda berada di dalam folder `ai_engine` dan virtual environment (`venv`) dalam keadaan aktif.
Atur variabel environment rahasia agar sama dengan Laravel, lalu jalankan server:

**Windows (PowerShell):**
```powershell
$env:AI_ENGINE_SECRET="metopen-ai-secret-2026"
python api_server.py
```
**Windows (CMD):**
```cmd
set AI_ENGINE_SECRET=metopen-ai-secret-2026
python api_server.py
```
**Mac/Linux:**
```bash
export AI_ENGINE_SECRET="metopen-ai-secret-2026"
python api_server.py
```
Server AI Python akan berjalan di `http://127.0.0.1:5000`.

---

## Kredensial Login Default
Setelah melakukan `php artisan migrate:fresh --seed`, gunakan akun berikut untuk mencoba sistem:

- **Admin / Resepsionis:** `admin@puskesmas.com` (Pass: `password`)
- **Dokter:** `dokter@puskesmas.com` (Pass: `password`)
- **Farmasi / Apoteker:** `farmasi@puskesmas.com` (Pass: `password`)
- **Pasien:** (Sistem men-generate pasien secara dinamis, cek database tabel `users` untuk email pasien).

## Troubleshooting Umum
- **Gagal Migrasi / SQL Error:** Pastikan MySQL (Laragon/XAMPP) sudah berjalan dan database `puskesmas_johar_baru` sudah dibuat sebelum menjalankan migrate.
- **AI / KBot Error 500:** Pastikan server Python Flask (Terminal 2) sedang berjalan dan API Key Groq di `.env` valid.
- **Tampilan CSS/JS hancur:** Pastikan Anda telah menjalankan `npm run build` untuk mengkompilasi aset Frontend (Vite).
