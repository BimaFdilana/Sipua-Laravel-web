{{--
    Tabel laporan yang sama untuk layar, halaman cetak, dan PDF (US-36 AC6).
    Gaya ada di laporan/_style (CSS polos, karena DomPDF tidak membaca Tailwind).
--}}
@php
    use App\Support\Format;
@endphp

@if ($jenis === 'usulan')
    <table class="lap">
        <thead>
            <tr>
                <th class="num">No</th><th>Nomor</th><th>Unit kerja</th><th>Kegiatan</th><th>IKU</th>
                <th>Prioritas</th><th>Status</th><th class="num">Nilai (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $u)
                <tr>
                    <td class="num">{{ $loop->iteration }}</td>
                    <td class="mono">{{ $u->nomor }}</td>
                    <td>{{ $u->unit->nama_unit }}</td>
                    <td>{{ $u->judul_kegiatan }}</td>
                    <td>{{ $u->ikus->pluck('kode_iku')->implode(', ') ?: '—' }}</td>
                    <td>{{ $u->prioritas ?? '—' }}</td>
                    <td>{{ $u->status->label() }}</td>
                    <td class="num">{{ number_format((float) $u->total_rab, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr><td colspan="8" class="kosong">Tidak ada usulan sesuai filter.</td></tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr><td colspan="7">Total {{ $rows->count() }} usulan</td><td class="num">{{ number_format($total, 0, ',', '.') }}</td></tr>
        </tfoot>
    </table>

@elseif ($jenis === 'prioritas')
    <table class="lap">
        <thead>
            <tr><th class="num">No</th><th>Nomor</th><th>Unit kerja</th><th>Kegiatan</th><th>IKU</th><th class="num">Nilai (Rp)</th></tr>
        </thead>
        @foreach ($kelompok as $p => $daftar)
            <tbody>
                <tr class="grup"><td colspan="6">Prioritas {{ $p }} — {{ $daftar->count() }} usulan</td></tr>
                @forelse ($daftar as $u)
                    <tr>
                        <td class="num">{{ $loop->iteration }}</td>
                        <td class="mono">{{ $u->nomor }}</td>
                        <td>{{ $u->unit->nama_unit }}</td>
                        <td>{{ $u->judul_kegiatan }}</td>
                        <td>{{ $u->ikus->pluck('kode_iku')->implode(', ') ?: '—' }}</td>
                        <td class="num">{{ number_format((float) $u->total_rab, 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="kosong">Tidak ada usulan.</td></tr>
                @endforelse
                <tr class="subtotal"><td colspan="5">Subtotal prioritas {{ $p }}</td><td class="num">{{ number_format((float) $daftar->sum('total_rab'), 0, ',', '.') }}</td></tr>
            </tbody>
        @endforeach
        <tfoot>
            <tr><td colspan="5">Total {{ $rows->count() }} usulan berprioritas</td><td class="num">{{ number_format($total, 0, ',', '.') }}</td></tr>
        </tfoot>
    </table>

@elseif ($jenis === 'iku')
    <table class="lap">
        <thead>
            <tr><th>Kode</th><th>Indikator / usulan terkait</th><th>Unit kerja</th><th class="num">Usulan</th><th class="num">Nilai (Rp)</th></tr>
        </thead>
        @forelse ($perIku as $r)
            <tbody>
                <tr @class(['grup', 'peringatan' => $r['jumlah'] === 0])>
                    <td class="mono">{{ $r['iku']->kode_iku }}</td>
                    <td>{{ $r['iku']->nama_iku }} <span class="muted">({{ $r['iku']->sifat }}@if ($r['iku']->target), target {{ $r['iku']->target }}@endif)</span></td>
                    <td>{{ $r['jumlah'] === 0 ? 'Belum ada usulan terkait' : '' }}</td>
                    <td class="num">{{ $r['jumlah'] }}</td>
                    <td class="num">{{ number_format($r['nilai'], 0, ',', '.') }}</td>
                </tr>
                @foreach ($r['usulans'] as $u)
                    <tr>
                        <td></td>
                        <td><span class="mono muted">{{ $u->nomor }}</span> {{ $u->judul_kegiatan }}</td>
                        <td>{{ $u->unit->nama_unit }}</td>
                        <td class="num"></td>
                        <td class="num">{{ number_format((float) $u->total_rab, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        @empty
            <tbody><tr><td colspan="5" class="kosong">Belum ada IKU untuk tahun ini.</td></tr></tbody>
        @endforelse
        <tfoot>
            <tr><td colspan="4">Total nilai usulan yang terkait IKU (tanpa hitung ganda)</td><td class="num">{{ number_format($total, 0, ',', '.') }}</td></tr>
        </tfoot>
    </table>
@endif
