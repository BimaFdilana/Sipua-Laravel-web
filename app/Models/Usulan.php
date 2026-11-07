<?php

namespace App\Models;

use App\Enums\StatusUsulan;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Usulan extends Model
{
    use SoftDeletes;

    /** Field langkah 1 yang wajib terisi sebelum usulan dikirim. */
    public const WAJIB_INFO = [
        'program' => 'Program',
        'hasil' => 'Hasil (outcome)',
        'judul_kegiatan' => 'Nama/judul kegiatan',
        'jenis_output' => 'Jenis output',
        'volume_output' => 'Volume output',
        'satuan_output' => 'Satuan output',
        'waktu_mulai' => 'Waktu mulai',
        'waktu_selesai' => 'Waktu selesai',
    ];

    /** Field KAK/TOR (langkah 2) yang wajib terisi sebelum usulan dikirim. */
    public const WAJIB_KAK = [
        'dasar_hukum' => 'Dasar hukum',
        'latar_belakang' => 'Latar belakang',
        'tujuan' => 'Tujuan',
        'penerima_manfaat' => 'Penerima manfaat',
        'metode_pelaksanaan' => 'Metode pelaksanaan',
        'uraian_kebutuhan' => 'Uraian kebutuhan',
    ];

    /** Tingkat prioritas (US-20) => warna badge. */
    public const PRIORITAS = [
        'Tinggi' => 'red',
        'Sedang' => 'amber',
        'Rendah' => 'slate',
    ];

    protected $fillable = [
        'program', 'hasil', 'judul_kegiatan', 'jenis_output', 'volume_output', 'satuan_output',
        'waktu_mulai', 'waktu_selesai',
        'dasar_hukum', 'latar_belakang', 'tujuan', 'penerima_manfaat', 'metode_pelaksanaan', 'uraian_kebutuhan',
    ];

    protected $casts = [
        'status' => StatusUsulan::class,
        'waktu_mulai' => 'date',
        'waktu_selesai' => 'date',
        'volume_output' => 'decimal:2',
        'total_rab' => 'decimal:2',
        'submitted_at' => 'datetime',
    ];

    protected $attributes = [
        'status' => 'draft',
        'total_rab' => 0,
    ];

    // ---------- Relasi ----------

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function tahun(): BelongsTo
    {
        return $this->belongsTo(Tahun::class);
    }

    public function pembuat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function rabItems(): HasMany
    {
        return $this->hasMany(RabItem::class)->orderBy('urutan')->orderBy('id');
    }

    public function dokumens(): HasMany
    {
        return $this->hasMany(UsulanDokumen::class)->latest();
    }

    public function ikus(): BelongsToMany
    {
        return $this->belongsToMany(Iku::class)->orderBy('kode_iku');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(UsulanLog::class)->orderBy('created_at')->orderBy('id');
    }

    // ---------- Scope ----------

    public function scopeMilikUnit(Builder $query, ?int $unitId): Builder
    {
        return $query->where('unit_id', $unitId);
    }

    // ---------- Aturan ----------

    public function isEditable(): bool
    {
        return $this->status->isEditable();
    }

    /** Hitung ulang total RAB dari item. */
    public function hitungTotalRab(): void
    {
        $this->total_rab = $this->rabItems()->sum('jumlah');
        $this->saveQuietly();
    }

    /**
     * Daftar syarat kelengkapan sebelum dikirim (US-13, US-16).
     *
     * @return list<array{label: string, ok: bool, step: string, detail: ?string}>
     */
    public function kelengkapan(): array
    {
        $kosong = fn (array $fields) => collect($fields)->filter(fn ($label, $f) => blank($this->{$f}))->values()->all();

        $info = $kosong(self::WAJIB_INFO);
        $kak = $kosong(self::WAJIB_KAK);
        $tanggalValid = ! $this->waktu_mulai || ! $this->waktu_selesai || $this->waktu_selesai->gte($this->waktu_mulai);
        $jumlahRab = $this->rabItems()->count();
        $jumlahIku = $this->ikus()->count();
        $jumlahDok = $this->dokumens()->count();

        return [
            ['label' => 'Informasi kegiatan lengkap', 'ok' => $info === [] && $tanggalValid, 'step' => 'info',
                'detail' => $info ? 'Belum diisi: '.implode(', ', $info) : (! $tanggalValid ? 'Waktu selesai sebelum waktu mulai' : null)],
            ['label' => 'KAK/TOR lengkap', 'ok' => $kak === [], 'step' => 'kak',
                'detail' => $kak ? 'Belum diisi: '.implode(', ', $kak) : null],
            ['label' => 'RAB berisi minimal 1 item dengan total di atas nol', 'ok' => $jumlahRab > 0 && $this->total_rab > 0, 'step' => 'rab',
                'detail' => $jumlahRab ? null : 'Belum ada item RAB'],
            ['label' => 'Minimal 1 IKU dipilih', 'ok' => $jumlahIku > 0, 'step' => 'iku', 'detail' => null],
            ['label' => 'Minimal 1 dokumen data dukung (PDF)', 'ok' => $jumlahDok > 0, 'step' => 'dokumen', 'detail' => null],
        ];
    }

    public function isLengkap(): bool
    {
        return collect($this->kelengkapan())->every('ok');
    }

    /** Nomor otomatis: USL/2026/TI/0001 (urut per unit per tahun). */
    public static function nomorBaru(Unit $unit, Tahun $tahun): string
    {
        return DB::transaction(function () use ($unit, $tahun) {
            $prefix = "USL/{$tahun->tahun}/{$unit->unit_id}/";
            $last = static::withTrashed()->where('nomor', 'like', $prefix.'%')->lockForUpdate()->max('nomor');
            $seq = $last ? ((int) substr($last, strlen($prefix))) + 1 : 1;

            return $prefix.str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
        });
    }
}
