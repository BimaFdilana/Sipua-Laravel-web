<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TahunRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // dibatasi middleware role:admin
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['status' => $this->boolean('status')]);
    }

    public function rules(): array
    {
        return [
            'tahun' => ['required', 'integer', 'between:2000,2100', Rule::unique('tahuns', 'tahun')->ignore($this->route('tahun'))],
            'status' => ['boolean'],
        ];
    }

    public function attributes(): array
    {
        return ['tahun' => 'tahun anggaran'];
    }
}
