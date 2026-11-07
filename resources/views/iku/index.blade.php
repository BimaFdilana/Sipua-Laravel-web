<x-app-layout>
    <x-slot name="title">IKU</x-slot>
    <x-slot name="header"><span class="text-sm text-slate-500">Master Data</span></x-slot>

    <div class="mx-auto max-w-7xl space-y-5 px-4 py-6 sm:px-6 lg:px-8">
        <x-ui.page-header title="Indikator Kinerja Utama"
            description="IKU yang bisa dikaitkan dengan usulan anggaran. IKU Wajib harus didukung oleh usulan.">
            <x-slot name="actions">
                <x-ui.button :href="route('iku.create')">+ Tambah IKU</x-ui.button>
            </x-slot>
        </x-ui.page-header>

        <x-ui.search placeholder="Cari kode atau nama IKU…">
            <x-ui.select name="tahun" :options="$tahunOptions" :value="$tahun" placeholder="Semua tahun" class="sm:w-40" />
            <x-ui.select name="sifat" :options="array_combine(\App\Models\Iku::SIFAT, \App\Models\Iku::SIFAT)" :value="request('sifat')" placeholder="Semua sifat" class="sm:w-36" />
        </x-ui.search>

        <x-ui.table :paginator="$ikus" :empty="$tahun ? 'Belum ada IKU untuk tahun '.$tahun.'.' : 'Belum ada IKU.'">
            <x-slot name="head">
                <th class="px-4 py-3">Kode</th>
                <th class="px-4 py-3">Indikator</th>
                <th class="px-4 py-3">Sifat</th>
                <th class="px-4 py-3">Tahun</th>
                <th class="px-4 py-3">Target</th>
                <th class="px-4 py-3"><span class="sr-only">Aksi</span></th>
            </x-slot>

            @foreach ($ikus as $iku)
                <tr class="align-top hover:bg-slate-50" x-data="{ open: false }">
                    <td class="whitespace-nowrap px-4 py-3 font-mono text-xs font-semibold text-slate-900">{{ $iku->kode_iku }}</td>
                    <td class="max-w-xl px-4 py-3">
                        <p class="font-medium text-slate-900">{{ $iku->nama_iku }}</p>
                        @if ($iku->deskripsi || $iku->kriteria)
                            <button type="button" @click="open = ! open" class="mt-1 text-xs font-medium text-blue-700 hover:underline"
                                x-text="open ? 'Sembunyikan detail' : 'Lihat deskripsi & kriteria'">Lihat deskripsi & kriteria</button>
                            <div x-show="open" x-cloak x-transition class="mt-2 space-y-2 text-xs leading-relaxed text-slate-600">
                                @if ($iku->deskripsi)
                                    <p>{{ $iku->deskripsi }}</p>
                                @endif
                                @if ($iku->kriteria)
                                    <p class="whitespace-pre-line"><span class="font-semibold text-slate-700">Kriteria:</span> {{ $iku->kriteria }}</p>
                                @endif
                            </div>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <x-ui.badge :color="$iku->sifat === 'Wajib' ? 'amber' : 'slate'">{{ $iku->sifat }}</x-ui.badge>
                    </td>
                    <td class="px-4 py-3 tabular-nums">{{ $iku->tahun }}</td>
                    <td class="px-4 py-3 tabular-nums">{{ $iku->target ?? '—' }}</td>
                    <td class="whitespace-nowrap px-4 py-3 text-right">
                        <x-ui.edit-link :href="route('iku.edit', $iku)" :item="$iku->kode_iku" />
                        <x-ui.delete-button :action="route('iku.destroy', $iku)" :item="$iku->kode_iku.' ('.$iku->tahun.')'" />
                    </td>
                </tr>
            @endforeach
        </x-ui.table>
    </div>
</x-app-layout>
