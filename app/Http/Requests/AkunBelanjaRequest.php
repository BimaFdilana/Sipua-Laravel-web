<?php

namespace App\Http\Requests;

use App\Models\AkunBelanja;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AkunBelanjaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // dibatasi middleware role:admin
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'kode_akun' => trim((string) $this->kode_akun),
            'is_active' => $this->boolean('is_active'),
        ]);
    }

    public function rules(): array
    {
        $rules = [
            'nama_akun' => ['required', 'string', 'max:255'],
            'jenis_belanja' => ['required', Rule::in(AkunBelanja::JENIS)],
            'keterangan' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['boolean'],
        ];

        // Kode akun adalah primary key: hanya diisi saat membuat, tidak bisa diubah.
        if (! $this->route('akun_belanja')) {
            $rules['kode_akun'] = ['required', 'digits:6', Rule::unique('akun_belanjas', 'kode_akun')];
        }

        return $rules;
    }

    public function messages(): array
    {
        return ['kode_akun.digits' => 'Kode akun harus 6 digit angka (mis. 521211).'];
    }
}
