<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\WabahGeospasial;
use Illuminate\Http\Request;

class WabahController extends Controller
{
    /**
     * Tampilkan antarmuka peta geospasial
     */
    public function index()
    {
        return view('wabah.peta');
    }

    /**
     * Kembalikan data spasial dalam format JSON (Dikonsumsi oleh Leaflet.js & AI Bot)
     */
    public function getApiData()
    {
        $data = WabahGeospasial::all();
        
        return response()->json([
            'success' => true,
            'message' => 'Data spasial berhasil diambil',
            'data' => $data
        ]);
    }
}
