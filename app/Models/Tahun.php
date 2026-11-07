<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Tahun anggaran. Hanya satu tahun yang boleh aktif (status = true).
 */
class Tahun extends Model
{
    protected $table = 'tahuns';

    protected $fillable = [
        'tahun',
        'status',
    ];

    protected $casts = [
        'tahun' => 'integer',
        'status' => 'boolean',
    ];

    public static function aktif(): ?self
    {
        return static::where('status', true)->first();
    }

    /** Simpan data; jika tahun ini diaktifkan, tahun lain otomatis dinonaktifkan. */
    public function saveExclusive(array $attributes): self
    {
        DB::transaction(function () use ($attributes) {
            if (! empty($attributes['status'])) {
                static::where('status', true)->whereKeyNot($this->getKey())->update(['status' => false]);
            }

            $this->fill($attributes)->save();
        });

        return $this;
    }

    /** Pilihan untuk <select>: [2026 => "2026 (aktif)"]. */
    public static function options(): array
    {
        return static::orderByDesc('tahun')->get()
            ->mapWithKeys(fn (Tahun $t) => [$t->tahun => $t->tahun.($t->status ? ' (aktif)' : '')])
            ->all();
    }
}
