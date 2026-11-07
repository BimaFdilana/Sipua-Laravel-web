<x-app-layout>
    <x-slot name="title">Unit Kerja</x-slot>
    <x-slot name="header"><span class="text-sm text-slate-500">Master Data</span></x-slot>

    <div class="mx-auto max-w-7xl space-y-5 px-4 py-6 sm:px-6 lg:px-8">
        <x-ui.page-header title="Unit Kerja" description="Jurusan, bagian, dan unit yang dapat mengajukan usulan anggaran.">
            <x-slot name="actions">
                <x-ui.button :href="route('unit.create')">+ Tambah unit</x-ui.button>
            </x-slot>
        </x-ui.page-header>

        <x-ui.search placeholder="Cari kode atau nama unit…">
            <x-ui.select name="jenis" :options="array_combine(\App\Models\Unit::JENIS, \App\Models\Unit::JENIS)" :value="request('jenis')" placeholder="Semua jenis" class="sm:w-40" />
            <x-ui.select name="status" :options="['aktif' => 'Aktif', 'nonaktif' => 'Nonaktif']" :value="request('status')" placeholder="Semua status" class="sm:w-40" />
        </x-ui.search>

        <x-ui.table :paginator="$units" empty="Tidak ada unit kerja yang cocok.">
            <x-slot name="head">
                <th class="px-4 py-3">Kode</th>
                <th class="px-4 py-3">Nama unit</th>
                <th class="px-4 py-3">Jenis</th>
                <th class="px-4 py-3 text-right">Pengguna</th>
                <th class="px-4 py-3">Status</th>
                <th class="px-4 py-3"><span class="sr-only">Aksi</span></th>
            </x-slot>

            @foreach ($units as $unit)
                <tr class="hover:bg-slate-50">
                    <td class="whitespace-nowrap px-4 py-3 font-mono text-xs font-semibold text-slate-900">{{ $unit->unit_id }}</td>
                    <td class="px-4 py-3">
                        <p class="font-medium text-slate-900">{{ $unit->nama_unit }}</p>
                        @if ($unit->keterangan)
                            <p class="text-xs text-slate-500">{{ $unit->keterangan }}</p>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-slate-600">{{ $unit->jenis }}</td>
                    <td class="px-4 py-3 text-right tabular-nums">{{ $unit->users_count }}</td>
                    <td class="px-4 py-3">
                        <x-ui.badge :color="$unit->is_active ? 'green' : 'slate'">{{ $unit->is_active ? 'Aktif' : 'Nonaktif' }}</x-ui.badge>
                    </td>
                    <td class="whitespace-nowrap px-4 py-3 text-right">
                        <x-ui.edit-link :href="route('unit.edit', $unit)" :item="$unit->nama_unit" />
                        <x-ui.delete-button :action="route('unit.destroy', $unit)" :item="$unit->nama_unit" />
                    </td>
                </tr>
            @endforeach
        </x-ui.table>
    </div>
</x-app-layout>
