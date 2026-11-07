<?php

namespace App\Support;

use Carbon\CarbonInterface;

class Format
{
    /** 1250000 → "Rp 1.250.000" (desimal hanya tampil jika ada). */
    public static function rupiah(float|int|string|null $nilai): string
    {
        $nilai = (float) $nilai;
        $desimal = fmod($nilai, 1.0) !== 0.0 ? 2 : 0;

        return 'Rp '.number_format($nilai, $desimal, ',', '.');
    }

    /** 12.50 → "12,5" */
    public static function angka(float|int|string|null $nilai): string
    {
        $nilai = (float) $nilai;

        return rtrim(rtrim(number_format($nilai, 2, ',', '.'), '0'), ',');
    }

    public static function tanggal(?CarbonInterface $tanggal, bool $jam = false): string
    {
        if (! $tanggal) {
            return '—';
        }

        return $tanggal->locale('id')->translatedFormat($jam ? 'j M Y, H:i' : 'j M Y');
    }
}
