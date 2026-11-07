@php use App\Support\Format; @endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>RKAKL {{ $usulan->nomor }}</title>
    <style>
        @page { margin: 32px 36px; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10.5px; color: #0f172a; }
        h1 { font-size: 14px; text-align: center; margin: 0; letter-spacing: .04em; }
        .sub { text-align: center; margin: 2px 0 14px; font-size: 11px; }
        .info { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        .info td { padding: 2px 4px; vertical-align: top; }
        .info td:first-child { width: 150px; color: #475569; }
        table.rab { width: 100%; border-collapse: collapse; }
        .rab th, .rab td { border: 1px solid #94a3b8; padding: 4px 6px; vertical-align: top; }
        .rab th { background: #e2e8f0; font-size: 9.5px; text-transform: uppercase; }
        .num { text-align: right; white-space: nowrap; }
        .akun td { background: #f1f5f9; font-weight: bold; }
        .total td { font-weight: bold; background: #e2e8f0; }
        .ttd { width: 100%; margin-top: 28px; }
        .ttd td { width: 50%; text-align: center; vertical-align: top; }
        .stempel { margin-top: 6px; display: inline-block; border: 1.5px solid #15803d; color: #15803d; padding: 3px 10px; font-weight: bold; font-size: 10px; }
    </style>
</head>
<body>
    <h1>RINCIAN KERTAS KERJA SATKER (RKAKL)</h1>
    <p class="sub">Tahun Anggaran {{ $usulan->tahun->tahun }}</p>

    <table class="info">
        <tr><td>Nomor usulan</td><td>: {{ $usulan->nomor }}</td></tr>
        <tr><td>Unit kerja</td><td>: {{ $usulan->unit->unit_id }} — {{ $usulan->unit->nama_unit }}</td></tr>
        <tr><td>Program</td><td>: {{ $usulan->program }}</td></tr>
        <tr><td>Kegiatan</td><td>: {{ $usulan->judul_kegiatan }}</td></tr>
        <tr><td>Hasil (outcome)</td><td>: {{ $usulan->hasil }}</td></tr>
        <tr><td>Output</td><td>: {{ $usulan->jenis_output }} ({{ Format::angka($usulan->volume_output) }} {{ $usulan->satuan_output }})</td></tr>
        <tr><td>Waktu pelaksanaan</td><td>: {{ Format::tanggal($usulan->waktu_mulai) }} s.d. {{ Format::tanggal($usulan->waktu_selesai) }}</td></tr>
        <tr><td>IKU terkait</td><td>: {{ $usulan->ikus->map(fn ($i) => $i->kode_iku.' '.$i->nama_iku)->implode('; ') }}</td></tr>
        <tr><td>Prioritas</td><td>: {{ $usulan->prioritas ?? '—' }}</td></tr>
    </table>

    <table class="rab">
        <thead>
            <tr><th style="width:70px">Kode</th><th>Uraian</th><th class="num" style="width:60px">Volume</th><th style="width:55px">Satuan</th><th class="num" style="width:95px">Harga satuan</th><th class="num" style="width:105px">Jumlah (Rp)</th></tr>
        </thead>
        <tbody>
            @foreach ($perAkun as $kode => $items)
                <tr class="akun">
                    <td>{{ $kode }}</td>
                    <td colspan="4">{{ $items->first()->akun?->nama_akun }}</td>
                    <td class="num">{{ number_format((float) $items->sum('jumlah'), 0, ',', '.') }}</td>
                </tr>
                @foreach ($items as $item)
                    <tr>
                        <td></td>
                        <td>- {{ $item->uraian }}</td>
                        <td class="num">{{ Format::angka($item->volume) }}</td>
                        <td>{{ $item->satuan }}</td>
                        <td class="num">{{ number_format((float) $item->harga_satuan, 0, ',', '.') }}</td>
                        <td class="num">{{ number_format((float) $item->jumlah, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            @endforeach
            <tr class="total"><td colspan="5">JUMLAH</td><td class="num">{{ number_format((float) $usulan->total_rab, 0, ',', '.') }}</td></tr>
        </tbody>
    </table>

    <table class="ttd">
        <tr>
            <td>
                Diusulkan oleh<br>{{ $usulan->unit->nama_unit }}
                <br><br><br><br>
                <strong>{{ $usulan->pembuat->nama_lengkap ?: $usulan->pembuat->name }}</strong><br>
                @if ($usulan->pembuat->nip) NIP {{ $usulan->pembuat->nip }} @endif
            </td>
            <td>
                Disahkan, {{ Format::tanggal($pengesahan?->created_at) }}<br>Direktur
                <br><span class="stempel">DISAHKAN DI SIPUA</span><br><br>
                <strong>{{ $pengesahan?->user->nama_lengkap ?: $pengesahan?->user->name }}</strong><br>
                @if ($pengesahan?->user->nip) NIP {{ $pengesahan->user->nip }} @endif
            </td>
        </tr>
    </table>
</body>
</html>
