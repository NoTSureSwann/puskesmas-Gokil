<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\DetailResep;
use App\Models\Kunjungan;
use App\Models\Obat;
use App\Models\Poli;
use App\Models\ProfilDokter;
use App\Models\ProfilPasien;
use App\Models\Resep;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RecursiveDataValidationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test N=1 Recursive Data Generation and Matching Validation.
     * Menguji pembuatan 1 alur utuh data (Poli -> Dokter/Pasien -> Kunjungan -> Resep -> DetailResep)
     * dan memastikan relasi rekursif/berantai (nested relations) tersebut matched.
     */
    public function test_recursive_data_n_equals_1_matched(): void
    {
        // 1. Setup Poli
        $poli = Poli::create([
            'nama_poli' => 'Poli Dummy N1',
            'kode_poli' => 'PL-N1',
            'deskripsi' => 'Poli khusus testing N=1',
        ]);

        // 2. Setup User & Profil Dokter
        $userDokter = User::create([
            'name' => 'Dr. Test N1',
            'email' => 'dr.test.n1@metopen.local',
            'password' => Hash::make('password'),
            'role' => 'dokter',
            'phone' => '08110000001',
            'status' => 'aktif',
        ]);
        $dokter = ProfilDokter::create([
            'user_id' => $userDokter->id,
            'nip' => '123456789012345678',
            'sip' => 'SIP/TEST/N1',
            'spesialisasi' => 'Spesialis Test',
            'poli' => $poli->nama_poli,
            'harga_konsultasi' => 100000,
            'jam_kerja' => '08:00 - 15:00',
        ]);

        // 3. Setup User & Profil Pasien
        $userPasien = User::create([
            'name' => 'Pasien Test N1',
            'email' => 'pasien.test.n1@metopen.local',
            'password' => Hash::make('password'),
            'role' => 'pasien',
            'phone' => '08110000002',
            'status' => 'aktif',
        ]);
        $pasien = ProfilPasien::create([
            'user_id' => $userPasien->id,
            'nik' => '3171000000000001',
            'jenis_kelamin' => 'L',
            'tanggal_lahir' => '1990-01-01',
            'tempat_lahir' => 'Jakarta',
            'alamat' => 'Jl. Test N1',
            'jenis_pasien' => 'umum',
        ]);

        // 4. Setup Kunjungan
        $kunjungan = Kunjungan::create([
            'pasien_id' => $pasien->id,
            'poli_id' => $poli->id,
            'dokter_id' => $dokter->id,
            'no_kunjungan' => 'KJ-TEST-N1',
            'no_antrian' => 1,
            'tanggal_kunjungan' => now()->toDateString(),
            'status' => 'diperiksa',
            'jenis_kunjungan' => 'baru',
        ]);

        // 5. Setup Obat
        $obat = Obat::create([
            'kode_obat' => 'OBT-N1',
            'nama_obat' => 'Obat Test N1',
            'jenis_obat' => 'Tablet',
            'kategori' => 'Bebas',
            'stok' => 100,
            'satuan' => 'Strip',
            'harga_beli' => 10000,
            'harga_jual' => 15000,
        ]);

        // 6. Setup Resep
        $resep = Resep::create([
            'kunjungan_id' => $kunjungan->id,
            'dokter_id' => $dokter->id,
            'no_resep' => 'RSP-TEST-N1',
            'status' => 'menunggu',
            'prioritas' => 'normal',
            'jam_input_resep' => now(),
        ]);

        // 7. Setup Detail Resep (N=1)
        $detailResep = DetailResep::create([
            'resep_id' => $resep->id,
            'obat_id' => $obat->id,
            'jumlah' => 2,
            'dosis' => '3x1',
            'aturan_pakai' => 'Sesudah makan',
            'harga_satuan' => 15000,
            'subtotal' => 30000,
        ]);

        // Validasi N=1 Rekursif (Matching)
        // Kita fetch ulang dari DB untuk mensimulasikan query sebenarnya (menghindari cache in-memory object saat creation)
        $fetchedDetail = DetailResep::with([
            'resep.kunjungan.pasien.user',
            'resep.kunjungan.dokter.user',
            'resep.kunjungan.poli',
            'obat'
        ])->find($detailResep->id);

        // Assert rekursi N=1 Matching: DetailResep -> Resep -> Kunjungan -> Pasien -> User
        $this->assertNotNull($fetchedDetail);
        $this->assertEquals($resep->id, $fetchedDetail->resep->id);
        $this->assertEquals($kunjungan->id, $fetchedDetail->resep->kunjungan->id);
        
        // Assert Pasien Matching
        $this->assertEquals($pasien->id, $fetchedDetail->resep->kunjungan->pasien->id);
        $this->assertEquals($userPasien->id, $fetchedDetail->resep->kunjungan->pasien->user->id);
        $this->assertEquals('Pasien Test N1', $fetchedDetail->resep->kunjungan->pasien->user->name);

        // Assert Dokter Matching
        $this->assertEquals($dokter->id, $fetchedDetail->resep->kunjungan->dokter->id);
        $this->assertEquals($userDokter->id, $fetchedDetail->resep->kunjungan->dokter->user->id);
        $this->assertEquals('Dr. Test N1', $fetchedDetail->resep->kunjungan->dokter->user->name);

        // Assert Poli Matching
        $this->assertEquals($poli->id, $fetchedDetail->resep->kunjungan->poli->id);

        // Assert Obat Matching
        $this->assertEquals($obat->id, $fetchedDetail->obat->id);
    }
}
