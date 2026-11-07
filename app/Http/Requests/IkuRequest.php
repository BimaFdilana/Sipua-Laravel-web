<?php

namespace App\Http\Requests;

use App\Models\Iku;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IkuRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // dibatasi middleware role:admin
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['kode_iku' => strtoupper(preg_replace('/\s+/', '', (string) $this->kode_iku))]);
    }

    public function rules(): array
    {
        return [
            'kode_iku' => [
                'required', 'string', 'max:20',
                Rule::unique('ikus', 'kode_iku')->where('tahun', $this->input('tahun'))->ignore($this->route('iku')),
            ],
            'nama_iku' => ['required', 'string', 'max:255'],
            'sifat' => ['required', Rule::in(Iku::SIFAT)],
            'tahun' => ['required', 'integer', Rule::exists('tahuns', 'tahun')],
            'target' => ['nullable', 'string', 'max:100'],
            'deskripsi' => ['nullable', 'string', 'max:5000'],
            'kriteria' => ['nullable', 'string', 'max:5000'],
        ];
    }

    public function messages(): array
    {
        return [
            'kode_iku.unique' => 'Kode IKU ini sudah dipakai di tahun yang sama.',
            'tahun.exists' => 'Tahun belum terdaftar di Tahun Anggaran.',
        ];
    }
}
