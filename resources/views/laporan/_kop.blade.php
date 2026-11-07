<div class="kop">
    <p>{{ config('app.name') }} · Aplikasi Pengajuan Usulan Anggaran</p>
    <h1>{{ $judul }}</h1>
    <table>
        @foreach ($filterLabel as $k => $v)
            <tr><td>{{ $k }}</td><td>: <strong>{{ $v }}</strong></td></tr>
        @endforeach
        <tr><td>Tanggal dibuat</td><td>: {{ \App\Support\Format::tanggal($dicetak, true) }} WIB oleh {{ $dicetakOleh }}</td></tr>
    </table>
</div>
