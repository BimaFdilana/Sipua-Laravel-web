<?php

use App\Enums\StatusUsulan as S;
use App\Models\AkunBelanja;
use App\Models\Iku;
use App\Models\Tahun;
use App\Models\Unit;
use App\Models\User;
use App\Models\Usulan;
use App\Services\UsulanWorkflow;

beforeEach(function () {
    $this->tahun = Tahun::create(['tahun' => 2026, 'status' => true]);
    $this->unit = Unit::create(['unit_id' => 'TI', 'nama_unit' => 'Teknik Informatika', 'jenis' => 'Jurusan']);
    AkunBelanja::create(['kode_akun' => '521211', 'nama_akun' => 'Belanja Bahan', 'jenis_belanja' => 'Barang']);
    $this->iku = Iku::create(['kode_iku' => 'IKU1', 'nama_iku' => 'Lulusan bekerja', 'sifat' => 'Wajib', 'tahun' => 2026]);

    $this->pengusul = User::factory()->role('pengusul')->create(['unit_id' => $this->unit->id]);
    $this->perencanaan = User::factory()->role('perencanaan')->create();
    $this->wadir = User::factory()->role('wadir')->create();
    $this->spi = User::factory()->role('spi')->create();
    $this->direktur = User::factory()->role('direktur')->create();
});

/** Usulan lengkap yang sudah diajukan Pengusul (langsung lewat model, tanpa HTTP). */
function usulanDiajukan($test): Usulan
{
    $wf = app(UsulanWorkflow::class);
    $usulan = $wf->buat($test->pengusul, [
        'program' => 'Pendidikan', 'hasil' => 'Lulusan kompeten', 'judul_kegiatan' => 'Pelatihan Sertifikasi',
        'jenis_output' => 'Mahasiswa', 'volume_output' => 40, 'satuan_output' => 'Orang',
        'waktu_mulai' => '2026-03-01', 'waktu_selesai' => '2026-03-31',
    ] + collect(Usulan::WAJIB_KAK)->map(fn ($l) => "Isi {$l}")->all());

    $usulan->rabItems()->create(['kode_akun' => '521211', 'uraian' => 'ATK', 'volume' => 1, 'satuan' => 'Paket', 'harga_satuan' => 500000, 'jumlah' => 500000]);
    $usulan->hitungTotalRab();
    $usulan->ikus()->attach($test->iku);
    $usulan->dokumens()->create(['kategori' => 'lainnya', 'nama_asli' => 'a.pdf', 'path' => 'x/a.pdf', 'ukuran' => 1000, 'uploaded_by' => $test->pengusul->id]);

    return $wf->kirim($usulan, $test->pengusul);
}

function putuskan($test, User $user, Usulan $usulan, string $keputusan, ?string $catatan = null)
{
    return $test->actingAs($user)->post("/usulan/{$usulan->id}/verifikasi", [
        'keputusan' => $keputusan,
        'catatan' => $catatan,
        'status' => $usulan->fresh()->status->value,
    ]);
}

// ---------- US-15 / US-16 · Perencanaan melihat dan memeriksa ----------

test('US-15 Perencanaan melihat antrean usulan berstatus Diajukan beserta unit, judul, tahun, nilai', function () {
    $usulan = usulanDiajukan($this);
    $draft = app(UsulanWorkflow::class)->buat($this->pengusul, ['judul_kegiatan' => 'Masih draft']);

    $this->actingAs($this->perencanaan)->get('/verifikasi')
        ->assertOk()
        ->assertSee('Pelatihan Sertifikasi')
        ->assertSee('Teknik Informatika')
        ->assertSee('Rp 500.000')
        ->assertDontSee('Masih draft');

    $this->get("/usulan/{$usulan->id}")->assertOk();
    $this->get("/usulan/{$draft->id}")->assertForbidden();
});

test('US-16 detail untuk Perencanaan menampilkan RAB, KAK, dokumen, IKU, dan kelengkapan', function () {
    $usulan = usulanDiajukan($this);

    $this->actingAs($this->perencanaan)->get("/usulan/{$usulan->id}")
        ->assertOk()
        ->assertSee('Isi Latar belakang')
        ->assertSee('Rp 500.000')
        ->assertSee('a.pdf')
        ->assertSee('IKU1')
        ->assertSee('Kelengkapan dokumen')
        ->assertSee('Keputusan verifikasi');
});

// ---------- US-17 / US-19 / US-20 · Verifikasi & prioritas ----------

test('US-20 prioritas wajib sebelum diteruskan, dan tercatat siapa serta kapan', function () {
    $usulan = usulanDiajukan($this);

    putuskan($this, $this->perencanaan, $usulan, 'teruskan')->assertSessionHasErrors('prioritas');
    expect($usulan->fresh()->status)->toBe(S::Diajukan);

    $this->post("/usulan/{$usulan->id}/prioritas", ['prioritas' => 'Sangat'])->assertSessionHasErrors('prioritas');
    $this->post("/usulan/{$usulan->id}/prioritas", ['prioritas' => 'Tinggi', 'alasan_prioritas' => 'Mendukung IKU wajib'])->assertSessionHasNoErrors();

    $usulan->refresh();
    $log = $usulan->logs()->where('aksi', 'prioritas')->first();
    expect($usulan->prioritas)->toBe('Tinggi')
        ->and($log->user_id)->toBe($this->perencanaan->id)
        ->and($log->catatan)->toContain('Mendukung IKU wajib');

    // US-20 AC5: prioritas tampil di daftar
    $this->get('/verifikasi')->assertSee('Tinggi');
});

test('US-17 Perencanaan meneruskan usulan ke Wadir dan keputusan tercatat', function () {
    $usulan = usulanDiajukan($this);
    $this->actingAs($this->perencanaan)->post("/usulan/{$usulan->id}/prioritas", ['prioritas' => 'Sedang']);

    putuskan($this, $this->perencanaan, $usulan, 'teruskan', 'Lengkap dan layak')->assertRedirect('/verifikasi');

    $log = $usulan->logs()->where('aksi', 'teruskan')->first();
    expect($usulan->fresh()->status)->toBe(S::DiWadir)
        ->and($log->user_id)->toBe($this->perencanaan->id)
        ->and($log->tahap)->toBe('perencanaan')
        ->and($log->catatan)->toBe('Lengkap dan layak')
        ->and($log->created_at)->not->toBeNull();
});

test('US-17 catatan wajib saat mengembalikan atau menolak', function (string $keputusan) {
    $usulan = usulanDiajukan($this);

    putuskan($this, $this->perencanaan, $usulan, $keputusan, '')->assertSessionHasErrors('catatan');
    expect($usulan->fresh()->status)->toBe(S::Diajukan);
})->with(['kembalikan', 'tolak']);

test('US-17 Perencanaan tidak bisa mengesahkan (aksi di luar tahapnya)', function () {
    $usulan = usulanDiajukan($this);

    putuskan($this, $this->perencanaan, $usulan, 'sahkan')->assertSessionHasErrors('keputusan');
});

// ---------- US-18 · Kembalikan untuk perbaikan ----------

test('US-18 usulan dikembalikan, Pengusul melihat catatan, memperbaiki, lalu mengirim ulang ke Perencanaan', function () {
    $usulan = usulanDiajukan($this);

    putuskan($this, $this->perencanaan, $usulan, 'kembalikan', 'Lampirkan SBM terbaru.')->assertRedirect();
    expect($usulan->fresh()->status)->toBe(S::PerluPerbaikan);

    $this->actingAs($this->pengusul)->get("/usulan/{$usulan->id}/ubah/info")
        ->assertOk()->assertSee('Lampirkan SBM terbaru.');

    $this->put("/usulan/{$usulan->id}/info", [
        'program' => 'Pendidikan', 'hasil' => 'Lulusan kompeten', 'judul_kegiatan' => 'Pelatihan Sertifikasi (revisi)',
        'jenis_output' => 'Mahasiswa', 'volume_output' => 40, 'satuan_output' => 'Orang',
        'waktu_mulai' => '2026-03-01', 'waktu_selesai' => '2026-03-31',
    ])->assertSessionHasNoErrors();

    $this->post("/usulan/{$usulan->id}/kirim")->assertRedirect("/usulan/{$usulan->id}");
    expect($usulan->fresh())->status->toBe(S::Diajukan)->judul_kegiatan->toBe('Pelatihan Sertifikasi (revisi)');
});

// ---------- US-21..US-25 · Wadir, SPI, Direktur ----------

test('alur lengkap Perencanaan → Wadir → SPI → Direktur sampai Disahkan', function () {
    $usulan = usulanDiajukan($this);
    $this->actingAs($this->perencanaan)->post("/usulan/{$usulan->id}/prioritas", ['prioritas' => 'Tinggi']);
    putuskan($this, $this->perencanaan, $usulan, 'teruskan');

    // US-21: Wadir melihat usulan beserta hasil Perencanaan dan prioritas
    $this->actingAs($this->wadir)->get('/verifikasi')->assertSee('Pelatihan Sertifikasi');
    $this->get("/usulan/{$usulan->id}")->assertOk()->assertSee('Hasil verifikasi')->assertSee('Tinggi');

    // US-22: Wadir menyetujui dengan rekomendasi
    putuskan($this, $this->wadir, $usulan, 'teruskan', 'Sesuai renstra');
    expect($usulan->fresh()->status)->toBe(S::DiSpi);

    // US-23 / US-24: SPI
    $this->actingAs($this->spi)->get("/usulan/{$usulan->id}")->assertOk()->assertSee('Sesuai renstra');
    putuskan($this, $this->spi, $usulan, 'teruskan', 'Patuh');
    expect($usulan->fresh()->status)->toBe(S::DiDirektur);

    // US-25: Direktur mengesahkan
    $this->actingAs($this->direktur)->get('/verifikasi')->assertSee('Pelatihan Sertifikasi');
    putuskan($this, $this->direktur, $usulan, 'sahkan')->assertRedirect();

    $usulan->refresh();
    expect($usulan->status)->toBe(S::Disahkan)
        ->and($usulan->logs()->pluck('status_ke')->map->value->all())
        ->toBe(['draft', 'diajukan', 'diajukan', 'di_wadir', 'di_spi', 'di_direktur', 'disahkan']);

    // Pengusul melihat status akhir; usulan terkunci untuk semua
    $this->actingAs($this->pengusul)->get('/usulan')->assertSee('Disahkan');
    $this->get("/usulan/{$usulan->id}/ubah/info")->assertForbidden();
    putuskan($this, $this->direktur, $usulan, 'tolak', 'x')->assertForbidden();
});

test('US-22 / US-24 Wadir dan SPI bisa mengembalikan atau menolak', function (string $peran, string $keputusan, S $hasil) {
    $usulan = usulanDiajukan($this);
    $this->actingAs($this->perencanaan)->post("/usulan/{$usulan->id}/prioritas", ['prioritas' => 'Rendah']);
    putuskan($this, $this->perencanaan, $usulan, 'teruskan');
    if ($peran === 'spi') {
        putuskan($this, $this->wadir, $usulan, 'teruskan');
    }

    putuskan($this, $this->{$peran}, $usulan, $keputusan, 'Catatan dari '.$peran)->assertRedirect();

    expect($usulan->fresh()->status)->toBe($hasil);
    $this->actingAs($this->pengusul)->get("/usulan/{$usulan->id}")->assertSee('Catatan dari '.$peran);
})->with([
    ['wadir', 'kembalikan', S::PerluPerbaikan],
    ['wadir', 'tolak', S::Ditolak],
    ['spi', 'kembalikan', S::PerluPerbaikan],
    ['spi', 'tolak', S::Ditolak],
]);

test('US-25 Direktur bisa menolak; Direktur tidak bisa mengembalikan', function () {
    $usulan = usulanDiajukan($this);
    $this->actingAs($this->perencanaan)->post("/usulan/{$usulan->id}/prioritas", ['prioritas' => 'Sedang']);
    putuskan($this, $this->perencanaan, $usulan, 'teruskan');
    putuskan($this, $this->wadir, $usulan, 'teruskan');
    putuskan($this, $this->spi, $usulan, 'teruskan');

    putuskan($this, $this->direktur, $usulan, 'kembalikan', 'x')->assertSessionHasErrors('keputusan');
    putuskan($this, $this->direktur, $usulan, 'tolak', 'Anggaran tidak tersedia')->assertRedirect();
    expect($usulan->fresh()->status)->toBe(S::Ditolak);
});

// ---------- Hak akses antar tahap ----------

test('verifikator tidak bisa memproses usulan di luar tahapnya', function () {
    $usulan = usulanDiajukan($this);

    putuskan($this, $this->wadir, $usulan, 'teruskan')->assertForbidden();
    putuskan($this, $this->direktur, $usulan, 'tolak', 'x')->assertForbidden();
    $this->actingAs($this->pengusul)->post("/usulan/{$usulan->id}/verifikasi", ['keputusan' => 'teruskan', 'status' => 'diajukan'])->assertForbidden();
    $this->actingAs($this->wadir)->post("/usulan/{$usulan->id}/prioritas", ['prioritas' => 'Tinggi'])->assertForbidden();

    expect($usulan->fresh()->status)->toBe(S::Diajukan);
});

test('Wadir dan SPI tidak bisa membuka usulan yang belum sampai ke tahapnya', function () {
    $usulan = usulanDiajukan($this);

    $this->actingAs($this->wadir)->get("/usulan/{$usulan->id}")->assertForbidden();
    $this->actingAs($this->spi)->get("/usulan/{$usulan->id}")->assertForbidden();
    $this->actingAs($this->direktur)->get("/usulan/{$usulan->id}")->assertOk();
});

test('keputusan ganda dicegah jika usulan sudah diproses di tab lain', function () {
    $usulan = usulanDiajukan($this);
    $this->actingAs($this->perencanaan)->post("/usulan/{$usulan->id}/prioritas", ['prioritas' => 'Tinggi']);
    putuskan($this, $this->perencanaan, $usulan, 'teruskan');

    // Halaman lama masih membawa status "diajukan"
    $this->actingAs($this->wadir)->post("/usulan/{$usulan->id}/verifikasi", [
        'keputusan' => 'teruskan', 'status' => 'diajukan',
    ])->assertSessionHasErrors('keputusan');

    expect($usulan->fresh()->status)->toBe(S::DiWadir);
});

test('riwayat menampilkan usulan yang sudah diproses verifikator', function () {
    $usulan = usulanDiajukan($this);
    putuskan($this, $this->perencanaan, $usulan, 'kembalikan', 'Perbaiki');

    $this->actingAs($this->perencanaan)->get('/verifikasi')->assertDontSee('Pelatihan Sertifikasi');
    $this->get('/verifikasi?tab=riwayat')->assertSee('Pelatihan Sertifikasi')->assertSee('Perlu perbaikan');
});
