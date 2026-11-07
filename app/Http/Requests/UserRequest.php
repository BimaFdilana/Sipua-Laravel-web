<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Validator;

class UserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // dibatasi middleware role:admin
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => strtolower(trim((string) $this->email)),
            'is_active' => $this->boolean('is_active'),
            'unit_id' => $this->unit_id ?: null,
            'nip' => $this->nip ? preg_replace('/\s+/', '', $this->nip) : null,
        ]);
    }

    public function rules(): array
    {
        $user = $this->route('user');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user)],
            'password' => [$user ? 'nullable' : 'required', 'confirmed', Password::min(8)],
            'role' => ['required', Rule::in(array_keys(User::ROLES))],
            'unit_id' => ['nullable', 'required_if:role,'.User::ROLE_PENGUSUL, Rule::exists('units', 'id')],
            'nama_lengkap' => ['nullable', 'string', 'max:255'],
            'nip' => ['nullable', 'regex:/^[0-9]{8,20}$/'],
            'jabatan' => ['nullable', 'string', 'max:255'],
            'no_hp' => ['nullable', 'regex:/^(\+62|62|0)[0-9]{8,13}$/'],
            'is_active' => ['boolean'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                $target = $this->route('user');

                // Admin tidak boleh mengunci dirinya sendiri keluar.
                if ($target && $target->is($this->user())) {
                    if ($this->input('role') !== User::ROLE_ADMIN) {
                        $validator->errors()->add('role', 'Anda tidak bisa mengubah peran akun Anda sendiri.');
                    }
                    if (! $this->boolean('is_active')) {
                        $validator->errors()->add('is_active', 'Anda tidak bisa menonaktifkan akun Anda sendiri.');
                    }
                }
            },
        ];
    }

    public function messages(): array
    {
        return [
            'unit_id.required_if' => 'Unit kerja wajib dipilih untuk peran Pengusul.',
            'nip.regex' => 'NIP hanya berisi 8–20 digit angka.',
            'no_hp.regex' => 'Nomor HP tidak valid (contoh: 081234567890).',
        ];
    }

    /** Data siap simpan; password kosong saat edit berarti tidak diubah. */
    public function payload(): array
    {
        $data = $this->safe()->except(['password']);

        if ($this->filled('password')) {
            $data['password'] = $this->input('password');
        }

        return $data;
    }
}
