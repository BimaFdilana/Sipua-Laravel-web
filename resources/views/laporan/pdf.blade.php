<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>{{ $judul }}</title>
    <style>
        @page { margin: 28px 32px; }
        body { font-family: DejaVu Sans, sans-serif; }
        .footer { position: fixed; bottom: -14px; left: 0; right: 0; font-size: 9px; color: #94a3b8; }
    </style>
    @include('laporan._style')
</head>
<body>
    @include('laporan._kop')
    @include('laporan._tabel')
    <div class="footer">Dokumen dibuat otomatis oleh SIPUA.</div>
</body>
</html>
