<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RabItem extends Model
{
    protected $fillable = ['kode_akun', 'uraian', 'volume', 'satuan', 'harga_satuan', 'jumlah', 'urutan'];

    protected $casts = [
        'volume' => 'decimal:2',
        'harga_satuan' => 'decimal:2',
        'jumlah' => 'decimal:2',
    ];

    public function usulan(): BelongsTo
    {
        return $this->belongsTo(Usulan::class);
    }

    public function akun(): BelongsTo
    {
        return $this->belongsTo(AkunBelanja::class, 'kode_akun', 'kode_akun');
    }
}
