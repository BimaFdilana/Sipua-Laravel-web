@php use App\Support\Format; @endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>RKT {{ $unit->unit_id }} {{ $tahun->tahun }}</title>
    <style>
        @page { margin: 30px 34px; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #0f172a; }
        h1 { font-size: 14px; text-align: center; margin: 0; letter-spacing: .04em; }
        .sub { text-align: center; margin: 2px 0 14px; font-size: 11px; }
        table.rkt { width: 100%; border-collapse: collapse; }
        .rkt th, .rkt td { border: 1px solid #94a3b8; padding: 4px 6px; vertical-align: top; }
        .rkt th { background: #e2e8f0; font-size: 9px; text-transform: uppercase; }
        .num { text-align: right; white-space: nowrap; }
        .total td { font-weight: bold; background: #e2e8f0; }
        .kosong { text-align: center; color: #64748b; padding: 18px; }
        .meta { margin-top: 10px; font-size: 9px; color: #64748b; }
    </style>
</head>
<body>
    <h1>RENCANA KERJA TAHUNAN (RKT)</h1>
    <p class="sub">{{ $unit->nama_unit }} · Tahun Anggaran {{ $tahun->tahun }}</p>

    <table class="rkt">
        <thead>
            <tr>
                <th style="width:24px">No</th><th>Program / Kegiatan</th><th>Output</th><th>IKU</th>
                <th style="width:120px">Waktu</th><th style="width:50px">Prioritas</th><th class="num" style="width:100px">Anggaran (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($usulans as $u)
                <tr>
                    <td class="num">{{ $loop->iteration }}</td>
                    <td><strong>{{ $u->judul_kegiatan }}</strong><br><span style="color:#64748b">{{ $u->program }} · {{ $u->nomor }}</span></td>
                    <td>{{ $u->jenis_output }} ({{ Format::angka($u->volume_output) }} {{ $u->satuan_output }})</td>
                    <td>{{ $u->ikus->pluck('kode_iku')->implode(', ') }}</td>
                    <td>{{ Format::tanggal($u->waktu_mulai) }} – {{ Format::tanggal($u->waktu_selesai) }}</td>
                    <td>{{ $u->prioritas }}</td>
                    <td class="num">{{ number_format((float) $u->total_rab, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr><td colspan="7" class="kosong">Belum ada usulan yang disahkan untuk tahun {{ $tahun->tahun }}.</td></tr>
            @endforelse
            <tr class="total"><td colspan="6">TOTAL {{ $usulans->count() }} KEGIATAN</td><td class="num">{{ number_format((float) $usulans->sum('total_rab'), 0, ',', '.') }}</td></tr>
        </tbody>
    </table>

    <p class="meta">Dibuat {{ Format::tanggal($dicetak, true) }} WIB oleh {{ $user->name }} melalui SIPUA. Hanya memuat usulan berstatus Disahkan.</p>
</body>
</html>
