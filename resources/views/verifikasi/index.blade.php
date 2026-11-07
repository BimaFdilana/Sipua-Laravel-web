@php
    use App\Models\Usulan;
    use App\Support\Format;

    $judul = [
        'perencanaan' => 'Usulan yang diajukan unit kerja dan menunggu pemeriksaan Perencanaan.',
        'wadir' => 'Usulan yang sudah diverifikasi dan diprioritaskan Perencanaan.',
        'spi' => 'Usulan yang disetujui Wadir dan menunggu telaah SPI.',
        'direktur' => 'Usulan yang lolos verifikasi SPI dan menunggu pengesahan.',
    ][auth()->user()->role] ?? '';
@endphp

<x-app-layout>
    <x-slot name="title">Antrean Verifikasi</x-slot>
    <x-slot name="header"><span class="text-sm text-slate-500">Verifikasi · {{ auth()->user()->roleLabel() }}</span></x-slot>

    <div class="mx-auto max-w-7xl space-y-5 px-4 py-6 sm:px-6 lg:px-8">
        <x-ui.page-header :title="auth()->user()->hasRole('direktur') ? 'Pengesahan Usulan' : 'Antrean Verifikasi'" :description="$judul" />

        {{-- Tab --}}
        <nav class="flex gap-1 border-b border-slate-200" aria-label="Tab">
            @foreach (['antrean' => 'Perlu diproses', 'riwayat' => 'Sudah diproses'] as $key => $label)
                <a href="{{ request()->fullUrlWithQuery(['tab' => $key, 'page' => null]) }}"
                    @if ($tab === $key) aria-current="page" @endif
                    @class([
                        '-mb-px inline-flex items-center gap-2 border-b-2 px-3 py-2 text-sm font-medium',
                        'border-blue-600 text-blue-700' => $tab === $key,
                        'border-transparent text-slate-500 hover:text-slate-800' => $tab !== $key,
                    ])>
                    {{ $label }}
                    @if ($key === 'antrean')
                        <span @class(['rounded-full px-2 py-0.5 text-xs tabular-nums', 'bg-blue-600 text-white' => $antreanCount, 'bg-slate-100 text-slate-500' => ! $antreanCount])>{{ $antreanCount }}</span>
                    @endif
                </a>
            @endforeach
        </nav>

        <x-ui.search placeholder="Cari judul atau nomor…">
            <input type="hidden" name="tab" value="{{ $tab }}">
            <x-ui.select name="tahun" :options="$tahunOptions" :value="$tahunId" placeholder="Semua tahun" class="sm:w-32" />
            <x-ui.select name="unit" :options="$unitOptions" :value="request('unit')" placeholder="Semua unit" class="sm:w-56" />
            <x-ui.select name="prioritas" :options="array_combine(array_keys(Usulan::PRIORITAS), array_keys(Usulan::PRIORITAS))" :value="request('prioritas')" placeholder="Semua prioritas" class="sm:w-40" />
        </x-ui.search>

        <x-ui.table :paginator="$usulans" :empty="$tab === 'antrean' ? 'Tidak ada usulan yang menunggu. Antrean bersih.' : 'Belum ada usulan yang Anda proses.'">
            <x-slot name="head">
                <th class="px-4 py-3">Usulan</th>
                <th class="px-4 py-3">Unit kerja</th>
                <th class="px-4 py-3 text-right">Total</th>
                <th class="px-4 py-3">Prioritas</th>
                <th class="px-4 py-3">{{ $tab === 'antrean' ? 'Diajukan' : 'Status' }}</th>
                <th class="px-4 py-3"><span class="sr-only">Aksi</span></th>
            </x-slot>

            @foreach ($usulans as $u)
                <tr class="hover:bg-slate-50">
                    <td class="px-4 py-3">
                        <a href="{{ route('usulan.show', $u) }}" class="font-medium text-slate-900 hover:text-blue-700 hover:underline">{{ $u->judul_kegiatan }}</a>
                        <p class="text-xs text-slate-500"><span class="font-mono">{{ $u->nomor }}</span>
                            @if ($u->iku_wajib_count) · {{ $u->iku_wajib_count }} IKU wajib @endif
                        </p>
                    </td>
                    <td class="px-4 py-3 text-slate-600">{{ $u->unit->nama_unit }}</td>
                    <td class="whitespace-nowrap px-4 py-3 text-right tabular-nums">{{ Format::rupiah($u->total_rab) }}</td>
                    <td class="px-4 py-3">
                        @if ($u->prioritas)
                            <x-ui.badge :color="Usulan::PRIORITAS[$u->prioritas]">{{ $u->prioritas }}</x-ui.badge>
                        @else
                            <span class="text-xs text-slate-400">—</span>
                        @endif
                    </td>
                    <td class="whitespace-nowrap px-4 py-3">
                        @if ($tab === 'antrean')
                            <span class="text-slate-600">{{ Format::tanggal($u->submitted_at) }}</span>
                            <span class="block text-xs text-slate-400">{{ $u->submitted_at?->locale('id')->diffForHumans() }}</span>
                        @else
                            <x-ui.badge :color="$u->status->color()">{{ $u->status->label() }}</x-ui.badge>
                        @endif
                    </td>
                    <td class="whitespace-nowrap px-4 py-3 text-right">
                        <x-ui.button size="sm" :variant="$tab === 'antrean' ? 'primary' : 'ghost'" :href="route('usulan.show', $u)">
                            {{ $tab === 'antrean' ? 'Periksa' : 'Lihat' }}
                        </x-ui.button>
                    </td>
                </tr>
            @endforeach
        </x-ui.table>
    </div>
</x-app-layout>
