<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UsulanDokumen extends Model
{
    public const KATEGORI = [
        'referensi_harga' => 'Referensi harga / SBM',
        'kak_ttd' => 'KAK/TOR bertanda tangan',
        'lainnya' => 'Data dukung lainnya',
    ];

    protected $fillable = ['kategori', 'nama_asli', 'path', 'ukuran', 'uploaded_by'];

    public function usulan(): BelongsTo
    {
        return $this->belongsTo(Usulan::class);
    }

    public function pengunggah(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function kategoriLabel(): string
    {
        return self::KATEGORI[$this->kategori] ?? $this->kategori;
    }

    public function ukuranLabel(): string
    {
        return $this->ukuran >= 1048576
            ? number_format($this->ukuran / 1048576, 1, ',', '.').' MB'
            : number_format(max(1, $this->ukuran / 1024), 0, ',', '.').' KB';
    }
}
