# Panduan Mahasiswa: Eksplorasi & Instalasi Menyeluruh (Local Environment)

Selamat datang di repositori proyek **Sistem Informasi Puskesmas & Klinik (E-Health Enterprise)**! Panduan ini dirancang khusus bagi mahasiswa yang ingin menginstal, mempelajari arsitektur, dan ikut mengembangkan proyek ini secara lokal di komputer masing-masing.

## 🚀 Apa yang Ada di Dalam Proyek Ini?
Proyek ini mengadopsi arsitektur **Microservices (Dual Engine)**:
1. **Laravel Backend & Frontend (PHP & JS):** Mengurus manajemen database, autentikasi, UI/UX dengan Blade/Tailwind, serta API Routing.
2. **Python AI Sidecar Engine (Flask):** Menangani pemrosesan AI menggunakan API LLM Groq (Llama-3), seperti KBot Triase dan pengecekan interaksi obat.

---

## 💻 Kebutuhan Perangkat Lunak (Prerequisites)

Sebelum mulai, pastikan komputer/laptop kamu sudah terinstal:
- **Laragon atau XAMPP** (Sangat disarankan Laragon di Windows untuk environment yang lebih ringan). Versi PHP minimal 8.2, disarankan 8.4.
- **Composer** (Untuk menginstal package Laravel). Download di: [getcomposer.org](https://getcomposer.org/).
- **Node.js & NPM** (Untuk kompilasi frontend Vite). Download versi LTS di: [nodejs.org](https://nodejs.org/).
- **Python 3.10+** (Wajib terinstal dan terdaftar di `PATH` environment variables). Download di: [python.org](https://www.python.org/).
- **Git** (Untuk clone repositori). Download di: [git-scm.com](https://git-scm.com/).
- **Visual Studio Code (VS Code)** (Code editor yang disarankan).

---

## 🛠️ Langkah-Langkah Instalasi Menyeluruh

### 1. Clone Repositori
Buka terminal pilihanmu (di VS Code, Git Bash, atau Terminal biasa), lalu navigasikan ke folder server lokal (misal: `C:\laragon\www` atau `C:\xampp\htdocs`).
```bash
git clone https://github.com/NoTSureSwann/puskesmas-Gokil.git
cd puskesmas-Gokil
```

### 2. Setup Framework Laravel
Di dalam folder proyek, unduh semua library PHP yang dibutuhkan oleh Laravel:
```bash
composer install
```
Kemudian unduh package JavaScript untuk frontend:
```bash
npm install
```
Kompilasi aset UI menggunakan Vite:
```bash
npm run build
```

### 3. Konfigurasi Environment (`.env`)
Laravel memerlukan file konfigurasi untuk koneksi database dan kredensial eksternal.
- Gandakan file konfigurasi bawaan:
  ```bash
  cp .env.example .env
  ```
  *(Jika `cp` tidak jalan, copy file manual di File Explorer, ubah namanya jadi `.env`)*
- Generate kunci aplikasi:
  ```bash
  php artisan key:generate
  ```

### 4. Setup Database
- Buka aplikasi **Laragon** atau **XAMPP**, lalu klik **Start All** (Nyalakan Apache & MySQL).
- Buka database manager (HeidiSQL, phpMyAdmin, atau DBeaver).
- Buat sebuah database baru dan beri nama: `puskesmas_johar_baru`
- Buka file `.env` di VS Code, sesuaikan konfigurasi berikut:
  ```env
  DB_CONNECTION=mysql
  DB_HOST=127.0.0.1
  DB_PORT=3306
  DB_DATABASE=puskesmas_johar_baru
  DB_USERNAME=root
  DB_PASSWORD=
  ```

### 5. Setup Kunci API Groq (AI Engine)
Kamu membutuhkan kunci API dari Groq untuk menjalankan fitur AI.
- Daftar gratis di [Groq Console](https://console.groq.com).
- Buat API Key baru.
- Tambahkan kode berikut di baris paling bawah file `.env`:
  ```env
  GROQ_API_KEY=masukkan_api_key_groq_anda_disini
  GROQ_MODEL=llama-3.3-70b-versatile
  
  AI_ENGINE_URL=http://127.0.0.1:5000
  AI_ENGINE_SECRET=metopen-ai-secret-2026
  ```

### 6. Migrasi & Seeding
Untuk mengisi tabel database dengan struktur yang benar beserta data dummy, jalankan:
```bash
php artisan migrate:fresh --seed
```
*Pastikan tidak ada error merah di langkah ini. Jika ada, biasanya karena database `puskesmas_johar_baru` belum dibuat atau MySQL belum menyala.*

### 7. Setup Mesin AI (Python)
Buka terminal baru di VS Code, masuk ke folder AI Engine:
```bash
cd ai_engine
```
Buat isolasi virtual environment agar library Python tidak bentrok dengan project lain:
```bash
python -m venv venv
```
Aktifkan environment-nya:
- **Windows (Command Prompt):** `venv\Scripts\activate.bat`
- **Windows (PowerShell):** `.\venv\Scripts\Activate.ps1`
- **Linux/Mac:** `source venv/bin/activate`

Instal library AI yang dibutuhkan:
```bash
pip install -r requirements.txt
```

---

## 🚀 Cara Menjalankan Proyek secara Bersamaan

Karena kita memiliki dua sistem (PHP dan Python), kita butuh dua terminal yang hidup secara paralel.

### Terminal 1: Laravel Server
Buka terminal baru, pastikan di folder utama `puskesmas-Gokil`:
```bash
php artisan serve
```
👉 Server web akan menyala di: **http://127.0.0.1:8000**

### Terminal 2: AI Engine Server
Buka terminal yang sudah berada di folder `ai_engine` dengan environment aktif `(venv)`:
**Jika Command Prompt (CMD):**
```cmd
set AI_ENGINE_SECRET=metopen-ai-secret-2026
python api_server.py
```
**Jika PowerShell:**
```powershell
$env:AI_ENGINE_SECRET="metopen-ai-secret-2026"
python api_server.py
```
👉 Server AI akan menyala di: **http://127.0.0.1:5000**

---

## 🎯 Mulai Eksplorasi!

Akses **http://127.0.0.1:8000** di browser. Coba bereksperimen dengan menggunakan akun-akun ini:
- **Resepsionis/Admin:** `admin@puskesmas.com` (Pass: `password`)
- **Dokter:** `dokter@puskesmas.com` (Pass: `password`)
- **Farmasi:** `farmasi@puskesmas.com` (Pass: `password`)

**Tips Eksplorasi Mahasiswa:**
- Cobalah memodifikasi prompt AI di dalam `app/Http/Controllers/Api/AiController.php` (jika ada) atau di file Python `ai_engine/api_server.py`.
- Jika kamu ingin mencoba fitur scan obat via handphone, silakan pelajari `SCANNER_APP_GUIDE.md` yang menggunakan `ngrok` untuk mengekspos localhost ke internet.
- Struktur UI menggunakan Blade templating. File-file view ada di `resources/views/`. Kamu bebas mengubah warna, layout, dan struktur HTML di sana.
