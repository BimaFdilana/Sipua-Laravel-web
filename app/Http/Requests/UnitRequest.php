<?php

namespace App\Http\Requests;

use App\Models\Unit;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UnitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // dibatasi middleware role:admin
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'unit_id' => strtoupper(trim((string) $this->unit_id)),
            'is_active' => $this->boolean('is_active'),
        ]);
    }

    public function rules(): array
    {
        return [
            'unit_id' => ['required', 'string', 'max:20', 'regex:/^[A-Z0-9\-]+$/', Rule::unique('units', 'unit_id')->ignore($this->route('unit'))],
            'nama_unit' => ['required', 'string', 'max:255'],
            'jenis' => ['required', Rule::in(Unit::JENIS)],
            'keterangan' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['boolean'],
        ];
    }

    public function attributes(): array
    {
        return ['unit_id' => 'kode unit', 'jenis' => 'jenis unit'];
    }

    public function messages(): array
    {
        return ['unit_id.regex' => 'Kode unit hanya boleh huruf, angka, dan tanda hubung (mis. TI, P3M).'];
    }
}
