<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Indikator Kinerja Utama. Kode unik per tahun anggaran.
 */
class Iku extends Model
{
    public const SIFAT = ['Wajib', 'Pilihan'];

    protected $fillable = [
        'kode_iku',
        'nama_iku',
        'sifat',
        'tahun',
        'target',
        'deskripsi',
        'kriteria',
    ];

    protected $casts = [
        'tahun' => 'integer',
    ];

    public function scopeForTahun(Builder $query, int $tahun): Builder
    {
        return $query->where('tahun', $tahun);
    }
}
