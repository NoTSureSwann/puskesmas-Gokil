<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\ProfilDokter;
use App\Models\ProfilFarmasi;
use App\Models\ProfilPasien;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Seed 14 Dokter (2 per Poli)
        $polis = [
            'Poli Umum' => ['Budi Setiawan', 'Sarah Ayu'],
            'Poli Gigi' => ['Andi Wijaya', 'Sinta Dewi'],
            'Poli Penyakit Dalam' => ['Hendra Gunawan', 'Maya Sari'],
            'Poli Anak' => ['Rizky Pratama', 'Dian Kusuma'],
            'Poli Kandungan (Obgyn)' => ['Farhan Hidayat', 'Lina Marlina'],
            'Poli Bedah' => ['Satria Wiguna', 'Reza Pahlevi'],
            'Poli Saraf' => ['Tari Lestari', 'Bima Sakti'],
        ];

        $basePrices = [
            'Poli Umum' => 150000,
            'Poli Gigi' => 200000,
            'Poli Penyakit Dalam' => 300000,
            'Poli Anak' => 250000,
            'Poli Kandungan (Obgyn)' => 350000,
            'Poli Bedah' => 400000,
            'Poli Saraf' => 320000,
        ];

        $dokterCounter = 1;
        $nipBase = 198503152010011000;
        foreach ($polis as $poliName => $dokters) {
            foreach ($dokters as $index => $dokterName) {
                $emailName = strtolower(str_replace(' ', '', $dokterName));
                $email = ($dokterCounter === 1) ? 'dokter@metopen.local' : "dr.{$emailName}@metopen.local";
                $dokterUser = User::updateOrCreate(
                    ['email' => $email],
                    [
                        'name' => "dr. {$dokterName}",
                        'password' => Hash::make('password123'),
                        'role' => 'dokter',
                        'phone' => '0812' . str_pad((string)$dokterCounter, 8, '0', STR_PAD_LEFT),
                        'status' => 'aktif',
                        'email_verified_at' => now(),
                    ]
                );

                $nip = (string)($nipBase + $dokterCounter);
                $sip = 'SIP/2026/0012/' . str_pad((string)$dokterCounter, 3, '0', STR_PAD_LEFT);
                $jamKerja = $index === 0 ? '08:00 - 15:00' : '15:00 - 21:00';
                $hargaKonsultasi = $basePrices[$poliName] + ($index * 50000); // Dokter ke-2 lebih mahal 50k

                ProfilDokter::updateOrCreate(
                    ['user_id' => $dokterUser->id],
                    [
                        'nip' => $nip,
                        'sip' => $sip,
                        'spesialisasi' => "Spesialis {$poliName}", // Asumsi simplifikasi
                        'poli' => $poliName,
                        'harga_konsultasi' => $hargaKonsultasi,
                        'jam_kerja' => $jamKerja,
                    ]
                );
                $dokterCounter++;
            }
        }

        // 2. Seed Farmasi (Apoteker)
        $farmasiUser = User::updateOrCreate(
            ['email' => 'farmasi@metopen.local'],
            [
                'name' => 'Siti Aminah, S.Farm',
                'password' => Hash::make('password123'),
                'role' => 'farmasi',
                'phone' => '081333444555',
                'status' => 'aktif',
                'email_verified_at' => now(),
            ]
        );

        ProfilFarmasi::updateOrCreate(
            ['user_id' => $farmasiUser->id],
            [
                'nip' => '199008202015022001',
                'jabatan' => 'Apoteker Penanggung Jawab',
            ]
        );

        // 3. Seed Pasien (BPJS)
        $pasienUserBpjs = User::updateOrCreate(
            ['email' => 'pasien@metopen.local'],
            [
                'name' => 'Ahmad Hidayat',
                'password' => Hash::make('password123'),
                'role' => 'pasien',
                'phone' => '081444555666',
                'status' => 'aktif',
                'email_verified_at' => now(),
            ]
        );

        ProfilPasien::updateOrCreate(
            ['user_id' => $pasienUserBpjs->id],
            [
                'nik' => '3171012345670001',
                'no_bpjs' => '0001234567890',
                'no_kk' => '3171019876543210',
                'jenis_kelamin' => 'L',
                'tanggal_lahir' => '1995-04-12',
                'tempat_lahir' => 'Jakarta',
                'alamat' => 'Jl. Salemba Raya No. 12',
                'kelurahan' => 'Kramat',
                'kecamatan' => 'Senen',
                'jenis_pasien' => 'bpjs',
                'riwayat_alergi' => 'Alergi obat Amoxicillin',
                'golongan_darah' => 'O',
            ]
        );

        // 4. Seed Pasien (Umum)
        $pasienUserUmum = User::updateOrCreate(
            ['email' => 'pasien.umum@metopen.local'],
            [
                'name' => 'Rina Wijaya',
                'password' => Hash::make('password123'),
                'role' => 'pasien',
                'phone' => '081555666777',
                'status' => 'aktif',
                'email_verified_at' => now(),
            ]
        );

        ProfilPasien::updateOrCreate(
            ['user_id' => $pasienUserUmum->id],
            [
                'nik' => '3171012345670002',
                'no_bpjs' => null,
                'no_kk' => '3171019876543211',
                'jenis_kelamin' => 'P',
                'tanggal_lahir' => '1998-11-20',
                'tempat_lahir' => 'Bandung',
                'alamat' => 'Jl. Percetakan Negara No. 8',
                'kelurahan' => 'Rawasari',
                'kecamatan' => 'Cempaka Putih',
                'jenis_pasien' => 'umum',
                'riwayat_alergi' => null,
                'golongan_darah' => 'AB',
            ]
        );
    }
}
