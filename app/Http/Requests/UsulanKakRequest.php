<?php

namespace App\Http\Requests;

use App\Models\Usulan;
use Illuminate\Foundation\Http\FormRequest;

/** Langkah 2: KAK/TOR. Semua boleh kosong saat disimpan sebagai draft. */
class UsulanKakRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // dicek lewat policy di controller
    }

    public function rules(): array
    {
        return collect(Usulan::WAJIB_KAK)->mapWithKeys(fn ($label, $field) => [$field => ['nullable', 'string', 'max:10000']])->all();
    }

    public function attributes(): array
    {
        return collect(Usulan::WAJIB_KAK)->map(fn ($l) => strtolower($l))->all();
    }
}
