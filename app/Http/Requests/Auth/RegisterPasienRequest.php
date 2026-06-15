<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterPasienRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Akun
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'string', 'regex:/^08[0-9]{8,13}$/'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            
            // Kependudukan
            'nik' => ['required', 'string', 'numeric', 'digits:16', 'unique:profil_pasien,nik'],
            'no_bpjs' => ['nullable', 'string', 'numeric', 'digits:13', 'unique:profil_pasien,no_bpjs'],
            'no_kk' => ['nullable', 'string', 'numeric', 'digits:16'],
            'jenis_kelamin' => ['required', 'in:L,P'],
            'tanggal_lahir' => ['required', 'date', 'before_or_equal:today'],
            'tempat_lahir' => ['nullable', 'string', 'max:100'],
            'golongan_darah' => ['nullable', 'in:A,B,AB,O,Tidak Tahu'],

            // Alamat & Jenis Pasien
            'alamat' => ['nullable', 'string'],
            'kelurahan' => ['nullable', 'string', 'max:100'],
            'kecamatan' => ['nullable', 'string', 'max:100'],
            'jenis_pasien' => ['nullable', 'in:umum,bpjs'],
            'riwayat_alergi' => ['nullable', 'string'],
        ];
    }

    /**
     * Custom messages for validation errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama lengkap wajib diisi.',
            'name.max' => 'Nama lengkap maksimal 100 karakter.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email ini sudah terdaftar.',
            'phone.required' => 'Nomor HP/WA wajib diisi.',
            'phone.regex' => 'Nomor HP/WA harus berformat valid (contoh: 081234567890).',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'nik.required' => 'NIK wajib diisi.',
            'nik.digits' => 'NIK harus 16 digit.',
            'nik.numeric' => 'NIK harus berupa angka.',
            'nik.unique' => 'NIK ini sudah terdaftar.',
            'no_bpjs.digits' => 'Nomor BPJS harus 13 digit.',
            'no_bpjs.numeric' => 'Nomor BPJS harus berupa angka.',
            'no_bpjs.unique' => 'Nomor BPJS ini sudah terdaftar.',
            'no_kk.digits' => 'Nomor Kartu Keluarga harus 16 digit.',
            'no_kk.numeric' => 'Nomor Kartu Keluarga harus berupa angka.',
            'jenis_kelamin.required' => 'Jenis kelamin wajib dipilih.',
            'tanggal_lahir.required' => 'Tanggal lahir wajib diisi.',
            'tanggal_lahir.date' => 'Format tanggal lahir tidak valid.',
            'tanggal_lahir.before_or_equal' => 'Tanggal lahir tidak boleh di masa depan.',
            'tempat_lahir.required' => 'Tempat lahir wajib diisi.',
            'golongan_darah.in' => 'Golongan darah tidak valid.',
            'alamat.required' => 'Alamat lengkap wajib diisi.',
            'kelurahan.required' => 'Kelurahan wajib diisi.',
            'kecamatan.required' => 'Kecamatan wajib diisi.',
            'jenis_pasien.required' => 'Jenis pasien wajib dipilih.',
        ];
    }
}
