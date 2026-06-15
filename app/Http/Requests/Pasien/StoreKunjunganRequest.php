<?php

declare(strict_types=1);

namespace App\Http\Requests\Pasien;

use Illuminate\Foundation\Http\FormRequest;

class StoreKunjunganRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Dijaga oleh middleware role:pasien
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'poli_id'           => ['required', 'exists:poli,id'],
            'keluhan'           => ['required', 'string', 'max:2000'],
            'jenis_kunjungan'   => ['required', 'in:umum,bpjs'],
            'metode_kunjungan'  => ['required', 'in:langsung,telemedisin'],
            'tanggal_kunjungan' => ['required', 'date', 'after_or_equal:today'],
        ];
    }

    /**
     * Custom messages for validation errors.
     */
    public function messages(): array
    {
        return [
            'poli_id.required'                  => 'Poli tujuan wajib dipilih.',
            'poli_id.exists'                    => 'Poli yang dipilih tidak valid.',
            'keluhan.required'                  => 'Keluhan wajib diisi.',
            'keluhan.max'                       => 'Keluhan maksimal 2000 karakter.',
            'jenis_kunjungan.required'          => 'Jenis kunjungan wajib dipilih.',
            'jenis_kunjungan.in'                => 'Jenis kunjungan harus umum atau bpjs.',
            'metode_kunjungan.required'         => 'Metode kunjungan wajib dipilih.',
            'metode_kunjungan.in'               => 'Metode kunjungan harus langsung atau telemedisin.',
            'tanggal_kunjungan.required'        => 'Tanggal kunjungan wajib diisi.',
            'tanggal_kunjungan.date'            => 'Format tanggal tidak valid.',
            'tanggal_kunjungan.after_or_equal'  => 'Tanggal kunjungan tidak boleh di masa lalu.',
        ];
    }
}
