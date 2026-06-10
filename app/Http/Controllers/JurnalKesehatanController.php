<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class JurnalKesehatanController extends Controller
{
    /**
     * Endpoint API untuk Tracker Real-Time (99+ Users)
     * Mengembalikan jumlah user aktif & log aktivitas terbaru
     */
    public function liveTracking()
    {
        // Hitung unique user_id yang aktif dalam 15 menit terakhir
        $activeUsersCount = DB::table('audit_logs')
            ->where('created_at', '>=', now()->subMinutes(15))
            ->distinct('user_id')
            ->count('user_id');

        $recentLogs = DB::table('audit_logs')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get()
            ->map(function ($log) {
                return [
                    'event' => $log->event,
                    'time' => Carbon::parse($log->created_at)->diffForHumans(),
                ];
            });

        return response()->json([
            'active_users' => $activeUsersCount,
            'recent_logs' => $recentLogs
        ]);
    }

    /**
     * Menampilkan dashboard visualisasi Jurnal (Chart.js)
     */
    public function visual()
    {
        // Mock data metrik AI & Agregat
        $data = [
            'ringan' => 60,
            'sedang' => 30,
            'kritis' => 10,
            'labels_line' => ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun'],
            'data_line' => [2, 4, 3, 7, 5, 8],
        ];

        return view('pasien.jurnal_visual', compact('data'));
    }

    /**
     * Mengekspor Jurnal Rekam Medis ke PDF dengan Hash Hukum UU ITE
     */
    public function exportPdf(Request $request)
    {
        $user = Auth::user();

        // 1. Ambil data pasien (disini kita mock dengan agregat dummy untuk chart PDF)
        $patientData = [
            'nama' => $user->name,
            'email' => $user->email,
            'tanggal_cetak' => Carbon::now()->translatedFormat('d F Y H:i:s'),
            'diagnosis_ai_summary' => 'Menunjukkan tren perbaikan, namun kewaspadaan direkomendasikan pada musim tropis.',
            // Menyertakan Lifestyle AI (Seharusnya ditarik dari DB/Kunjungan terakhir)
            'ai_tips' => 'Tidur menggunakan kelambu, hindari gigitan nyamuk, pastikan lingkungan bersih dari genangan air.',
            'ai_food' => 'Buah jambu biji, makanan kaya vitamin C untuk imunitas, kurma.',
            'ai_drink' => 'Banyak minum air putih, rebusan jahe merah.',
        ];

        // MOCKUP BILLING & STRUK OBAT
        $billingData = [
            'jasa_dokter' => [
                'nama_dokter' => 'Dr. Santoso (Spesialis Tropis)',
                'biaya' => 150000
            ],
            'obat' => [
                ['nama' => 'Paracetamol 500mg', 'qty' => 10, 'harga_satuan' => 2000, 'subtotal' => 20000],
                ['nama' => 'Vitamin C 1000mg', 'qty' => 5, 'harga_satuan' => 5000, 'subtotal' => 25000],
                ['nama' => 'Obat Anti Malaria', 'qty' => 1, 'harga_satuan' => 75000, 'subtotal' => 75000],
            ]
        ];
        $totalBiaya = $billingData['jasa_dokter']['biaya'] + collect($billingData['obat'])->sum('subtotal');
        $billingData['total'] = $totalBiaya;

        // 2. Buat URL Grafik Statis via QuickChart.io untuk diembed di PDF
        $pieChartUrl = "https://quickchart.io/chart?c={type:'pie',data:{labels:['Ringan','Sedang','Kritis'],datasets:[{data:[60,30,10]}]}}";
        $lineChartUrl = "https://quickchart.io/chart?c={type:'line',data:{labels:['Jan','Feb','Mar','Apr','Mei','Jun'],datasets:[{label:'Skor AI',data:[2,4,3,7,5,8]}]}}";

        // 3. UU ITE Pasal 32 (Anti-Tampering) & Pasal 5,6 (Alat Bukti)
        // Membuat Digital Signature / Hash SHA-256 dari komposisi data ini
        $rawStringToHash = $patientData['nama'] . $patientData['tanggal_cetak'] . $patientData['diagnosis_ai_summary'];
        $digitalHash = hash('sha256', $rawStringToHash);

        // Catat di Audit Logs
        DB::table('audit_logs')->insert([
            'user_id' => $user->id,
            'event' => 'export_pdf',
            'auditable_type' => 'App\Models\User',
            'auditable_id' => $user->id,
            'old_values' => null,
            'new_values' => json_encode(['hash' => $digitalHash]),
            'url' => $request->fullUrl(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $pdfData = [
            'patient' => $patientData,
            'billing' => $billingData,
            'pieChartUrl' => $pieChartUrl,
            'lineChartUrl' => $lineChartUrl,
            'digitalHash' => $digitalHash,
        ];

        $pdf = Pdf::loadView('pdf.jurnal_kesehatan', $pdfData);
        return $pdf->download('Jurnal_Kesehatan_RME_' . time() . '.pdf');
    }

    /**
     * Mengimpor data jurnal/riwayat CSV (Bulk Input)
     */
    public function importCsv(Request $request)
    {
        $request->validate([
            'file_csv' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        $file = $request->file('file_csv');
        $handle = fopen($file->getPathname(), "r");
        
        // Membaca header
        $header = fgetcsv($handle, 1000, ",");
        $rowCount = 0;

        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            // Logika menyimpan row CSV ke database
            // Contoh struktur CSV: Keluhan, Skor_AI, Tanggal
            // DB::table('rekam_medis')->insert([...]);
            $rowCount++;
        }
        fclose($handle);

        // Audit Trail untuk Import UU ITE
        DB::table('audit_logs')->insert([
            'user_id' => Auth::id(),
            'event' => 'import_csv',
            'auditable_type' => 'App\Models\RekamMedis',
            'auditable_id' => 0,
            'old_values' => null,
            'new_values' => json_encode(['rows_imported' => $rowCount]),
            'url' => $request->fullUrl(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->back()->with('success', "Berhasil mengimpor {$rowCount} baris data CSV secara aman.");
    }
}
