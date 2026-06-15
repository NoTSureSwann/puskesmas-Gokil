<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterAdminRequest;
use App\Http\Requests\Auth\RegisterDokterRequest;
use App\Http\Requests\Auth\RegisterFarmasiRequest;
use App\Http\Requests\Auth\RegisterPasienRequest;
use App\Mail\VerifikasiEmailMail;
use App\Mail\WelcomeMail;
use App\Models\ProfilDokter;
use App\Models\ProfilFarmasi;
use App\Models\ProfilPasien;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;

/**
 * Class AuthController
 * Handles authentication, registration and email verification for all roles.
 */
class AuthController extends Controller
{
    /**
     * Tampilkan form registrasi berdasarkan role.
     * Hanya role 'pasien' yang diperbolehkan registrasi mandiri.
     */
    public function showRegisterForm(Request $request): View|RedirectResponse
    {
        $role = 'pasien';

        return view('auth.register.' . $role, compact('role'));
    }

    /**
     * Proses registrasi pasien mandiri.
     */
    public function register(Request $request): RedirectResponse
    {
        // Secara paksa men-set role hanya sebagai pasien untuk publik
        $role = 'pasien';

        // Resolve spesifik FormRequest berdasarkan role
        $validatedRequest = app(RegisterPasienRequest::class);

        // 1. Simpan user baru (langsung terverifikasi)
        $user = new User([
            'name' => $validatedRequest->name,
            'email' => $validatedRequest->email,
            'password' => Hash::make($validatedRequest->password),
            'phone' => $validatedRequest->phone,
            'status' => 'aktif',
            'email_verified_at' => now(),
        ]);
        $user->role = $role; // Set manual karena role sudah dihapus dari $fillable
        $user->save();

        // 2. Simpan profil berdasarkan role
        if ($role === 'pasien') {
            ProfilPasien::create([
                'user_id' => $user->id,
                'nik' => $validatedRequest->nik,
                'no_bpjs' => $validatedRequest->no_bpjs,
                'no_kk' => $validatedRequest->no_kk,
                'jenis_kelamin' => $validatedRequest->jenis_kelamin,
                'tanggal_lahir' => $validatedRequest->tanggal_lahir,
                'tempat_lahir' => $validatedRequest->tempat_lahir ?? '-',
                'alamat' => $validatedRequest->alamat ?? '-',
                'kelurahan' => $validatedRequest->kelurahan ?? '-',
                'kecamatan' => $validatedRequest->kecamatan ?? '-',
                'jenis_pasien' => $validatedRequest->jenis_pasien ?? 'umum',
                'riwayat_alergi' => $validatedRequest->riwayat_alergi,
                'golongan_darah' => $validatedRequest->golongan_darah ?? 'Tidak Tahu',
            ]);
        }

        // 3. Redirect ke halaman login sesuai role
        return redirect()->route('login', ['role' => $role])
            ->with('status', 'Registrasi berhasil! Akun Anda telah aktif, silakan masuk.');
    }

    /**
     * Tampilkan pemberitahuan verifikasi email.
     */
    public function showVerificationNotice(): View
    {
        return view('auth.verify-email');
    }

    /**
     * Proses verifikasi email dari tautan surat elektronik.
     */
    public function verifyEmail(Request $request, int $id, string $hash): RedirectResponse
    {
        $user = User::findOrFail($id);

        if (!hash_equals((string) $hash, sha1($user->email))) {
            abort(403, 'Tautan verifikasi tidak valid.');
        }

        if ($user->email_verified_at) {
            return redirect()->route('login', ['role' => $user->role])
                ->with('status', 'Email Anda sudah diverifikasi sebelumnya. Silakan masuk.');
        }

        // Tandai email terverifikasi
        $user->forceFill(['email_verified_at' => now()])->save();

        // Kirim WelcomeMail (via Queue)
        $loginUrl = route('login', ['role' => $user->role]);
        Mail::to($user->email)->send(new WelcomeMail($user->name, $user->role, $loginUrl));

        return redirect()->route('login', ['role' => $user->role])
            ->with('status', 'Email berhasil diverifikasi! Selamat datang di SI Puskesmas & Klinik Sehat Sentosa. Silakan masuk.');
    }

    /**
     * Tampilkan form login per role.
     */
    public function showLoginForm(string $role = 'pasien'): View
    {
        return view('auth.login', compact('role'));
    }

    /**
     * Proses autentikasi pengguna.
     */
    public function login(LoginRequest $request): RedirectResponse
    {
        $credentials = $request->only('email', 'password');
        $remember = $request->has('remember');

        if (!Auth::attempt($credentials, $remember)) {
            return back()->withErrors(['email' => 'Kredensial yang diberikan tidak cocok dengan data kami.'])
                ->withInput($request->only('email', 'remember', 'role'));
        }

        $user = Auth::user();

        // Validasi kesesuaian role
        if ($user->role !== $request->role) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()->withErrors([
                'email' => "Akun ini bukan akun " . ucfirst($request->role) . ". Silakan pilih peran yang tepat."
            ])->withInput($request->only('email', 'remember', 'role'));
        }

        // Validasi status aktif
        if ($user->status !== 'aktif') {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()->withErrors([
                'email' => 'Akun Anda dinonaktifkan. Silakan hubungi Administrator.'
            ])->withInput($request->only('email', 'remember', 'role'));
        }

        // Validasi verifikasi email
        if (!$user->email_verified_at) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('verification.notice')
                ->with('email', $user->email)
                ->with('error', 'Alamat email Anda belum diverifikasi. Silakan periksa kotak masuk email Anda.');
        }

        $request->session()->regenerate();

        return match ($user->role) {
            'pasien' => redirect()->route('pasien.dashboard'),
            'dokter' => redirect()->route('dokter.dashboard'),
            'farmasi' => redirect()->route('farmasi.dashboard'),
            'admin' => redirect()->route('admin.dashboard'),
            default => redirect()->route('home'),
        };
    }

    /**
     * Proses keluar sistem (logout).
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
