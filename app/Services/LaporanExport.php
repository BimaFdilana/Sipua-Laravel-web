<?php

namespace App\Services;

use App\Support\Format;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Writer\XLSX\Writer;
use Symfony\Component\HttpFoundation\StreamedResponse;

/** US-36: unduh laporan sebagai Excel (.xlsx), isi sama dengan tampilan layar. */
class LaporanExport
{
    public function xlsx(array $data, string $namaFile): StreamedResponse
    {
        return response()->streamDownload(function () use ($data) {
            $writer = new Writer;
            $writer->openToFile('php://output');

            $bold = (new Style)->setFontBold();
            $judul = (new Style)->setFontBold()->setFontSize(14);
            $uang = (new Style)->setFormat('#,##0');

            $writer->addRow(Row::fromValues([$data['judul']], $judul));
            foreach ($data['filterLabel'] as $k => $v) {
                $writer->addRow(Row::fromValues([$k, (string) $v]));
            }
            $writer->addRow(Row::fromValues(['Dicetak', Format::tanggal($data['dicetak'], true).' oleh '.$data['dicetakOleh']]));
            $writer->addRow(Row::fromValues([]));

            $tambah = function (array $nilai, ?Style $style = null) use ($writer, $uang) {
                $cells = array_map(fn ($v) => \OpenSpout\Common\Entity\Cell::fromValue($v, is_float($v) ? $uang : null), $nilai);
                $writer->addRow(new Row($cells, $style));
            };

            match ($data['jenis']) {
                'usulan' => $this->usulan($data, $tambah, $bold),
                'prioritas' => $this->prioritas($data, $tambah, $bold),
                'iku' => $this->iku($data, $tambah, $bold),
            };

            $writer->close();
        }, $namaFile, ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
    }

    private function usulan(array $data, callable $tambah, Style $bold): void
    {
        $tambah(['No', 'Nomor usulan', 'Unit kerja', 'Kegiatan', 'IKU', 'Prioritas', 'Status', 'Nilai (Rp)'], $bold);
        foreach ($data['rows'] as $i => $u) {
            $tambah([$i + 1, $u->nomor, $u->unit->nama_unit, $u->judul_kegiatan, $u->ikus->pluck('kode_iku')->implode(', '),
                $u->prioritas ?? '-', $u->status->label(), (float) $u->total_rab]);
        }
        $tambah(['', '', '', '', '', '', 'Total', $data['total']], $bold);
    }

    private function prioritas(array $data, callable $tambah, Style $bold): void
    {
        $tambah(['Prioritas', 'No', 'Nomor usulan', 'Unit kerja', 'Kegiatan', 'IKU', 'Nilai (Rp)'], $bold);
        foreach ($data['kelompok'] as $p => $rows) {
            foreach ($rows as $i => $u) {
                $tambah([$p, $i + 1, $u->nomor, $u->unit->nama_unit, $u->judul_kegiatan, $u->ikus->pluck('kode_iku')->implode(', '), (float) $u->total_rab]);
            }
            $tambah(["Subtotal {$p}", '', $rows->count().' usulan', '', '', '', (float) $rows->sum('total_rab')], $bold);
        }
        $tambah(['Total', '', '', '', '', '', $data['total']], $bold);
    }

    private function iku(array $data, callable $tambah, Style $bold): void
    {
        $tambah(['Kode IKU', 'Indikator', 'Sifat', 'Nomor usulan', 'Unit kerja', 'Kegiatan', 'Nilai (Rp)'], $bold);
        foreach ($data['perIku'] as $r) {
            if ($r['usulans']->isEmpty()) {
                $tambah([$r['iku']->kode_iku, $r['iku']->nama_iku, $r['iku']->sifat, 'Belum ada usulan', '', '', 0.0]);
            }
            foreach ($r['usulans'] as $u) {
                $tambah([$r['iku']->kode_iku, $r['iku']->nama_iku, $r['iku']->sifat, $u->nomor, $u->unit->nama_unit, $u->judul_kegiatan, (float) $u->total_rab]);
            }
            $tambah(["Subtotal {$r['iku']->kode_iku}", '', '', $r['jumlah'].' usulan', '', '', $r['nilai']], $bold);
        }
    }
}
