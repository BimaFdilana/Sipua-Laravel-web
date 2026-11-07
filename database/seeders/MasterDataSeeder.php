<?php

namespace Database\Seeders;

use App\Models\AkunBelanja;
use App\Models\Iku;
use App\Models\Tahun;
use App\Models\Unit;
use Illuminate\Database\Seeder;

/**
 * Master data contoh: unit kerja, tahun anggaran, akun belanja, dan IKU.
 */
class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        $units = [
            ['TI', 'Teknik Informatika', 'Jurusan'],
            ['KPL', 'Teknik Perkapalan', 'Jurusan'],
            ['TE', 'Teknik Elektro', 'Jurusan'],
            ['ADM', 'Administrasi Niaga', 'Jurusan'],
            ['TM', 'Teknik Mesin', 'Jurusan'],
            ['KMR', 'Kemaritiman', 'Jurusan'],
            ['BHS', 'Bahasa', 'Jurusan'],
            ['TS', 'Teknik Sipil', 'Jurusan'],
            ['PRC', 'Bagian Perencanaan', 'Bagian'],
            ['P3M', 'Pusat Penelitian dan Pengabdian Kepada Masyarakat', 'Unit'],
            ['P4MP', 'Pusat Pengembangan Pembelajaran dan Penjaminan Mutu Pendidikan', 'Unit'],
            ['SPI', 'Satuan Pengawas Internal', 'Unit'],
            ['SNT', 'Senat', 'Unit'],
            ['UPPS', 'UPA Perpustakaan', 'Unit'],
            ['UPKJ', 'UPA Komputer, Jaringan dan Sistem Informasi', 'Unit'],
            ['UPPP', 'UPA Perawatan dan Perbaikan', 'Unit'],
            ['UPUJ', 'UPA Uji Kompetensi', 'Unit'],
        ];

        foreach ($units as [$kode, $nama, $jenis]) {
            Unit::updateOrCreate(['unit_id' => $kode], ['nama_unit' => $nama, 'jenis' => $jenis]);
        }

        Tahun::updateOrCreate(['tahun' => 2025], ['status' => false, 'pagu' => 2_100_000_000]);
        Tahun::updateOrCreate(['tahun' => 2026], ['status' => true, 'pagu' => 2_500_000_000]);
        Tahun::updateOrCreate(['tahun' => 2027], ['status' => false]);

        $akun = [
            ['521211', 'Belanja Bahan', 'Barang', 'Pembelian bahan habis pakai kegiatan'],
            ['521213', 'Honorarium Output Kegiatan', 'Barang', 'Honor narasumber, panitia, dan pelaksana kegiatan'],
            ['524111', 'Belanja Perjalanan Dinas Biasa', 'Barang', 'Perjalanan dinas dalam rangka pelaksanaan kegiatan'],
            ['532111', 'Belanja Modal Peralatan dan Mesin', 'Modal', 'Pengadaan peralatan dan mesin'],
        ];

        foreach ($akun as [$kode, $nama, $jenis, $ket]) {
            AkunBelanja::updateOrCreate(
                ['kode_akun' => $kode],
                ['nama_akun' => $nama, 'jenis_belanja' => $jenis, 'keterangan' => $ket]
            );
        }

        Iku::updateOrCreate(['kode_iku' => 'IKU1'], [
            'nama_iku' => 'Angka Efisiensi Edukasi Perguruan Tinggi (AEE PT)',
            'sifat' => 'Wajib',
            'tahun' => 2026,
            'target' => '80%',
            'deskripsi' => 'Mengukur tingkat keberhasilan mahasiswa menyelesaikan studi tepat waktu sesuai masa studi standar, dibandingkan dengan total mahasiswa yang masuk pada periode tertentu.',
            'kriteria' => "1. Mahasiswa lulus tepat waktu sesuai masa tempuh kurikulum standar.\n2. Berlaku untuk program pendidikan D1 sampai Doktor Terapan sesuai jenjang yang ada di perguruan tinggi.",
        ]);
    }
}
