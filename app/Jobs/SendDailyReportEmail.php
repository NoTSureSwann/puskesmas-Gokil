<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Mail\LaporanHarianMail;
use App\Models\Kunjungan;
use App\Models\Poli;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendDailyReportEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $today = Carbon::today();
        
        $totalVisits = Kunjungan::whereDate('tanggal_kunjungan', $today)->count();
        $visitsCompleted = Kunjungan::whereDate('tanggal_kunjungan', $today)->where('status', 'selesai')->count();
        $visitsCancelled = Kunjungan::whereDate('tanggal_kunjungan', $today)->where('status', 'batal')->count();
        $visitsInProgress = Kunjungan::whereDate('tanggal_kunjungan', $today)
            ->whereIn('status', ['menunggu', 'dipanggil', 'diperiksa', 'resep'])
            ->count();
            
        $visitsUmum = Kunjungan::whereDate('tanggal_kunjungan', $today)->where('jenis_kunjungan', 'umum')->count();
        $visitsBpjs = Kunjungan::whereDate('tanggal_kunjungan', $today)->where('jenis_kunjungan', 'bpjs')->count();
        
        $visitsPerPoli = [];
        $polis = Poli::all();
        foreach ($polis as $poli) {
            $count = Kunjungan::whereDate('tanggal_kunjungan', $today)->where('poli_id', $poli->id)->count();
            $visitsPerPoli[$poli->nama_poli] = $count;
        }
        
        $reportData = [
            'total_visits' => $totalVisits,
            'visits_completed' => $visitsCompleted,
            'visits_in_progress' => $visitsInProgress,
            'visits_cancelled' => $visitsCancelled,
            'visits_umum' => $visitsUmum,
            'visits_bpjs' => $visitsBpjs,
            'visits_per_poli' => $visitsPerPoli,
        ];
        
        $reportDateStr = $today->format('d-m-Y');
        
        // Dapatkan semua email admin yang aktif
        $adminEmails = User::where('role', 'admin')->where('status', 'aktif')->pluck('email')->toArray();
        
        if (!empty($adminEmails)) {
            Mail::to($adminEmails)->send(new LaporanHarianMail($reportDateStr, $reportData));
        }
    }
}
