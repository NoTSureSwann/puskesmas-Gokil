<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StorePoliRequest extends FormRequest
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
            'kode_poli'  => ['required', 'string', 'max:10', 'unique:poli,kode_poli', 'regex:/^[A-Za-z0-9\-]+$/'],
            'nama_poli'  => ['required', 'string', 'max:100'],
            'deskripsi'  => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * Custom messages for validation errors.
     */
    public function messages(): array
    {
        return [
            'kode_poli.required'  => 'Kode Poli wajib diisi.',
            'kode_poli.max'       => 'Kode Poli maksimal 10 karakter.',
            'kode_poli.unique'    => 'Kode Poli sudah digunakan.',
            'kode_poli.regex'     => 'Kode Poli hanya boleh huruf, angka, dan tanda hubung.',
            'nama_poli.required'  => 'Nama Poli wajib diisi.',
            'nama_poli.max'       => 'Nama Poli maksimal 100 karakter.',
            'deskripsi.max'       => 'Deskripsi maksimal 500 karakter.',
        ];
    }
}
