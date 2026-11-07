<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>{{ $judul }}</title>
    <style>
        body { font-family: system-ui, -apple-system, 'Segoe UI', sans-serif; margin: 24px; }
        .aksi { margin-bottom: 16px; }
        .aksi button { font: inherit; padding: 6px 14px; border-radius: 6px; border: 1px solid #cbd5e1; background: #fff; cursor: pointer; }
        @media print { .aksi { display: none; } body { margin: 0; } @page { size: A4 {{ $jenis === 'usulan' ? 'landscape' : 'portrait' }}; margin: 14mm; } }
    </style>
    @include('laporan._style')
</head>
<body onload="window.print()">
    <div class="aksi"><button type="button" onclick="window.print()">Cetak</button> <button type="button" onclick="window.close()">Tutup</button></div>
    @include('laporan._kop')
    @include('laporan._tabel')
</body>
</html>
