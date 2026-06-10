<?php

declare(strict_types=1);

namespace App\Http\Requests\Dokter;

use Illuminate\Foundation\Http\FormRequest;

class StoreResepRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Dijaga oleh middleware role:dokter
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'kunjungan_id'          => ['required', 'exists:kunjungan,id'],
            'catatan_dokter'        => ['nullable', 'string', 'max:2000'],
            'prioritas'             => ['required', 'in:normal,urgen'],
            'obat'                  => ['required', 'array', 'min:1', 'max:20'],
            'obat.*.obat_id'        => ['required', 'exists:obat,id'],
            'obat.*.jumlah'         => ['required', 'integer', 'min:1', 'max:999'],
            'obat.*.dosis'          => ['required', 'string', 'max:100'],
            'obat.*.aturan_pakai'   => ['required', 'string', 'max:100'],
            'obat.*.keterangan'     => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * Custom messages for validation errors.
     */
    public function messages(): array
    {
        return [
            'kunjungan_id.required'         => 'ID Kunjungan wajib diisi.',
            'kunjungan_id.exists'           => 'Kunjungan tidak ditemukan.',
            'prioritas.required'            => 'Prioritas resep wajib dipilih.',
            'prioritas.in'                  => 'Prioritas harus normal atau urgen.',
            'catatan_dokter.max'            => 'Catatan dokter maksimal 2000 karakter.',
            'obat.required'                 => 'Setidaknya harus ada 1 item obat dalam resep.',
            'obat.min'                      => 'Setidaknya harus ada 1 item obat dalam resep.',
            'obat.max'                      => 'Maksimal 20 item obat per resep.',
            'obat.*.obat_id.required'       => 'Pilih jenis obat.',
            'obat.*.obat_id.exists'         => 'Obat yang dipilih tidak valid.',
            'obat.*.jumlah.required'        => 'Masukkan jumlah obat.',
            'obat.*.jumlah.min'             => 'Jumlah minimal 1.',
            'obat.*.jumlah.max'             => 'Jumlah maksimal 999.',
            'obat.*.dosis.required'         => 'Aturan dosis wajib diisi.',
            'obat.*.dosis.max'              => 'Dosis maksimal 100 karakter.',
            'obat.*.aturan_pakai.required'  => 'Aturan pakai wajib diisi.',
            'obat.*.aturan_pakai.max'       => 'Aturan pakai maksimal 100 karakter.',
            'obat.*.keterangan.max'         => 'Keterangan maksimal 255 karakter.',
        ];
    }
}
