<?php

namespace App\Models;

use App\Enums\StatusUsulan;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Riwayat perubahan status usulan. Hanya ditambah, tidak pernah diubah atau dihapus.
 */
class UsulanLog extends Model
{
    public const UPDATED_AT = null;

    public const AKSI = [
        'buat' => 'Membuat usulan',
        'kirim' => 'Mengirim usulan',
        'teruskan' => 'Meneruskan',
        'kembalikan' => 'Mengembalikan untuk perbaikan',
        'tolak' => 'Menolak',
        'sahkan' => 'Mengesahkan',
        'prioritas' => 'Menetapkan prioritas',
    ];

    protected $fillable = ['user_id', 'tahap', 'aksi', 'status_dari', 'status_ke', 'catatan'];

    protected $casts = [
        'status_dari' => StatusUsulan::class,
        'status_ke' => StatusUsulan::class,
        'created_at' => 'datetime',
    ];

    public function usulan(): BelongsTo
    {
        return $this->belongsTo(Usulan::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function aksiLabel(): string
    {
        return self::AKSI[$this->aksi] ?? ucfirst($this->aksi);
    }
}
