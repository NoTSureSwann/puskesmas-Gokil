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

/**
 * Security & Validation Feature Tests
 *
 * Memverifikasi bahwa seluruh proteksi keamanan, validasi data,
 * dan business logic guards berfungsi dengan benar.
 */
class SecurityValidationTest extends TestCase
{
    use RefreshDatabase;

    // ===========================
    // HELPER METHODS
    // ===========================

    private function createUser(string $role, array $overrides = []): User
    {
        return User::factory()->create(array_merge([
            'role' => $role,
            'status' => 'aktif',
            'email_verified_at' => now(),
        ], $overrides));
    }

    private function createPoli(string $kode = 'PL-UMUM', string $nama = 'Poli Umum'): Poli
    {
        return Poli::create([
            'kode_poli' => $kode,
            'nama_poli' => $nama,
            'deskripsi' => 'Poli umum untuk pelayanan dasar',
            'is_aktif' => true,
        ]);
    }

    private function createObat(string $kode = 'OBT-001', int $stok = 100): Obat
    {
        return Obat::create([
            'kode_obat' => $kode,
            'nama_obat' => 'Paracetamol 500mg',
            'satuan' => 'Tablet',
            'kategori' => 'Analgesik',
            'stok' => $stok,
            'stok_minimum' => 10,
            'harga_satuan' => 5000,
            'is_aktif' => true,
        ]);
    }

    // ===========================
    // 1. AUTHENTICATION & AUTHORIZATION
    // ===========================

    public function test_unauthenticated_user_cannot_access_admin_routes(): void
    {
        $response = $this->get('/admin');
        $response->assertRedirect(route('login'));
    }

    public function test_unauthenticated_user_cannot_access_dokter_routes(): void
    {
        $response = $this->get('/dokter');
        $response->assertRedirect(route('login'));
    }

    public function test_pasien_cannot_access_dokter_routes(): void
    {
        $user = $this->createUser('pasien');

        $response = $this->actingAs($user)->get('/dokter');
        $response->assertStatus(403);
    }

    public function test_pasien_cannot_access_admin_routes(): void
    {
        $user = $this->createUser('pasien');

        $response = $this->actingAs($user)->get('/admin');
        $response->assertStatus(403);
    }

    public function test_dokter_cannot_access_admin_routes(): void
    {
        $user = $this->createUser('dokter');

        $response = $this->actingAs($user)->get('/admin');
        $response->assertStatus(403);
    }

    public function test_farmasi_cannot_access_admin_routes(): void
    {
        $user = $this->createUser('farmasi');

        $response = $this->actingAs($user)->get('/admin');
        $response->assertStatus(403);
    }

    // ===========================
    // 2. ADMIN CRUD VALIDATION
    // ===========================

    public function test_admin_cannot_delete_self(): void
    {
        $admin = $this->createUser('admin');

        $response = $this->actingAs($admin)->delete("/admin/users/{$admin->id}");
        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    public function test_poli_store_validation_rejects_invalid_data(): void
    {
        $admin = $this->createUser('admin');

        // Empty kode_poli
        $response = $this->actingAs($admin)->post('/admin/poli', [
            'kode_poli' => '',
            'nama_poli' => '',
        ]);
        $response->assertSessionHasErrors(['kode_poli', 'nama_poli']);
    }

    public function test_poli_store_validation_accepts_valid_data(): void
    {
        $admin = $this->createUser('admin');

        $response = $this->actingAs($admin)->post('/admin/poli', [
            'kode_poli' => 'PL-GIGI',
            'nama_poli' => 'Poli Gigi',
            'deskripsi' => 'Pelayanan kesehatan gigi dan mulut.',
        ]);

        $response->assertRedirect(route('admin.poli.index'));
        $this->assertDatabaseHas('poli', ['kode_poli' => 'PL-GIGI']);
    }

    public function test_poli_store_rejects_duplicate_kode(): void
    {
        $admin = $this->createUser('admin');
        $this->createPoli('PL-UMUM');

        $response = $this->actingAs($admin)->post('/admin/poli', [
            'kode_poli' => 'PL-UMUM',
            'nama_poli' => 'Poli Umum Duplikat',
        ]);

        $response->assertSessionHasErrors(['kode_poli']);
    }

    public function test_obat_store_rejects_negative_stock(): void
    {
        $admin = $this->createUser('admin');

        $response = $this->actingAs($admin)->post('/admin/obat', [
            'kode_obat' => 'OBT-TEST',
            'nama_obat' => 'Test Obat',
            'satuan' => 'Tab',
            'kategori' => 'Test',
            'stok' => -5,
            'stok_minimum' => 10,
            'harga_satuan' => 1000,
        ]);

        $response->assertSessionHasErrors(['stok']);
    }

    public function test_obat_destroy_blocked_when_has_resep(): void
    {
        $admin = $this->createUser('admin');
        $obat = $this->createObat();

        // Create a detail_resep referencing this obat
        $poli = $this->createPoli();
        $pasien = $this->createUser('pasien');
        $profilPasien = ProfilPasien::create([
            'user_id' => $pasien->id,
            'nik' => '1234567890123456',
            'jenis_kelamin' => 'L',
            'tanggal_lahir' => '1990-01-01',
            'tempat_lahir' => 'Jakarta',
            'alamat' => 'Jl. Test',
            'kelurahan' => 'Test',
            'kecamatan' => 'Test',
            'jenis_pasien' => 'umum',
        ]);
        $dokterUser = $this->createUser('dokter');
        $profilDokter = ProfilDokter::create([
            'user_id' => $dokterUser->id,
            'nip' => '123456',
            'sip' => 'SIP-001',
            'spesialisasi' => 'Umum',
            'poli' => 'Poli Umum',
        ]);

        $kunjungan = Kunjungan::create([
            'pasien_id' => $profilPasien->id,
            'poli_id' => $poli->id,
            'tanggal_kunjungan' => now(),
            'keluhan' => 'Sakit kepala',
            'status' => 'selesai',
            'jenis_kunjungan' => 'umum',
            'jam_daftar' => now(),
        ]);

        $resep = Resep::create([
            'kunjungan_id' => $kunjungan->id,
            'dokter_id' => $profilDokter->id,
            'no_resep' => 'RSP-TEST-001',
            'status' => 'selesai',
            'jam_input_resep' => now(),
        ]);

        DetailResep::create([
            'resep_id' => $resep->id,
            'obat_id' => $obat->id,
            'jumlah' => 10,
            'dosis' => '3x1',
            'aturan_pakai' => 'Sesudah makan',
        ]);

        $response = $this->actingAs($admin)->delete("/admin/obat/{$obat->id}");
        $response->assertRedirect();
        $response->assertSessionHas('error');

        // Obat should still exist (not soft-deleted)
        $this->assertDatabaseHas('obat', ['id' => $obat->id, 'deleted_at' => null]);
    }

    // ===========================
    // 3. XSS SANITIZATION
    // ===========================

    public function test_xss_input_is_sanitized(): void
    {
        $admin = $this->createUser('admin');

        $response = $this->actingAs($admin)->post('/admin/poli', [
            'kode_poli' => 'PL-XSS',
            'nama_poli' => '<script>alert("xss")</script>Poli XSS',
            'deskripsi' => '<img src=x onerror=alert(1)>Deskripsi',
        ]);

        $response->assertRedirect(route('admin.poli.index'));

        // Verify tags are stripped
        $poli = Poli::query()->where('kode_poli', 'PL-XSS')->first();
        $this->assertNotNull($poli);
        $this->assertStringNotContainsString('<script>', $poli->nama_poli);
        $this->assertStringNotContainsString('<img', $poli->deskripsi);
        $this->assertStringContainsString('Poli XSS', $poli->nama_poli);
    }



    // ===========================
    // 5. LOGIN THROTTLE
    // ===========================

    public function test_login_throttle_blocks_after_5_attempts(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->withSession(['_token' => csrf_token()])
                ->post('/login', [
                    '_token' => csrf_token(),
                    'email' => 'nonexistent@test.com',
                    'password' => 'wrongpassword',
                    'role' => 'pasien',
                ]);
        }

        // 6th attempt should be throttled
        $response = $this->post('/login', [
            '_token' => csrf_token(),
            'email' => 'nonexistent@test.com',
            'password' => 'wrongpassword',
            'role' => 'pasien',
        ]);

        $response->assertStatus(429);
    }

    // ===========================
    // 6. DUPLICATE KUNJUNGAN CHECK
    // ===========================

    public function test_duplicate_kunjungan_same_day_same_poli_rejected(): void
    {
        $pasien = $this->createUser('pasien');
        $poli = $this->createPoli();

        $profilPasien = ProfilPasien::create([
            'user_id' => $pasien->id,
            'nik' => '9876543210123456',
            'jenis_kelamin' => 'P',
            'tanggal_lahir' => '1995-05-15',
            'tempat_lahir' => 'Bandung',
            'alamat' => 'Jl. Test 2',
            'kelurahan' => 'Kelurahan Test',
            'kecamatan' => 'Kecamatan Test',
            'jenis_pasien' => 'umum',
        ]);

        // First visit
        Kunjungan::create([
            'pasien_id' => $profilPasien->id,
            'poli_id' => $poli->id,
            'tanggal_kunjungan' => now()->format('Y-m-d'),
            'keluhan' => 'Sakit kepala',
            'status' => 'menunggu',
            'jenis_kunjungan' => 'umum',
            'jam_daftar' => now(),
        ]);

        // Second visit same poli same day
        $response = $this->actingAs($pasien)->post('/pasien/daftar', [
            'poli_id' => $poli->id,
            'keluhan' => 'Sakit perut',
            'jenis_kunjungan' => 'umum',
            'metode_kunjungan' => 'langsung',
            'tanggal_kunjungan' => now()->format('Y-m-d'),
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    // ===========================
    // 7. USER VALIDATION
    // ===========================

    public function test_user_store_rejects_invalid_email(): void
    {
        $admin = $this->createUser('admin');

        $response = $this->actingAs($admin)->post('/admin/users', [
            'name' => 'Test User',
            'email' => 'invalid-email',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'admin',
        ]);

        $response->assertSessionHasErrors(['email']);
    }

    public function test_user_store_rejects_short_password(): void
    {
        $admin = $this->createUser('admin');

        $response = $this->actingAs($admin)->post('/admin/users', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => '123',
            'password_confirmation' => '123',
            'role' => 'admin',
        ]);

        $response->assertSessionHasErrors(['password']);
    }

    // ===========================
    // 8. KUNJUNGAN VALIDATION
    // ===========================

    public function test_kunjungan_rejects_past_date(): void
    {
        $pasien = $this->createUser('pasien');
        $poli = $this->createPoli('PL-PAST', 'Poli Past');

        ProfilPasien::create([
            'user_id' => $pasien->id,
            'nik' => '1111222233334444',
            'jenis_kelamin' => 'L',
            'tanggal_lahir' => '2000-01-01',
            'tempat_lahir' => 'Surabaya',
            'alamat' => 'Jl. Test 3',
            'kelurahan' => 'Kel',
            'kecamatan' => 'Kec',
            'jenis_pasien' => 'umum',
        ]);

        $response = $this->actingAs($pasien)->post('/pasien/daftar', [
            'poli_id' => $poli->id,
            'keluhan' => 'Test',
            'jenis_kunjungan' => 'umum',
            'metode_kunjungan' => 'langsung',
            'tanggal_kunjungan' => '2020-01-01',
        ]);

        $response->assertSessionHasErrors(['tanggal_kunjungan']);
    }
}
