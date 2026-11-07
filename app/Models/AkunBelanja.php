<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class AkunBelanja extends Model
{
    public const JENIS = ['Pegawai', 'Barang', 'Jasa', 'Modal'];

    protected $primaryKey = 'kode_akun';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'kode_akun',
        'nama_akun',
        'jenis_belanja',
        'keterangan',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
