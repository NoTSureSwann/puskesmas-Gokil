<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\View\View;

/**
 * Class HomeController
 * Handles the public landing page.
 */
class HomeController extends Controller
{
    /**
     * Show the landing page.
     */
    public function index(): View
    {
        return view('home');
    }

    /**
     * Show the public display for queue announcement.
     */
    public function layarAntrean(): View
    {
        $antrians = \App\Models\Kunjungan::query()
            ->whereDate('tanggal_kunjungan', \Carbon\Carbon::today())
            ->whereIn('status', ['dipanggil', 'diperiksa'])
            ->with('poli')
            ->orderBy('updated_at', 'desc')
            ->take(6)
            ->get();

        return view('antrean.display', compact('antrians'));
    }
}
