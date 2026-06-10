<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreKeluargaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Hanya pasien yang sudah login yang bisa menambahkan anggota keluarga
        return Auth::check() && Auth::user()->role === 'pasien';
    }

    /**
     * Get the validation rules that apply to the request.
     * Validasi ketat untuk keamanan dan integritas data
     */
    public function rules(): array
    {
        return [
            'nama_lengkap' => 'required|string|max:255|regex:/^[a-zA-Z\s]+$/', // Anti SQL Injection / XSS
            'hubungan' => 'required|string|in:Istri,Suami,Anak,Orang Tua',
            'nik' => 'nullable|string|size:16|regex:/^[0-9]+$/', // Pastikan hanya angka
            'tanggal_lahir' => 'required|date|before:today',
            'jenis_kelamin' => 'required|in:L,P',
        ];
    }

    /**
     * Custom messages
     */
    public function messages(): array
    {
        return [
            'nama_lengkap.regex' => 'Nama hanya boleh berisi huruf dan spasi.',
            'nik.size' => 'NIK harus tepat 16 digit angka.',
            'nik.regex' => 'NIK hanya boleh berisi angka.',
            'tanggal_lahir.before' => 'Tanggal lahir tidak valid.',
        ];
    }
}
