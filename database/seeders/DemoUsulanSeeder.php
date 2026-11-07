<?php

namespace Database\Seeders;

use App\Models\Iku;
use App\Models\User;
use App\Models\Usulan;
use App\Services\UsulanWorkflow;
use Illuminate\Database\Seeder;

/**
 * Data contoh usulan di berbagai tahap, untuk mencoba alur verifikasi.
 * Jalankan setelah DatabaseSeeder:  php artisan db:seed --class=DemoUsulanSeeder
 */
class DemoUsulanSeeder extends Seeder
{
    public function run(UsulanWorkflow $wf): void
    {
        $pengusul = User::where('role', 'pengusul')->firstOrFail();
        $perencanaan = User::where('role', 'perencanaan')->firstOrFail();
        $iku = Iku::first();

        $contoh = [
            ['Pelatihan Sertifikasi Kompetensi Mahasiswa', [['521213', 'Honor narasumber', 6, 'OJ', 900000], ['521211', 'Konsumsi peserta', 80, 'OK', 35000]], 'diajukan'],
            ['Pengadaan Peralatan Laboratorium Jaringan', [['532111', 'Switch managed 24 port', 4, 'Unit', 12500000], ['532111', 'Rak server 42U', 1, 'Unit', 18000000]], 'di_wadir'],
            ['Workshop Penyusunan Kurikulum OBE', [['521211', 'Bahan workshop', 30, 'Paket', 75000], ['524111', 'Perjalanan dinas narasumber', 2, 'OK', 4500000]], 'perlu_perbaikan'],
            ['Kunjungan Industri Mahasiswa Semester 5', [['524111', 'Sewa bus', 2, 'Unit', 6000000]], 'draft'],
        ];

        foreach ($contoh as [$judul, $rab, $target]) {
            $usulan = $wf->buat($pengusul, [
                'program' => 'Program Pendidikan Tinggi Vokasi',
                'hasil' => 'Meningkatnya kualitas lulusan',
                'judul_kegiatan' => $judul,
                'jenis_output' => 'Kegiatan terlaksana',
                'volume_output' => 1,
                'satuan_output' => 'Kegiatan',
                'waktu_mulai' => now()->year.'-04-01',
                'waktu_selesai' => now()->year.'-04-30',
            ] + collect(Usulan::WAJIB_KAK)->map(fn ($label) => "{$label} untuk kegiatan {$judul}.")->all());

            foreach ($rab as $i => [$akun, $uraian, $vol, $sat, $harga]) {
                $usulan->rabItems()->create(compact('uraian') + [
                    'kode_akun' => $akun, 'volume' => $vol, 'satuan' => $sat,
                    'harga_satuan' => $harga, 'jumlah' => $vol * $harga, 'urutan' => $i,
                ]);
            }
            $usulan->hitungTotalRab();

            if ($target === 'draft') {
                continue;
            }

            $usulan->ikus()->attach($iku);
            $usulan->dokumens()->create([
                'kategori' => 'referensi_harga', 'nama_asli' => 'contoh-sbm.pdf', 'path' => 'demo/contoh-sbm.pdf',
                'ukuran' => 120000, 'uploaded_by' => $pengusul->id,
            ]);
            $wf->kirim($usulan, $pengusul);

            if ($target === 'di_wadir') {
                $wf->tetapkanPrioritas($usulan, $perencanaan, 'Tinggi', 'Mendukung IKU wajib.');
                $wf->verifikasi($usulan, $perencanaan, 'teruskan', 'Dokumen lengkap.', 'diajukan');
            } elseif ($target === 'perlu_perbaikan') {
                $wf->verifikasi($usulan, $perencanaan, 'kembalikan', 'Lampirkan referensi harga perjalanan dinas sesuai SBM terbaru.', 'diajukan');
            }
        }

    }
}
