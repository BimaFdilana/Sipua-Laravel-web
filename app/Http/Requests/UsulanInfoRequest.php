<?php

namespace App\Http\Requests;

use App\Models\Tahun;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Langkah 1. Hanya judul wajib untuk menyimpan draft;
 * kelengkapan field lain dicek saat usulan dikirim (US-12, US-13).
 */
class UsulanInfoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // dicek lewat policy di controller
    }

    public function rules(): array
    {
        $tahun = $this->route('usulan')?->tahun?->tahun ?? Tahun::aktif()?->tahun ?? now()->year;

        return [
            'program' => ['nullable', 'string', 'max:255'],
            'hasil' => ['nullable', 'string', 'max:255'],
            'judul_kegiatan' => ['required', 'string', 'max:255'],
            'jenis_output' => ['nullable', 'string', 'max:255'],
            'volume_output' => ['nullable', 'numeric', 'gt:0', 'max:9999999'],
            'satuan_output' => ['nullable', 'string', 'max:50'],
            'waktu_mulai' => ['nullable', 'date', "after_or_equal:{$tahun}-01-01", "before_or_equal:{$tahun}-12-31"],
            'waktu_selesai' => ['nullable', 'date', 'after_or_equal:waktu_mulai', "before_or_equal:{$tahun}-12-31"],
        ];
    }

    public function attributes(): array
    {
        return [
            'judul_kegiatan' => 'nama/judul kegiatan',
            'jenis_output' => 'jenis output',
            'volume_output' => 'volume output',
            'satuan_output' => 'satuan output',
            'waktu_mulai' => 'waktu mulai',
            'waktu_selesai' => 'waktu selesai',
        ];
    }

    public function messages(): array
    {
        return [
            'waktu_mulai.after_or_equal' => 'Waktu pelaksanaan harus di dalam tahun anggaran.',
            'waktu_mulai.before_or_equal' => 'Waktu pelaksanaan harus di dalam tahun anggaran.',
            'waktu_selesai.before_or_equal' => 'Waktu pelaksanaan harus di dalam tahun anggaran.',
            'waktu_selesai.after_or_equal' => 'Waktu selesai tidak boleh sebelum waktu mulai.',
        ];
    }
}
