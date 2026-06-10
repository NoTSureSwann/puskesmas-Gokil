<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Dijaga oleh middleware role:admin
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name'              => ['required', 'string', 'max:255'],
            'email'             => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password'          => ['required', 'string', 'min:8', 'confirmed'],
            'role'              => ['required', 'in:dokter,farmasi,admin'],
            'phone'             => ['nullable', 'string', 'max:20', 'regex:/^[0-9\+\-\s]+$/'],
            'nip'               => ['required_if:role,dokter,farmasi', 'nullable', 'string', 'max:50'],
            'sip'               => ['required_if:role,dokter', 'nullable', 'string', 'max:50'],
            'spesialisasi'      => ['required_if:role,dokter', 'nullable', 'string', 'max:100'],
            'poli'              => ['required_if:role,dokter', 'nullable', 'string', 'max:100'],
            'harga_konsultasi'  => ['required_if:role,dokter', 'nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'jam_kerja'         => ['required_if:role,dokter', 'nullable', 'string', 'max:100'],
            'jabatan'           => ['required_if:role,farmasi', 'nullable', 'string', 'max:100'],
        ];
    }

    /**
     * Custom messages for validation errors.
     */
    public function messages(): array
    {
        return [
            'name.required'                 => 'Nama wajib diisi.',
            'name.max'                      => 'Nama maksimal 255 karakter.',
            'email.required'                => 'Email wajib diisi.',
            'email.email'                   => 'Format email tidak valid.',
            'email.unique'                  => 'Email sudah terdaftar.',
            'password.required'             => 'Kata sandi wajib diisi.',
            'password.min'                  => 'Kata sandi minimal 8 karakter.',
            'password.confirmed'            => 'Konfirmasi kata sandi tidak cocok.',
            'role.required'                 => 'Peran pengguna wajib dipilih.',
            'role.in'                       => 'Peran pengguna tidak valid.',
            'phone.regex'                   => 'Format nomor telepon tidak valid.',
            'nip.required_if'               => 'NIP wajib diisi untuk Dokter/Farmasi.',
            'sip.required_if'               => 'SIP wajib diisi untuk Dokter.',
            'spesialisasi.required_if'      => 'Spesialisasi wajib diisi untuk Dokter.',
            'poli.required_if'              => 'Poli wajib dipilih untuk Dokter.',
            'harga_konsultasi.required_if'  => 'Harga Konsultasi/Jasa Dokter wajib diisi.',
            'harga_konsultasi.min'          => 'Harga konsultasi tidak boleh negatif.',
            'harga_konsultasi.max'          => 'Harga konsultasi terlalu besar.',
            'jam_kerja.required_if'         => 'Jam Kerja Dokter wajib diisi.',
            'jabatan.required_if'           => 'Jabatan wajib diisi untuk Apoteker/Farmasi.',
        ];
    }
}
