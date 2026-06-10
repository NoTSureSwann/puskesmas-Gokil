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
        // 1. Seed Dokter
        $dokterUser = User::updateOrCreate(
            ['email' => 'dokter@puskesmas.go.id'],
            [
                'name' => 'dr. Budi Setiawan',
                'password' => Hash::make('password123'),
                'role' => 'dokter',
                'phone' => '081222333444',
                'status' => 'aktif',
                'email_verified_at' => now(),
            ]
        );

        ProfilDokter::updateOrCreate(
            ['user_id' => $dokterUser->id],
            [
                'nip' => '198503152010011002',
                'sip' => 'SIP/2026/0012/100',
                'spesialisasi' => 'Dokter Umum',
                'poli' => 'Poli Umum',
                'harga_konsultasi' => 50000.00,
                'jam_kerja' => '08:00 - 15:00',
            ]
        );

        // 2. Seed Farmasi (Apoteker)
        $farmasiUser = User::updateOrCreate(
            ['email' => 'farmasi@puskesmas.go.id'],
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
            ['email' => 'pasien.bpjs@gmail.com'],
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
            ['email' => 'pasien.umum@gmail.com'],
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
