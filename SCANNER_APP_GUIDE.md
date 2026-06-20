# Panduan Pembuatan Aplikasi Barcode Scanner (Next.js + Ionic)

Aplikasi ini akan bertindak sebagai *Frontend Terpisah* (Client App) yang dirancang khusus untuk mobile/PWA. Pengguna/Pasien dapat menggunakan kamera HP untuk melakukan scan QR Code/Barcode, atau memasukkan kode secara manual, untuk melihat nomor antrian dan hasil analisis triase AI.

Aplikasi ini akan di-deploy ke **Vercel** dan berkomunikasi dengan **Laravel API** yang ada di server backend.

## 🏗️ Arsitektur Sistem
1. **Frontend:** Next.js (React) dipadukan dengan UI dari Ionic Framework (`@ionic/react`) agar tampilannya terasa seperti aplikasi native mobile (Android/iOS).
2. **Scanner:** Menggunakan library `html5-qrcode` yang sangat stabil untuk membaca barcode/QR melalui web browser kamera tanpa perlu *native build* (sangat cocok untuk Vercel PWA).
3. **Backend:** Laravel 11 Anda, di mana telah dibuka endpoint API khusus untuk melayani pengecekan tiket kunjungan. Berikut adalah implementasi *Route* dan *Controller*-nya agar *agents* atau *developer* dapat menyesuaikan:
   
   **A. Route (`routes/api.php`)**
   ```php
   Route::get('/kunjungan/{kode}', [\App\Http\Controllers\Api\ScannerApiController::class, 'cekAntrian']);
   ```
   
   **B. Controller (`app/Http/Controllers/Api/ScannerApiController.php`)**
   ```php
   <?php
   namespace App\Http\Controllers\Api;

   use App\Http\Controllers\Controller;
   use App\Models\Kunjungan;
   use Illuminate\Http\JsonResponse;

   class ScannerApiController extends Controller
   {
       public function cekAntrian(string $kode): JsonResponse
       {
           // Cari data kunjungan beserta relasi pasien dan poli
           $kunjungan = Kunjungan::with(['pasien.user', 'poli'])->where('no_kunjungan', $kode)->first();

           if (!$kunjungan) {
               return response()->json([
                   'status' => 'error',
                   'message' => 'Data kunjungan tidak ditemukan. Pastikan kode barcode benar.'
               ], 404);
           }

           // Tentukan pesan analisis AI (Triase) dari keluhan
           $aiAnalysis = 'Pasien belum melakukan skrining AI (KBot).';
           if (!empty($kunjungan->keluhan)) {
               $aiAnalysis = "Keluhan Utama: " . $kunjungan->keluhan;
           }

           return response()->json([
               'status' => 'success',
               'data' => [
                   'no_kunjungan' => $kunjungan->no_kunjungan,
                   'no_antrian' => str_pad((string)$kunjungan->no_antrian, 3, '0', STR_PAD_LEFT),
                   'nama_pasien' => $kunjungan->pasien->user->name ?? 'Anonim',
                   'poli' => $kunjungan->poli->nama_poli ?? 'Belum Ditentukan',
                   'status_kunjungan' => ucfirst($kunjungan->status),
                   'ai_analysis' => $aiAnalysis,
               ]
           ]);
       }
   }
   ```
4. **Hosting:** Vercel (Gratis dan auto-deploy dari GitHub).

---

## 🛠️ Step-by-Step Pembuatan Aplikasi

### 1. Inisialisasi Proyek Next.js
Buka terminal baru (di luar folder Laravel Anda, misal di `C:\laragon\www`), lalu jalankan:
```bash
npx create-next-app@latest puskesmas-scanner
```
*(Saat ditanya: gunakan TypeScript (Yes), ESLint (Yes), Tailwind (Yes), `src/` directory (Yes), App Router (Yes))*

Masuk ke folder proyek:
```bash
cd puskesmas-scanner
```

### 2. Install Ionic & Dependencies
Instal framework Ionic untuk UI mobile dan library scanner kamera:
```bash
npm install @ionic/react @ionic/react-router ionicons
npm install html5-qrcode
npm install axios
```

### 3. Konfigurasi Ionic di Next.js
Karena Ionic dirancang untuk *Client-Side Rendering (CSR)*, Anda perlu membuat file komponen client. 
Buat file `src/components/ScannerApp.tsx`:

```tsx
'use client';
import { useState, useEffect } from 'react';
import { Html5QrcodeScanner } from 'html5-qrcode';
import axios from 'axios';

export default function ScannerApp() {
  const [kode, setKode] = useState('');
  const [dataPasien, setDataPasien] = useState<any>(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');

  // Setup Scanner
  useEffect(() => {
    const scanner = new Html5QrcodeScanner(
      "reader", { fps: 10, qrbox: { width: 250, height: 250 } }, false
    );
    
    scanner.render((decodedText) => {
      setKode(decodedText);
      scanner.clear(); // Hentikan kamera setelah berhasil scan
      cekDataAPI(decodedText);
    }, (err) => {
      // ignore scanning errors
    });

    return () => scanner.clear(); // Cleanup
  }, []);

  const cekDataAPI = async (kodeScan: string) => {
    setLoading(true);
    setError('');
    try {
      // Membaca URL API dari environment variables Next.js
      const baseUrl = process.env.NEXT_PUBLIC_API_URL || 'http://localhost:8000/api';
      const res = await axios.get(`${baseUrl}/kunjungan/${kodeScan}`);
      
      // Karena API Laravel kita mengembalikan: { status: 'success', data: { ... } }
      if (res.data.status === 'success') {
        setDataPasien(res.data.data);
      } else {
        throw new Error(res.data.message);
      }
    } catch (err: any) {
      setError(err.response?.data?.message || 'Data tidak ditemukan atau kode tidak valid.');
    }
    setLoading(false);
  };

  return (
    <div className="p-4 max-w-md mx-auto">
      <h1 className="text-2xl font-bold mb-4 text-center">Scan Antrian & Triase</h1>
      
      {/* Kamera Scanner */}
      <div id="reader" className="mb-4 bg-white rounded-lg shadow overflow-hidden"></div>

      {/* Input Manual Fallback */}
      <div className="flex gap-2 mb-4">
        <input 
          type="text" 
          placeholder="Atau ketik kode manual..." 
          className="border p-2 rounded w-full text-black"
          value={kode}
          onChange={(e) => setKode(e.target.value)}
        />
        <button 
          onClick={() => cekDataAPI(kode)}
          className="bg-blue-600 text-white px-4 py-2 rounded"
        >
          Cari
        </button>
      </div>

      {loading && <p className="text-center text-blue-500">Mencari data...</p>}
      {error && <p className="text-center text-red-500">{error}</p>}

      {/* Hasil Data API */}
      {dataPasien && (
        <div className="bg-green-50 p-4 rounded-lg shadow border border-green-200 mt-4 text-black">
          <h2 className="font-bold text-lg mb-2">Hasil Ditemukan!</h2>
          <p><strong>Nama Pasien:</strong> {dataPasien.nama_pasien}</p>
          <p><strong>Nomor Antrian:</strong> {dataPasien.no_antrian}</p>
          <p><strong>Poli Tujuan:</strong> {dataPasien.poli}</p>
          <p><strong>Status:</strong> {dataPasien.status_kunjungan}</p>
          <div className="mt-2 pt-2 border-t border-gray-300">
            <p className="text-sm font-semibold text-gray-700">Analisis Triase / Keluhan Utama:</p>
            <p className="text-sm">{dataPasien.ai_analysis}</p>
          </div>
        </div>
      )}
    </div>
  );
}
```

Panggil komponen ini di `src/app/page.tsx`:
```tsx
import ScannerApp from '@/components/ScannerApp';

export default function Home() {
  return (
    <main className="min-h-screen bg-gray-100 text-black py-8">
      <ScannerApp />
    </main>
  );
}
```

---

## 🚀 Langkah Deploy ke Vercel

1. **Push ke GitHub:**
   Buat repositori baru di GitHub (misal: `puskesmas-scanner-app`), lalu jalankan:
   ```bash
   git add .
   git commit -m "Initial commit Next.js Scanner"
   git branch -M main
   git remote add origin https://github.com/UsernameAnda/puskesmas-scanner-app.git
   git push -u origin main
   ```

2. **Deploy di Vercel:**
   - Login ke [Vercel.com](https://vercel.com).
   - Klik **"Add New..." > "Project"**.
   - Pilih repositori `puskesmas-scanner-app` yang baru saja Anda push.
   - Vercel akan otomatis mendeteksi bahwa ini adalah proyek Next.js.
   - Di bagian **Environment Variables**, tambahkan:
     - Name: `NEXT_PUBLIC_API_URL`
     - Value: `https://URL_LARAVEL_ANDA_DI_INTERNET.com/api`
   - Klik **Deploy**.

---

## 📱 Pengujian Lokal via Smartphone (Menggunakan Ngrok)

Sangat penting diketahui: **Browser di smartphone mewajibkan koneksi HTTPS untuk mengakses kamera**. Jika Anda menjalankan aplikasi Scanner ini hanya di `http://localhost:3000` atau via IP Lokal, kamera kemungkinan besar akan diblokir oleh browser HP Anda.

Oleh karena itu, untuk menguji Scanner App di HP secara lokal sebelum di-deploy ke Vercel, sangat disarankan menggunakan **Ngrok**:

1. **Install Ngrok:** Download dari [ngrok.com](https://ngrok.com/) (atau via `npm install -g ngrok`).
2. **Jalankan Aplikasi Next.js:** Di terminal proyek Next.js Anda jalankan `npm run dev` (biasanya berjalan di port 3000).
3. **Ekspos Port via Ngrok:** Buka terminal baru dan jalankan:
   ```bash
   ngrok http 3000
   ```
4. **Buka di HP:** Ngrok akan memberikan URL *Forwarding* berawalan `https://` (misal: `https://a1b2-c3.ngrok-free.app`). Buka URL tersebut di browser smartphone Anda. Kamera sekarang diizinkan untuk berfungsi!

> **💡 Tips Tambahan:** Agar HP Anda juga bisa "ngobrol" dengan API Laravel di laptop Anda, Anda juga perlu mengekspos port 8000.  
> Jalankan `ngrok http 8000` di terminal lain, lalu copy URL `https://...` milik Laravel tersebut dan paste ke dalam `.env` milik Next.js sebagai `NEXT_PUBLIC_API_URL`.

---

## 🔗 API Backend (Laravel) Sudah Siap!
Kabar baik! Konfigurasi API dan CORS di sisi Laravel **sudah selesai dibuat** pada sesi sebelumnya.

Endpoint API Anda sudah aktif di:  
`GET http://localhost:8000/api/kunjungan/{kode}`

Pastikan untuk mengisi variabel environment `NEXT_PUBLIC_API_URL` di Vercel dengan URL Production dari server Laravel Anda nanti (misalnya `https://api.puskesmas.com/api`). Untuk saat pengembangan lokal, script di atas sudah mengarah otomatis ke `http://localhost:8000/api`.
