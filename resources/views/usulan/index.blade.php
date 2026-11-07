@php
    use App\Enums\StatusUsulan as S;
    use App\Support\Format;

    $hitung = fn (array $statuses) => collect($statuses)->sum(fn ($s) => $ringkasan[$s->value]->jumlah ?? 0);
    $kartu = [
        ['label' => 'Draft', 'value' => $hitung([S::Draft]), 'status' => 'draft'],
        ['label' => 'Diproses', 'value' => $hitung([S::Diajukan, S::DiWadir, S::DiSpi, S::DiDirektur]), 'status' => null],
        ['label' => 'Perlu perbaikan', 'value' => $hitung([S::PerluPerbaikan]), 'status' => 'perlu_perbaikan'],
        ['label' => 'Disahkan', 'value' => $hitung([S::Disahkan]), 'status' => 'disahkan'],
    ];
    $totalNilai = $ringkasan->sum('nilai');
@endphp

<x-app-layout>
    <x-slot name="title">Usulan Saya</x-slot>
    <x-slot name="header"><span class="text-sm text-slate-500">Usulan</span></x-slot>

    <div class="mx-auto max-w-7xl space-y-5 px-4 py-6 sm:px-6 lg:px-8">
        <x-ui.page-header title="Usulan Saya" :description="'Usulan anggaran '.($unit?->nama_unit ?? 'unit kerja Anda').'.'">
            <x-slot name="actions">
                <x-ui.button variant="secondary" :href="route('usulan.rkt', ['tahun' => $tahunId])" title="Rencana Kerja Tahunan dari usulan yang disahkan">Cetak RKT (PDF)</x-ui.button>
                <x-ui.button :href="route('usulan.create')">+ Buat usulan</x-ui.button>
            </x-slot>
        </x-ui.page-header>

        <section class="grid grid-cols-2 gap-3 lg:grid-cols-5">
            @foreach ($kartu as $k)
                <div class="rounded-xl border border-slate-200 bg-white px-4 py-3">
                    <p class="text-xs font-medium text-slate-500">{{ $k['label'] }}</p>
                    <p class="mt-1 text-2xl font-semibold tabular-nums text-slate-900">{{ $k['value'] }}</p>
                </div>
            @endforeach
            <div class="col-span-2 rounded-xl border border-slate-200 bg-white px-4 py-3 lg:col-span-1">
                <p class="text-xs font-medium text-slate-500">Total nilai usulan</p>
                <p class="mt-1 text-lg font-semibold tabular-nums text-slate-900">{{ Format::rupiah($totalNilai) }}</p>
            </div>
        </section>

        <x-ui.search placeholder="Cari judul atau nomor usulan…">
            <x-ui.select name="tahun" :options="$tahunOptions" :value="$tahunId" placeholder="Semua tahun" class="sm:w-36" />
            <x-ui.select name="status" :options="S::options()" :value="request('status')" placeholder="Semua status" class="sm:w-48" />
        </x-ui.search>

        <x-ui.table :paginator="$usulans" empty="Belum ada usulan. Klik “Buat usulan” untuk memulai.">
            <x-slot name="head">
                <th class="px-4 py-3">Usulan</th>
                <th class="px-4 py-3">Tahun</th>
                <th class="px-4 py-3 text-right">Total anggaran</th>
                <th class="px-4 py-3">Status</th>
                <th class="px-4 py-3"><span class="sr-only">Aksi</span></th>
            </x-slot>

            @foreach ($usulans as $u)
                <tr class="hover:bg-slate-50">
                    <td class="px-4 py-3">
                        <a href="{{ route('usulan.show', $u) }}" class="font-medium text-slate-900 hover:text-blue-700 hover:underline">{{ $u->judul_kegiatan }}</a>
                        <p class="font-mono text-xs text-slate-500">{{ $u->nomor }}</p>
                    </td>
                    <td class="px-4 py-3 tabular-nums">{{ $u->tahun->tahun }}</td>
                    <td class="whitespace-nowrap px-4 py-3 text-right tabular-nums">{{ Format::rupiah($u->total_rab) }}</td>
                    <td class="px-4 py-3">
                        <x-ui.badge :color="$u->status->color()">{{ $u->status->label() }}</x-ui.badge>
                    </td>
                    <td class="whitespace-nowrap px-4 py-3 text-right">
                        @can('update', $u)
                            <x-ui.button size="sm" variant="secondary" :href="route('usulan.edit', $u)">
                                {{ $u->status === S::PerluPerbaikan ? 'Perbaiki' : 'Lanjutkan' }}
                            </x-ui.button>
                        @else
                            <x-ui.button size="sm" variant="ghost" :href="route('usulan.show', $u)">Lihat</x-ui.button>
                        @endcan
                        @can('delete', $u)
                            <x-ui.delete-button :action="route('usulan.destroy', $u)" :item="$u->judul_kegiatan" />
                        @endcan
                    </td>
                </tr>
            @endforeach
        </x-ui.table>
    </div>
</x-app-layout>
