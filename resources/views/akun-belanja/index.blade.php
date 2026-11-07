<x-app-layout>
    <x-slot name="title">Akun Belanja</x-slot>
    <x-slot name="header"><span class="text-sm text-slate-500">Master Data</span></x-slot>

    <div class="mx-auto max-w-7xl space-y-5 px-4 py-6 sm:px-6 lg:px-8">
        <x-ui.page-header title="Akun Belanja" description="Kode akun yang dipilih pengusul saat menyusun RAB.">
            <x-slot name="actions">
                <x-ui.button :href="route('akun-belanja.create')">+ Tambah akun</x-ui.button>
            </x-slot>
        </x-ui.page-header>

        <x-ui.search placeholder="Cari kode atau nama akun…">
            <x-ui.select name="jenis" :options="array_combine(\App\Models\AkunBelanja::JENIS, \App\Models\AkunBelanja::JENIS)" :value="request('jenis')" placeholder="Semua jenis" class="sm:w-40" />
            <x-ui.select name="status" :options="['aktif' => 'Aktif', 'nonaktif' => 'Nonaktif']" :value="request('status')" placeholder="Semua status" class="sm:w-40" />
        </x-ui.search>

        <x-ui.table :paginator="$akun" empty="Tidak ada akun belanja yang cocok.">
            <x-slot name="head">
                <th class="px-4 py-3">Kode</th>
                <th class="px-4 py-3">Nama akun</th>
                <th class="px-4 py-3">Jenis</th>
                <th class="px-4 py-3">Status</th>
                <th class="px-4 py-3"><span class="sr-only">Aksi</span></th>
            </x-slot>

            @foreach ($akun as $a)
                <tr class="hover:bg-slate-50">
                    <td class="whitespace-nowrap px-4 py-3 font-mono text-xs font-semibold text-slate-900">{{ $a->kode_akun }}</td>
                    <td class="px-4 py-3">
                        <p class="font-medium text-slate-900">{{ $a->nama_akun }}</p>
                        @if ($a->keterangan)
                            <p class="text-xs text-slate-500">{{ $a->keterangan }}</p>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-slate-600">{{ $a->jenis_belanja ?? '—' }}</td>
                    <td class="px-4 py-3">
                        <x-ui.badge :color="$a->is_active ? 'green' : 'slate'">{{ $a->is_active ? 'Aktif' : 'Nonaktif' }}</x-ui.badge>
                    </td>
                    <td class="whitespace-nowrap px-4 py-3 text-right">
                        <x-ui.edit-link :href="route('akun-belanja.edit', $a)" :item="$a->nama_akun" />
                        <x-ui.delete-button :action="route('akun-belanja.destroy', $a)" :item="$a->kode_akun.' '.$a->nama_akun" />
                    </td>
                </tr>
            @endforeach
        </x-ui.table>
    </div>
</x-app-layout>
