<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PanggilanAmbulans;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminAmbulansController extends Controller
{
    /**
     * Tampilkan daftar panggilan ambulans.
     */
    public function index(): View
    {
        $panggilan = PanggilanAmbulans::with('pasien.user')
            ->orderByRaw("FIELD(status, 'menunggu', 'dijemput', 'selesai', 'batal')")
            ->latest()
            ->paginate(15);

        return view('admin.ambulans.index', compact('panggilan'));
    }

    /**
     * Update status panggilan ambulans.
     */
    public function updateStatus(Request $request, int|string $id)
    {
        $request->validate([
            'status' => ['required', 'in:menunggu,dijemput,selesai,batal'],
        ]);

        $panggilan = PanggilanAmbulans::findOrFail($id);
        $panggilan->update(['status' => $request->status]);

        return back()->with('status', 'Status pemanggilan ambulans berhasil diperbarui.');
    }
}
