<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Iku extends Model
{
    protected $fillable = [
        'kode_iku',
        'nama_iku',
        'sifat',
        'tahun',
        'target',
        'deskripsi',
        'kriteria'
    ];
}
