<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Unit extends Model
{
    public const JENIS = ['Jurusan', 'Bagian', 'Unit'];

    protected $fillable = [
        'unit_id',
        'nama_unit',
        'jenis',
        'keterangan',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /** Pilihan untuk <select>: [id => "TI — Teknik Informatika"]. */
    public static function options(bool $activeOnly = true): array
    {
        return static::query()
            ->when($activeOnly, fn ($q) => $q->active())
            ->orderBy('nama_unit')
            ->get()
            ->mapWithKeys(fn (Unit $u) => [$u->id => "{$u->unit_id} — {$u->nama_unit}"])
            ->all();
    }
}
