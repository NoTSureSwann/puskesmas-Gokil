<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
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
        $userId = $this->route('id');

        $rules = [
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $userId],
            'phone'    => ['nullable', 'string', 'max:20', 'regex:/^[0-9\+\-\s]+$/'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ];

        // Cek role user yang sedang diedit untuk conditional fields
        $user = $userId ? \App\Models\User::query()->find((int) $userId) : null;

        if ($user && $user->role === 'dokter') {
            $rules['nip']              = ['required', 'string', 'max:50'];
            $rules['sip']              = ['required', 'string', 'max:50'];
            $rules['spesialisasi']     = ['required', 'string', 'max:100'];
            $rules['poli']             = ['required', 'string', 'max:100'];
            $rules['harga_konsultasi'] = ['required', 'numeric', 'min:0', 'max:99999999.99'];
            $rules['jam_kerja']        = ['required', 'string', 'max:100'];
        } elseif ($user && $user->role === 'farmasi') {
            $rules['nip']     = ['required', 'string', 'max:50'];
            $rules['jabatan'] = ['required', 'string', 'max:100'];
        }

        return $rules;
    }

    /**
     * Custom messages for validation errors.
     */
    public function messages(): array
    {
        return [
            'name.required'             => 'Nama wajib diisi.',
            'name.max'                  => 'Nama maksimal 255 karakter.',
            'email.required'            => 'Email wajib diisi.',
            'email.email'               => 'Format email tidak valid.',
            'email.unique'              => 'Email sudah terdaftar.',
            'phone.regex'               => 'Format nomor telepon tidak valid.',
            'password.min'              => 'Kata sandi minimal 8 karakter.',
            'password.confirmed'        => 'Konfirmasi kata sandi tidak cocok.',
            'nip.required'              => 'NIP wajib diisi.',
            'sip.required'              => 'SIP wajib diisi.',
            'spesialisasi.required'     => 'Spesialisasi wajib diisi.',
            'poli.required'             => 'Poli wajib dipilih.',
            'harga_konsultasi.required' => 'Harga Konsultasi wajib diisi.',
            'harga_konsultasi.min'      => 'Harga konsultasi tidak boleh negatif.',
            'harga_konsultasi.max'      => 'Harga konsultasi terlalu besar.',
            'jam_kerja.required'        => 'Jam Kerja wajib diisi.',
            'jabatan.required'          => 'Jabatan wajib diisi.',
        ];
    }
}
