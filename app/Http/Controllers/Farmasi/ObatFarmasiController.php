<?php

declare(strict_types=1);

namespace App\Http\Controllers\Farmasi;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreObatRequest;
use App\Http\Requests\Admin\UpdateObatRequest;
use App\Models\Obat;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Class ObatFarmasiController
 * Handles Medicine Inventory Management (CRUD) for Pharmacists.
 */
class ObatFarmasiController extends Controller
{
    /**
     * Manajemen Obat (Inventory List).
     */
    public function index(): View
    {
        $obats = Obat::orderBy('nama_obat', 'asc')->paginate(10);
        return view('farmasi.obat.index', compact('obats'));
    }

    /**
     * Tambah Obat Baru.
     */
    public function store(StoreObatRequest $request): RedirectResponse
    {
        Obat::create([
            'kode_obat' => $request->kode_obat,
            'nama_obat' => $request->nama_obat,
            'satuan' => $request->satuan,
            'kategori' => $request->kategori,
            'stok' => $request->stok,
            'stok_minimum' => $request->stok_minimum,
            'harga_satuan' => $request->harga_satuan,
            'deskripsi' => $request->deskripsi,
            'is_aktif' => true,
        ]);

        return redirect()->route('farmasi.obat.index')->with('status', 'Obat berhasil ditambahkan.');
    }

    /**
     * Update Obat.
     */
    public function update(UpdateObatRequest $request, int $id): RedirectResponse
    {
        $obat = Obat::findOrFail($id);

        $obat->update([
            'nama_obat' => $request->nama_obat,
            'satuan' => $request->satuan,
            'kategori' => $request->kategori,
            'stok' => $request->stok,
            'stok_minimum' => $request->stok_minimum,
            'harga_satuan' => $request->harga_satuan,
            'deskripsi' => $request->deskripsi,
        ]);

        return redirect()->route('farmasi.obat.index')->with('status', 'Obat berhasil diperbarui.');
    }

    /**
     * Toggle status aktif Obat.
     */
    public function toggle(int $id): RedirectResponse
    {
        $obat = Obat::findOrFail($id);
        $obat->update(['is_aktif' => !$obat->is_aktif]);

        return back()->with('status', "Status Obat {$obat->nama_obat} diubah.");
    }

    /**
     * Hapus Obat (Soft Delete).
     */
    public function destroy(int $id): RedirectResponse
    {
        $obat = Obat::findOrFail($id);

        // Proteksi: tidak bisa hapus obat yang sudah digunakan dalam resep
        if ($obat->detailResep()->exists()) {
            return back()->with('error', "Obat {$obat->nama_obat} tidak dapat dihapus karena sudah digunakan dalam resep. Silakan nonaktifkan saja.");
        }

        $obat->delete();

        return redirect()->route('farmasi.obat.index')->with('status', "Obat {$obat->nama_obat} berhasil dihapus.");
    }
}
