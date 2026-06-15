<?php

declare(strict_types=1);

namespace App\Http\Controllers\Dokter;

use App\Http\Controllers\Controller;
use App\Models\AiDataset;
use App\Models\AiFeedback;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class MlAnalyticController extends Controller
{
    /**
     * Tampilkan halaman utama ML Analytics Dashboard.
     */
    public function index(): View
    {
        // Ambil data yang low confidence (uncertainty) untuk dilakukan Human-in-the-loop (Active Learning)
        $uncertainData = AiDataset::query()
            ->where('is_synthetic', false)
            ->where('nlp_confidence_score', '<', 0.70)
            ->whereDoesntHave('feedbacks') // Hanya data yang belum direview
            ->latest()
            ->limit(10)
            ->get();

        $stats = [
            'total_datasets' => AiDataset::count(),
            'total_synthetic' => AiDataset::where('is_synthetic', true)->count(),
            'total_feedbacks' => AiFeedback::count(),
            'avg_confidence' => AiDataset::where('is_synthetic', false)->avg('nlp_confidence_score') ?? 0,
        ];

        return view('dokter.ml_analytics', compact('uncertainData', 'stats'));
    }

    /**
     * Endpoint API untuk mengambil data Real-Time Chart.
     */
    public function realtimeData(): JsonResponse
    {
        // Distribusi Confidence Score (Binning)
        $highConf = AiDataset::where('is_synthetic', false)->where('nlp_confidence_score', '>=', 0.8)->count();
        $medConf = AiDataset::where('is_synthetic', false)->whereBetween('nlp_confidence_score', [0.5, 0.79])->count();
        $lowConf = AiDataset::where('is_synthetic', false)->where('nlp_confidence_score', '<', 0.5)->count();

        // Data 7 hari terakhir: Sintetis vs Organik
        $dates = collect();
        $organic = collect();
        $synthetic = collect();

        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $dates->push($date);
            
            $org = AiDataset::where('is_synthetic', false)->whereDate('created_at', $date)->count();
            $syn = AiDataset::where('is_synthetic', true)->whereDate('created_at', $date)->count();
            
            $organic->push($org);
            $synthetic->push($syn);
        }

        return response()->json([
            'confidence' => [
                'labels' => ['High (>80%)', 'Medium (50-79%)', 'Low (<50%)'],
                'data' => [$highConf, $medConf, $lowConf]
            ],
            'trends' => [
                'labels' => $dates,
                'organic' => $organic,
                'synthetic' => $synthetic
            ]
        ]);
    }

    /**
     * Menyimpan RLHF Feedback (Human-in-the-loop).
     */
    public function submitFeedback(Request $request, $id): RedirectResponse
    {
        $request->validate([
            'reward_score' => 'required|numeric|in:-1,0,1',
            'corrected_poli' => 'nullable|string|max:50',
            'notes' => 'nullable|string'
        ]);

        $dataset = AiDataset::findOrFail($id);

        AiFeedback::create([
            'ai_dataset_id' => $dataset->id,
            'user_id' => Auth::id(),
            'reward_score' => $request->reward_score,
            'corrected_poli' => $request->corrected_poli,
            'notes' => $request->notes,
        ]);

        return back()->with('status', 'Feedback RLHF berhasil disimpan. Model akan belajar dari koreksi Anda.');
    }
}
