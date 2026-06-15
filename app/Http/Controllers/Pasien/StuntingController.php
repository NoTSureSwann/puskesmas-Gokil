<?php

namespace App\Http\Controllers\Pasien;

use App\Http\Controllers\Controller;
use App\Services\StuntingCalculatorService;
use Illuminate\Http\Request;

class StuntingController extends Controller
{
    private StuntingCalculatorService $stuntingService;

    public function __construct(StuntingCalculatorService $stuntingService)
    {
        $this->stuntingService = $stuntingService;
    }

    /**
     * Tampilkan halaman kalkulator stunting.
     */
    public function index()
    {
        return view('pasien.stunting');
    }

    /**
     * Hitung hasil (AJAX/JSON)
     */
    public function calculate(Request $request)
    {
        $request->validate([
            'umur_bulan' => ['required', 'integer', 'min:0', 'max:60'],
            'jenis_kelamin' => ['required', 'in:L,P'],
            'tinggi_badan' => ['required', 'numeric', 'min:30', 'max:150'],
        ]);

        $result = $this->stuntingService->calculateZScore(
            (int) $request->umur_bulan,
            $request->jenis_kelamin,
            (float) $request->tinggi_badan
        );

        if (isset($result['error'])) {
            return response()->json(['success' => false, 'message' => $result['error']], 400);
        }

        return response()->json([
            'success' => true,
            'data' => $result
        ]);
    }
}
