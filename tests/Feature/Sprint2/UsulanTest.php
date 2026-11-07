<?php

use App\Enums\StatusUsulan;
use App\Models\AkunBelanja;
use App\Models\Iku;
use App\Models\Tahun;
use App\Models\Unit;
use App\Models\User;
use App\Models\Usulan;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('local');

    $this->tahun = Tahun::create(['tahun' => 2026, 'status' => true]);
    $this->unit = Unit::create(['unit_id' => 'TI', 'nama_unit' => 'Teknik Informatika', 'jenis' => 'Jurusan']);
    $this->unitLain = Unit::create(['unit_id' => 'TM', 'nama_unit' => 'Teknik Mesin', 'jenis' => 'Jurusan']);
    $this->pengusul = User::factory()->role('pengusul')->create(['unit_id' => $this->unit->id]);
    $this->akun = AkunBelanja::create(['kode_akun' => '521211', 'nama_akun' => 'Belanja Bahan', 'jenis_belanja' => 'Barang']);
    $this->iku = Iku::create(['kode_iku' => 'IKU1', 'nama_iku' => 'Lulusan bekerja', 'sifat' => 'Wajib', 'tahun' => 2026]);
});

function infoLengkap(array $override = []): array
{
    return array_merge([
        'program' => 'Program Pendidikan Tinggi',
        'hasil' => 'Meningkatnya kompetensi lulusan',
        'judul_kegiatan' => 'Pelatihan Sertifikasi Kompetensi',
        'jenis_output' => 'Mahasiswa tersertifikasi',
        'volume_output' => 40,
        'satuan_output' => 'Orang',
        'waktu_mulai' => '2026-03-01',
        'waktu_selesai' => '2026-03-31',
    ], $override);
}

function kakLengkap(): array
{
    return collect(Usulan::WAJIB_KAK)->map(fn ($label) => "Isi {$label}")->all();
}

/** Buat usulan lengkap lewat HTTP seperti yang dilakukan Pengusul. */
function usulanLengkap($test): Usulan
{
    $test->actingAs($test->pengusul)->post('/usulan', infoLengkap());
    $usulan = Usulan::latest('id')->first();

    $test->put("/usulan/{$usulan->id}/kak", kakLengkap());
    $test->put("/usulan/{$usulan->id}/rab", ['items' => [
        ['kode_akun' => '521211', 'uraian' => 'ATK', 'volume' => 2, 'satuan' => 'Paket', 'harga_satuan' => 150000],
    ]]);
    $test->put("/usulan/{$usulan->id}/iku", ['ikus' => [$test->iku->id]]);
    $test->post("/usulan/{$usulan->id}/dokumen", [
        'kategori' => 'referensi_harga',
        'file' => UploadedFile::fake()->create('sbm.pdf', 200, 'application/pdf'),
    ]);

    return $usulan->fresh();
}

// ---------- US-08 · Buat usulan ----------

test('US-08 form usulan menampilkan tahun aktif dan unit kerja pengguna', function () {
    $this->actingAs($this->pengusul)->get('/usulan/buat')
        ->assertOk()->assertSee('2026')->assertSee('Teknik Informatika');
});

test('US-08 draft dibuat untuk unit pengguna, tahun aktif, dengan nomor otomatis', function () {
    $this->actingAs($this->pengusul)->post('/usulan', ['judul_kegiatan' => 'Workshop Kurikulum', 'aksi' => 'lanjut'])
        ->assertRedirect();

    $usulan = Usulan::first();
    expect($usulan)
        ->unit_id->toBe($this->unit->id)
        ->tahun_id->toBe($this->tahun->id)
        ->user_id->toBe($this->pengusul->id)
        ->status->toBe(StatusUsulan::Draft)
        ->nomor->toBe('USL/2026/TI/0001');

    $this->post('/usulan', ['judul_kegiatan' => 'Kedua']);
    expect(Usulan::latest('id')->first()->nomor)->toBe('USL/2026/TI/0002');
});

test('US-08 judul wajib dan tanggal divalidasi terhadap tahun anggaran', function () {
    $this->actingAs($this->pengusul)->post('/usulan', [])->assertSessionHasErrors('judul_kegiatan');

    $this->post('/usulan', infoLengkap(['waktu_mulai' => '2025-12-01']))->assertSessionHasErrors('waktu_mulai');
    $this->post('/usulan', infoLengkap(['waktu_selesai' => '2026-02-01']))->assertSessionHasErrors('waktu_selesai');
});

test('US-08 pengusul tanpa unit kerja tidak bisa membuat usulan', function () {
    $tanpaUnit = User::factory()->role('pengusul')->create(['unit_id' => null]);

    $this->actingAs($tanpaUnit)->post('/usulan', ['judul_kegiatan' => 'X'])->assertForbidden();
});

test('US-08 semua langkah wizard bisa dibuka', function (string $step) {
    $usulan = usulanLengkap($this);

    $this->get("/usulan/{$usulan->id}/ubah/{$step}")->assertOk();
})->with(['info', 'kak', 'rab', 'iku', 'dokumen']);

test('US-08 pengusul bisa mengubah dan menghapus draft', function () {
    $this->actingAs($this->pengusul)->post('/usulan', ['judul_kegiatan' => 'Lama']);
    $usulan = Usulan::first();

    $this->put("/usulan/{$usulan->id}/info", infoLengkap(['judul_kegiatan' => 'Baru']))->assertRedirect();
    expect($usulan->fresh()->judul_kegiatan)->toBe('Baru');

    $this->delete("/usulan/{$usulan->id}")->assertRedirect('/usulan');
    expect(Usulan::count())->toBe(0);
});

// ---------- US-09 · RAB ----------

test('US-09 jumlah per item dan total RAB dihitung di server', function () {
    $this->actingAs($this->pengusul)->post('/usulan', ['judul_kegiatan' => 'X']);
    $usulan = Usulan::first();

    $this->put("/usulan/{$usulan->id}/rab", ['items' => [
        ['kode_akun' => '521211', 'uraian' => 'Kertas', 'volume' => 10, 'satuan' => 'Rim', 'harga_satuan' => 55000, 'jumlah' => 1],
        ['kode_akun' => '521211', 'uraian' => 'Tinta', 'volume' => 2.5, 'satuan' => 'Botol', 'harga_satuan' => 100000],
    ]])->assertRedirect();

    $usulan->refresh();
    expect($usulan->rabItems)->toHaveCount(2)
        ->and((float) $usulan->rabItems[0]->jumlah)->toBe(550000.0)
        ->and((float) $usulan->rabItems[1]->jumlah)->toBe(250000.0)
        ->and((float) $usulan->total_rab)->toBe(800000.0);

    // Ubah & hapus: kirim ulang daftar tanpa baris kedua.
    $this->put("/usulan/{$usulan->id}/rab", ['items' => [
        ['kode_akun' => '521211', 'uraian' => 'Kertas', 'volume' => 20, 'satuan' => 'Rim', 'harga_satuan' => 55000],
    ]]);
    expect((float) $usulan->fresh()->total_rab)->toBe(1100000.0)->and($usulan->rabItems()->count())->toBe(1);
});

test('US-09 akun belanja harus dari master data dan aktif, angka divalidasi', function () {
    AkunBelanja::create(['kode_akun' => '999999', 'nama_akun' => 'Nonaktif', 'jenis_belanja' => 'Barang', 'is_active' => false]);
    $this->actingAs($this->pengusul)->post('/usulan', ['judul_kegiatan' => 'X']);
    $usulan = Usulan::first();

    $this->put("/usulan/{$usulan->id}/rab", ['items' => [
        ['kode_akun' => '999999', 'uraian' => 'A', 'volume' => 1, 'satuan' => 'X', 'harga_satuan' => 1],
        ['kode_akun' => '521211', 'uraian' => '', 'volume' => 0, 'satuan' => 'X', 'harga_satuan' => -5],
    ]])->assertSessionHasErrors(['items.0.kode_akun', 'items.1.uraian', 'items.1.volume', 'items.1.harga_satuan']);
});

// ---------- US-10 · Data dukung ----------

test('US-10 hanya PDF maksimal 5 MB yang diterima dan tersimpan privat', function () {
    $this->actingAs($this->pengusul)->post('/usulan', ['judul_kegiatan' => 'X']);
    $usulan = Usulan::first();

    $this->post("/usulan/{$usulan->id}/dokumen", ['kategori' => 'lainnya', 'file' => UploadedFile::fake()->create('foto.jpg', 100, 'image/jpeg')])
        ->assertSessionHasErrors(['file' => 'File harus berformat PDF.']);

    $this->post("/usulan/{$usulan->id}/dokumen", ['kategori' => 'lainnya', 'file' => UploadedFile::fake()->create('besar.pdf', 6000, 'application/pdf')])
        ->assertSessionHasErrors(['file' => 'Ukuran file maksimal 5 MB.']);

    $this->post("/usulan/{$usulan->id}/dokumen", ['kategori' => 'referensi_harga', 'file' => UploadedFile::fake()->create('sbm.pdf', 300, 'application/pdf')])
        ->assertSessionHasNoErrors();

    $dok = $usulan->dokumens()->first();
    expect($dok->nama_asli)->toBe('sbm.pdf')->and($dok->usulan_id)->toBe($usulan->id);
    Storage::disk('local')->assertExists($dok->path);

    $this->get("/usulan/{$usulan->id}/dokumen/{$dok->id}")->assertOk();

    $this->delete("/usulan/{$usulan->id}/dokumen/{$dok->id}")->assertRedirect();
    Storage::disk('local')->assertMissing($dok->path);
});

test('US-10 dokumen unit lain tidak bisa diunduh', function () {
    $usulan = usulanLengkap($this);
    $dok = $usulan->dokumens()->first();
    $lain = User::factory()->role('pengusul')->create(['unit_id' => $this->unitLain->id]);

    $this->actingAs($lain)->get("/usulan/{$usulan->id}/dokumen/{$dok->id}")->assertForbidden();
});

// ---------- US-11 · IKU ----------

test('US-11 hanya IKU tahun anggaran usulan yang bisa dipilih, bisa lebih dari satu', function () {
    $iku2 = Iku::create(['kode_iku' => 'IKU2', 'nama_iku' => 'Dosen berkegiatan', 'sifat' => 'Pilihan', 'tahun' => 2026]);
    Tahun::create(['tahun' => 2027, 'status' => false]);
    $ikuLain = Iku::create(['kode_iku' => 'IKU1', 'nama_iku' => 'Tahun lain', 'sifat' => 'Wajib', 'tahun' => 2027]);

    $this->actingAs($this->pengusul)->post('/usulan', ['judul_kegiatan' => 'X']);
    $usulan = Usulan::first();

    $this->get("/usulan/{$usulan->id}/ubah/iku")->assertSee('Lulusan bekerja')->assertDontSee('Tahun lain');

    $this->put("/usulan/{$usulan->id}/iku", ['ikus' => [$ikuLain->id]])->assertSessionHasErrors('ikus.0');

    $this->put("/usulan/{$usulan->id}/iku", ['ikus' => [$this->iku->id, $iku2->id]])->assertSessionHasNoErrors();
    expect($usulan->ikus()->pluck('ikus.id')->sort()->values()->all())->toBe([$this->iku->id, $iku2->id]);
});

// ---------- US-12 · Draft ----------

test('US-12 draft belum lengkap tersimpan dan belum masuk verifikasi', function () {
    $this->actingAs($this->pengusul)->post('/usulan', ['judul_kegiatan' => 'Belum lengkap']);
    $usulan = Usulan::first();

    expect($usulan->status)->toBe(StatusUsulan::Draft)->and($usulan->isLengkap())->toBeFalse();

    $perencanaan = User::factory()->role('perencanaan')->create();
    $this->actingAs($perencanaan)->get("/usulan/{$usulan->id}")->assertForbidden();
});

// ---------- US-13 · Kirim ----------

test('US-13 usulan belum lengkap tidak bisa dikirim', function () {
    $this->actingAs($this->pengusul)->post('/usulan', ['judul_kegiatan' => 'X']);
    $usulan = Usulan::first();

    $this->post("/usulan/{$usulan->id}/kirim")->assertSessionHasErrors('kirim');
    expect($usulan->fresh()->status)->toBe(StatusUsulan::Draft);
});

test('US-13 usulan lengkap dikirim, waktu dicatat, terkunci, dan terlihat Perencanaan', function () {
    $usulan = usulanLengkap($this);
    expect($usulan->isLengkap())->toBeTrue();

    $this->post("/usulan/{$usulan->id}/kirim")
        ->assertRedirect("/usulan/{$usulan->id}")
        ->assertSessionHas('success');

    $usulan->refresh();
    expect($usulan->status)->toBe(StatusUsulan::Diajukan)
        ->and($usulan->submitted_at)->not->toBeNull();

    // Terkunci untuk Pengusul
    $this->get("/usulan/{$usulan->id}/ubah/info")->assertForbidden();
    $this->put("/usulan/{$usulan->id}/info", infoLengkap(['judul_kegiatan' => 'Diubah']))->assertForbidden();
    $this->put("/usulan/{$usulan->id}/rab", ['items' => []])->assertForbidden();
    $this->delete("/usulan/{$usulan->id}")->assertForbidden();
    expect($usulan->fresh()->judul_kegiatan)->toBe('Pelatihan Sertifikasi Kompetensi');

    // Terlihat oleh Perencanaan
    $perencanaan = User::factory()->role('perencanaan')->create();
    $this->actingAs($perencanaan)->get("/usulan/{$usulan->id}")->assertOk();
});

test('US-13 usulan yang sudah diajukan tidak bisa dikirim dua kali', function () {
    $usulan = usulanLengkap($this);
    $this->post("/usulan/{$usulan->id}/kirim");

    $this->post("/usulan/{$usulan->id}/kirim")->assertForbidden();
    expect($usulan->logs()->where('aksi', 'kirim')->count())->toBe(1);
});

// ---------- US-07 · Daftar ----------

test('US-07 pengusul hanya melihat usulan unitnya di tahun aktif', function () {
    usulanLengkap($this);
    $lain = User::factory()->role('pengusul')->create(['unit_id' => $this->unitLain->id]);
    $this->actingAs($lain)->post('/usulan', ['judul_kegiatan' => 'Usulan Teknik Mesin']);

    $this->actingAs($this->pengusul)->get('/usulan')
        ->assertOk()
        ->assertSee('Pelatihan Sertifikasi Kompetensi')
        ->assertSee('USL/2026/TI/0001')
        ->assertSee('Rp 300.000')
        ->assertDontSee('Usulan Teknik Mesin');

    $usulanLain = Usulan::firstWhere('unit_id', $this->unitLain->id);
    $this->get("/usulan/{$usulanLain->id}")->assertForbidden();
});

// ---------- US-14 · Detail & riwayat ----------

test('US-14 detail menampilkan info, RAB, IKU, dokumen, status, dan riwayat', function () {
    $usulan = usulanLengkap($this);
    $this->post("/usulan/{$usulan->id}/kirim");

    $this->get("/usulan/{$usulan->id}")
        ->assertOk()
        ->assertSee('Program Pendidikan Tinggi')
        ->assertSee('Rp 300.000')
        ->assertSee('IKU1')
        ->assertSee('sbm.pdf')
        ->assertSee('Diajukan')
        ->assertSee('Membuat usulan')
        ->assertSee('Mengirim usulan');

    expect($usulan->logs()->pluck('status_ke')->map->value->all())->toBe(['draft', 'diajukan']);
});

// ---------- Larangan hapus master data yang sudah dipakai ----------

test('master data yang sudah dipakai usulan tidak bisa dihapus', function () {
    $usulan = usulanLengkap($this);
    $admin = User::factory()->role('admin')->create();
    $this->tahun->update(['status' => false]);

    $this->actingAs($admin);
    $this->delete('/akun-belanja/521211')->assertSessionHas('error');
    $this->delete("/iku/{$this->iku->id}")->assertSessionHas('error');
    $this->delete("/tahun/{$this->tahun->id}")->assertSessionHas('error');
    $this->delete("/users/{$this->pengusul->id}")->assertSessionHas('error');

    expect(AkunBelanja::count())->toBe(1)->and(Iku::count())->toBe(1)->and(User::find($this->pengusul->id))->not->toBeNull();
});
