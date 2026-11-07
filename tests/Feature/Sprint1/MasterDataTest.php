<?php

use App\Models\AkunBelanja;
use App\Models\Iku;
use App\Models\Tahun;
use App\Models\Unit;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->role('admin')->create();
    $this->actingAs($this->admin);
});

// ---------- US-01 · Unit kerja ----------

test('US-01 admin bisa menambah, mengubah, dan menghapus unit kerja', function () {
    $this->post('/unit', ['unit_id' => 'ti', 'nama_unit' => 'Teknik Informatika', 'jenis' => 'Jurusan', 'is_active' => 1])
        ->assertRedirect('/unit');

    $unit = Unit::firstWhere('unit_id', 'TI'); // kode dinormalkan ke huruf besar
    expect($unit)->not->toBeNull()->and($unit->is_active)->toBeTrue();

    $this->put("/unit/{$unit->id}", ['unit_id' => 'TI', 'nama_unit' => 'Informatika', 'jenis' => 'Jurusan', 'is_active' => 0])
        ->assertRedirect('/unit');
    expect($unit->fresh())->nama_unit->toBe('Informatika')->is_active->toBeFalse();

    $this->delete("/unit/{$unit->id}")->assertRedirect('/unit');
    expect(Unit::find($unit->id))->toBeNull();
});

test('US-01 kode unit tidak boleh duplikat', function () {
    Unit::create(['unit_id' => 'TI', 'nama_unit' => 'Teknik Informatika', 'jenis' => 'Jurusan']);

    $this->post('/unit', ['unit_id' => 'TI', 'nama_unit' => 'Lain', 'jenis' => 'Jurusan'])
        ->assertSessionHasErrors(['unit_id' => 'Kode unit sudah digunakan.']);
});

test('US-01 unit yang masih dipakai pengguna tidak bisa dihapus', function () {
    $unit = Unit::create(['unit_id' => 'TI', 'nama_unit' => 'Teknik Informatika', 'jenis' => 'Jurusan']);
    User::factory()->create(['unit_id' => $unit->id]);

    $this->delete("/unit/{$unit->id}")->assertSessionHas('error');
    expect(Unit::find($unit->id))->not->toBeNull();
});

test('US-01 hanya unit aktif yang muncul sebagai pilihan', function () {
    $aktif = Unit::create(['unit_id' => 'TI', 'nama_unit' => 'Aktif', 'jenis' => 'Jurusan', 'is_active' => true]);
    $nonaktif = Unit::create(['unit_id' => 'TX', 'nama_unit' => 'Nonaktif', 'jenis' => 'Jurusan', 'is_active' => false]);

    expect(Unit::options())->toHaveKey($aktif->id)->not->toHaveKey($nonaktif->id);
});

// ---------- US-01 · Akun belanja ----------

test('US-01 admin bisa mengelola akun belanja, kode unik dan tidak bisa diubah', function () {
    $this->post('/akun-belanja', ['kode_akun' => '521211', 'nama_akun' => 'Belanja Bahan', 'jenis_belanja' => 'Barang', 'is_active' => 1])
        ->assertRedirect('/akun-belanja');

    $this->post('/akun-belanja', ['kode_akun' => '521211', 'nama_akun' => 'Dobel', 'jenis_belanja' => 'Barang'])
        ->assertSessionHasErrors('kode_akun');

    $this->post('/akun-belanja', ['kode_akun' => '52A', 'nama_akun' => 'Salah', 'jenis_belanja' => 'Barang'])
        ->assertSessionHasErrors('kode_akun');

    $this->put('/akun-belanja/521211', ['kode_akun' => '999999', 'nama_akun' => 'Belanja Bahan Habis Pakai', 'jenis_belanja' => 'Barang', 'is_active' => 1])
        ->assertRedirect('/akun-belanja');

    expect(AkunBelanja::find('521211'))->nama_akun->toBe('Belanja Bahan Habis Pakai')
        ->and(AkunBelanja::find('999999'))->toBeNull();

    $this->delete('/akun-belanja/521211')->assertRedirect('/akun-belanja');
    expect(AkunBelanja::count())->toBe(0);
});

// ---------- US-02 · Tahun anggaran ----------

test('US-02 admin bisa menambah dan mengubah tahun anggaran', function () {
    $this->post('/tahun', ['tahun' => 2027])->assertRedirect('/tahun');
    $tahun = Tahun::firstWhere('tahun', 2027);
    expect($tahun->status)->toBeFalse();

    $this->post('/tahun', ['tahun' => 2027])->assertSessionHasErrors('tahun');
    $this->post('/tahun', ['tahun' => 1999])->assertSessionHasErrors('tahun');

    $this->put("/tahun/{$tahun->id}", ['tahun' => 2028, 'status' => 0])->assertRedirect('/tahun');
    expect($tahun->fresh()->tahun)->toBe(2028);
});

test('US-02 hanya satu tahun anggaran yang aktif', function () {
    $a = Tahun::create(['tahun' => 2026, 'status' => true]);
    $b = Tahun::create(['tahun' => 2027, 'status' => false]);

    $this->patch("/tahun/{$b->id}/aktifkan")->assertRedirect();

    expect($a->fresh()->status)->toBeFalse()
        ->and($b->fresh()->status)->toBeTrue()
        ->and(Tahun::where('status', true)->count())->toBe(1);

    $this->post('/tahun', ['tahun' => 2028, 'status' => 1]);
    expect(Tahun::aktif()->tahun)->toBe(2028)->and(Tahun::where('status', true)->count())->toBe(1);
});

test('US-02 tahun aktif tidak bisa dihapus, tahun nonaktif bisa', function () {
    $aktif = Tahun::create(['tahun' => 2026, 'status' => true]);
    $lama = Tahun::create(['tahun' => 2025, 'status' => false]);

    $this->delete("/tahun/{$aktif->id}")->assertSessionHas('error');
    $this->delete("/tahun/{$lama->id}")->assertRedirect('/tahun');

    expect(Tahun::pluck('tahun')->all())->toBe([2026]);
});

// ---------- US-04 · IKU ----------

test('US-04 admin bisa mengelola IKU termasuk sifat, tahun, dan target', function () {
    Tahun::create(['tahun' => 2026, 'status' => true]);
    Tahun::create(['tahun' => 2027, 'status' => false]);

    $data = ['kode_iku' => 'iku 1', 'nama_iku' => 'Lulusan bekerja', 'sifat' => 'Wajib', 'tahun' => 2026, 'target' => '80%'];
    $this->post('/iku', $data)->assertRedirect();

    $iku = Iku::firstWhere('kode_iku', 'IKU1');
    expect($iku)->sifat->toBe('Wajib')->tahun->toBe(2026)->target->toBe('80%');

    // Bug lama: update tidak menyimpan sifat, tahun, target.
    $this->put("/iku/{$iku->id}", ['kode_iku' => 'IKU1', 'nama_iku' => 'Lulusan bekerja', 'sifat' => 'Pilihan', 'tahun' => 2027, 'target' => '85%'])
        ->assertRedirect();
    expect($iku->fresh())->sifat->toBe('Pilihan')->tahun->toBe(2027)->target->toBe('85%');

    $this->delete("/iku/{$iku->id}")->assertRedirect();
    expect(Iku::count())->toBe(0);
});

test('US-04 kode IKU unik per tahun dan sifat harus Wajib atau Pilihan', function () {
    Tahun::create(['tahun' => 2026, 'status' => true]);
    Tahun::create(['tahun' => 2027, 'status' => false]);
    Iku::create(['kode_iku' => 'IKU1', 'nama_iku' => 'A', 'sifat' => 'Wajib', 'tahun' => 2026]);

    $this->post('/iku', ['kode_iku' => 'IKU1', 'nama_iku' => 'B', 'sifat' => 'Wajib', 'tahun' => 2026])
        ->assertSessionHasErrors('kode_iku');

    $this->post('/iku', ['kode_iku' => 'IKU1', 'nama_iku' => 'B', 'sifat' => 'Wajib', 'tahun' => 2027])
        ->assertSessionDoesntHaveErrors();

    $this->post('/iku', ['kode_iku' => 'IKU2', 'nama_iku' => 'C', 'sifat' => 'Lainnya', 'tahun' => 2026])
        ->assertSessionHasErrors('sifat');

    $this->post('/iku', ['kode_iku' => 'IKU3', 'nama_iku' => 'D', 'sifat' => 'Wajib', 'tahun' => 2030])
        ->assertSessionHasErrors('tahun');
});

test('halaman daftar dan form master data tampil tanpa error', function (string $url) {
    Tahun::create(['tahun' => 2026, 'status' => true]);
    Unit::create(['unit_id' => 'TI', 'nama_unit' => 'Teknik Informatika', 'jenis' => 'Jurusan']);

    $this->get($url)->assertOk();
})->with(['/unit', '/unit/create', '/akun-belanja', '/akun-belanja/create', '/tahun', '/tahun/create', '/iku', '/iku/create', '/users', '/users/create']);
