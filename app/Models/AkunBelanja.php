<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AkunBelanja extends Model
{
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
}
