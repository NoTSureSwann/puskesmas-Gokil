<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateObatRequest extends FormRequest
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
            'nama_obat'     => ['required', 'string', 'max:100'],
            'satuan'        => ['required', 'string', 'max:50'],
            'kategori'      => ['required', 'string', 'max:50'],
            'stok'          => ['required', 'integer', 'min:0', 'max:999999'],
            'stok_minimum'  => ['required', 'integer', 'min:0', 'max:999999'],
            'harga_satuan'  => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'deskripsi'     => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Custom messages for validation errors.
     */
    public function messages(): array
    {
        return [
            'nama_obat.required'    => 'Nama Obat wajib diisi.',
            'nama_obat.max'         => 'Nama Obat maksimal 100 karakter.',
            'satuan.required'       => 'Satuan wajib diisi.',
            'kategori.required'     => 'Kategori wajib diisi.',
            'stok.required'         => 'Stok wajib diisi.',
            'stok.min'              => 'Stok tidak boleh negatif.',
            'stok.max'              => 'Stok maksimal 999.999.',
            'stok_minimum.required' => 'Stok minimum wajib diisi.',
            'stok_minimum.min'      => 'Stok minimum tidak boleh negatif.',
            'harga_satuan.required' => 'Harga satuan wajib diisi.',
            'harga_satuan.min'      => 'Harga satuan tidak boleh negatif.',
            'harga_satuan.max'      => 'Harga satuan terlalu besar.',
            'deskripsi.max'         => 'Deskripsi maksimal 1000 karakter.',
        ];
    }
}
