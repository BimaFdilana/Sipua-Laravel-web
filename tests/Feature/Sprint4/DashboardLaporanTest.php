<?php

use App\Models\Tahun;
use App\Models\Unit;
use App\Models\User;
use App\Models\Usulan;
use App\Services\UsulanWorkflow;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DemoUsulanSeeder;

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);
    $this->seed(DemoUsulanSeeder::class);

    $this->u = fn (string $role) => User::where('role', $role)->first();

    // Bawa usulan "di_wadir" sampai disahkan supaya ada data final.
    $wf = app(UsulanWorkflow::class);
    $this->disahkan = Usulan::where('status', 'di_wadir')->first();
    $wf->verifikasi($this->disahkan, ($this->u)('wadir'), 'teruskan', 'OK', 'di_wadir');
    $wf->verifikasi($this->disahkan->fresh(), ($this->u)('spi'), 'teruskan', 'OK', 'di_spi');
    $wf->verifikasi($this->disahkan->fresh(), ($this->u)('direktur'), 'sahkan', null, 'di_direktur');
    $this->disahkan->refresh();
});

// ---------- Dashboard ----------

test('US-26 dashboard admin menampilkan jumlah pengguna, unit kerja, dan IKU', function () {
    $this->actingAs(($this->u)('admin'))->get('/dashboard')
        ->assertOk()
        ->assertSeeInOrder(['Pengguna', (string) User::count()])
        ->assertSeeInOrder(['Unit kerja', (string) Unit::count()])
        ->assertSee('IKU');
});

test('US-27 dashboard pengusul: jumlah per status, total nilai, IKU, kriteria prioritas, grafik pagu', function () {
    $res = $this->actingAs(($this->u)('pengusul'))->get('/dashboard')->assertOk();

    $res->assertSee('Total usulan')->assertSee('Draft')->assertSee('Perlu perbaikan')->assertSee('Disahkan')
        ->assertSee('IKU PTN')->assertSee('Kriteria prioritas')
        ->assertSee('Pagu dan usulan per tahun')
        ->assertSee('Rp 2.500.000.000'); // pagu 2026 di tabel alternatif grafik

    $kpi = $res->viewData('kpi');
    expect($kpi[0]['jumlah'])->toBe(4)                               // semua usulan unit
        ->and($kpi[1]['jumlah'])->toBe(1)                             // draft
        ->and($kpi[3]['jumlah'])->toBe(1)                             // perlu perbaikan
        ->and($kpi[4]['jumlah'])->toBe(1)                             // disahkan
        ->and($kpi[0]['nilai'])->toBe((float) Usulan::sum('total_rab'));
});

test('US-29 dashboard perencanaan: masuk, belum diverifikasi, dikembalikan, diteruskan, rekap unit, filter tahun', function () {
    $res = $this->actingAs(($this->u)('perencanaan'))->get('/dashboard')->assertOk();
    $kpi = collect($res->viewData('kpi'))->keyBy('label');

    expect($kpi['Usulan masuk']['jumlah'])->toBe(3)     // draft tidak dihitung
        ->and($kpi['Belum diverifikasi']['jumlah'])->toBe(1)
        ->and($kpi['Dikembalikan']['jumlah'])->toBe(1)
        ->and($kpi['Diteruskan']['jumlah'])->toBe(1);

    $res->assertSee('Rekap per unit kerja')->assertSee('Teknik Informatika')->assertSee('Berdasarkan prioritas');

    $tahun2025 = Tahun::firstWhere('tahun', 2025);
    $kosong = $this->get("/dashboard?tahun={$tahun2025->id}")->assertOk()->viewData('kpi');
    expect($kosong[0]['jumlah'])->toBe(0);
});

test('US-30 dashboard wadir hanya menghitung usulan yang pernah sampai ke Wadir', function () {
    $kpi = collect($this->actingAs(($this->u)('wadir'))->get('/dashboard')->assertOk()->viewData('kpi'))->keyBy('label');

    expect($kpi['Masuk ke Wadir']['jumlah'])->toBe(1)
        ->and($kpi['Disetujui']['jumlah'])->toBe(1)
        ->and($kpi['Belum diverifikasi']['jumlah'])->toBe(0);
});

test('US-31 dashboard SPI dan US-32 dashboard Direktur', function () {
    $spi = collect($this->actingAs(($this->u)('spi'))->get('/dashboard')->assertOk()->viewData('kpi'))->keyBy('label');
    expect($spi['Masuk ke SPI']['jumlah'])->toBe(1)->and($spi['Sudah diverifikasi']['jumlah'])->toBe(1);

    $dir = collect($this->actingAs(($this->u)('direktur'))->get('/dashboard')->assertOk()->viewData('kpi'))->keyBy('label');
    expect($dir['Seluruh usulan']['jumlah'])->toBe(3)
        ->and($dir['Disahkan']['jumlah'])->toBe(1)
        ->and($dir['Telah melalui verifikasi']['jumlah'])->toBe(1)
        ->and($dir['Menunggu pengesahan']['jumlah'])->toBe(0);
});

// ---------- Laporan ----------

test('US-33 laporan usulan menampilkan kolom lengkap dan bisa difilter', function () {
    $this->actingAs(($this->u)('perencanaan'))->get('/laporan')->assertRedirect('/laporan/usulan');

    $res = $this->get('/laporan/usulan')->assertOk()
        ->assertSee('Teknik Informatika')->assertSee('IKU1')->assertSee('Disahkan')->assertSee('Tinggi');
    expect($res->viewData('rows'))->toHaveCount(3);

    expect($this->get('/laporan/usulan?status=disahkan')->viewData('rows'))->toHaveCount(1)
        ->and($this->get('/laporan/usulan?prioritas=Tinggi')->viewData('rows'))->toHaveCount(1)
        ->and($this->get('/laporan/usulan?unit='.Unit::firstWhere('unit_id', 'TM')->id)->viewData('rows'))->toHaveCount(0);
});

test('US-34 laporan prioritas dikelompokkan dengan subtotal, untuk Perencanaan, Wadir, Direktur', function () {
    foreach (['perencanaan', 'wadir', 'direktur'] as $role) {
        $res = $this->actingAs(($this->u)($role))->get('/laporan/prioritas')->assertOk()
            ->assertSee('Prioritas Tinggi')->assertSee('Subtotal prioritas Tinggi');
        expect($res->viewData('kelompok')['Tinggi'])->toHaveCount(1);
    }

    $this->actingAs(($this->u)('wadir'))->get('/laporan/usulan')->assertForbidden();
    $this->actingAs(($this->u)('pengusul'))->get('/laporan/prioritas')->assertForbidden();
    $this->actingAs(($this->u)('spi'))->get('/laporan')->assertForbidden();
});

test('US-35 laporan IKU menampilkan usulan per IKU dan IKU yang belum punya usulan', function () {
    \App\Models\Iku::create(['kode_iku' => 'IKU9', 'nama_iku' => 'IKU tanpa usulan', 'sifat' => 'Pilihan', 'tahun' => 2026]);

    $res = $this->actingAs(($this->u)('perencanaan'))->get('/laporan/iku')->assertOk()
        ->assertSee('IKU1')->assertSee('IKU tanpa usulan')->assertSee('Belum ada usulan terkait');

    $iku1 = $res->viewData('perIku')->firstWhere('iku.kode_iku', 'IKU1');
    expect($iku1['jumlah'])->toBe(3)->and($res->viewData('tanpaUsulan'))->toBe(1);
});

test('US-36 laporan bisa dicetak, diunduh PDF dan Excel, mencantumkan tahun dan tanggal', function () {
    $this->actingAs(($this->u)('perencanaan'));

    $this->get('/laporan/usulan?format=cetak')->assertOk()
        ->assertSee('Tahun anggaran')->assertSee('2026')->assertSee('Tanggal dibuat')->assertSee('window.print()', false);

    $pdf = $this->get('/laporan/prioritas?format=pdf')->assertOk();
    expect($pdf->headers->get('content-type'))->toBe('application/pdf')
        ->and($pdf->headers->get('content-disposition'))->toContain('laporan-prioritas-2026');

    $xlsx = $this->get('/laporan/iku?format=xlsx')->assertOk();
    expect($xlsx->headers->get('content-type'))->toContain('spreadsheetml');
    expect(substr($xlsx->streamedContent(), 0, 2))->toBe('PK'); // file zip/xlsx valid
});

// ---------- US-28 RKAKL & RKT ----------

test('US-28 RKAKL hanya untuk usulan disahkan; RKT berisi usulan disahkan unit', function () {
    $pengusul = ($this->u)('pengusul');
    $draft = Usulan::where('status', 'draft')->first();

    $this->actingAs($pengusul)->get("/usulan/{$this->disahkan->id}")->assertSee('Cetak RKAKL');
    $this->get("/usulan/{$draft->id}/rkakl")->assertNotFound();

    $rkakl = $this->get("/usulan/{$this->disahkan->id}/rkakl")->assertOk();
    expect($rkakl->headers->get('content-type'))->toBe('application/pdf');

    $rkt = $this->get('/usulan/rkt')->assertOk();
    expect($rkt->headers->get('content-type'))->toBe('application/pdf')
        ->and($rkt->headers->get('content-disposition'))->toContain('rkt-ti-2026');

    // Pengusul unit lain tidak bisa mengunduh RKAKL unit ini
    $lain = User::factory()->role('pengusul')->create(['unit_id' => Unit::firstWhere('unit_id', 'TM')->id]);
    $this->actingAs($lain)->get("/usulan/{$this->disahkan->id}/rkakl")->assertForbidden();
});

test('angka dashboard sama dengan laporan (US-36 AC6)', function () {
    $this->actingAs(($this->u)('perencanaan'));
    $dash = $this->get('/dashboard')->viewData('total');
    $lap = $this->get('/laporan/usulan')->viewData('total');

    expect($lap)->toBe($dash['nilai']);
});

test('admin bisa mengisi pagu tahun anggaran', function () {
    $tahun = Tahun::firstWhere('tahun', 2027);
    $this->actingAs(($this->u)('admin'))->put("/tahun/{$tahun->id}", ['tahun' => 2027, 'pagu' => '3.000.000.000'])
        ->assertSessionHasNoErrors();

    expect((float) $tahun->fresh()->pagu)->toBe(3000000000.0);
});
