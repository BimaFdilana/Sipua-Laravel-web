<?php

namespace App\Enums;

/**
 * Status usulan. Alur lengkap: lihat PRD bagian "Alur & status usulan".
 */
enum StatusUsulan: string
{
    case Draft = 'draft';
    case Diajukan = 'diajukan';             // menunggu Perencanaan
    case PerluPerbaikan = 'perlu_perbaikan';
    case DiWadir = 'di_wadir';
    case DiSpi = 'di_spi';
    case DiDirektur = 'di_direktur';
    case Disahkan = 'disahkan';
    case Ditolak = 'ditolak';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Diajukan => 'Diajukan',
            self::PerluPerbaikan => 'Perlu perbaikan',
            self::DiWadir => 'Verifikasi Wadir',
            self::DiSpi => 'Verifikasi SPI',
            self::DiDirektur => 'Menunggu pengesahan',
            self::Disahkan => 'Disahkan',
            self::Ditolak => 'Ditolak',
        };
    }

    /** Warna badge (lihat x-ui.badge). */
    public function color(): string
    {
        return match ($this) {
            self::Draft => 'slate',
            self::Diajukan, self::DiWadir, self::DiSpi, self::DiDirektur => 'blue',
            self::PerluPerbaikan => 'amber',
            self::Disahkan => 'green',
            self::Ditolak => 'red',
        };
    }

    /** Pengusul hanya boleh mengubah isi usulan pada status ini. */
    public function isEditable(): bool
    {
        return in_array($this, [self::Draft, self::PerluPerbaikan], true);
    }

    /** Sedang di salah satu tahap verifikasi. */
    public function isInProcess(): bool
    {
        return in_array($this, [self::Diajukan, self::DiWadir, self::DiSpi, self::DiDirektur], true);
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn (self $s) => [$s->value => $s->label()])->all();
    }
}
