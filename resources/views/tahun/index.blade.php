<x-app-layout>
    <x-slot name="title">Tahun Anggaran</x-slot>
    <x-slot name="header"><span class="text-sm text-slate-500">Master Data</span></x-slot>

    <div class="mx-auto max-w-4xl space-y-5 px-4 py-6 sm:px-6 lg:px-8">
        <x-ui.page-header title="Tahun Anggaran"
            description="Hanya satu tahun yang aktif. Semua usulan baru memakai tahun anggaran aktif.">
            <x-slot name="actions">
                <x-ui.button :href="route('tahun.create')">+ Tambah tahun</x-ui.button>
            </x-slot>
        </x-ui.page-header>

        <x-ui.table empty="Belum ada tahun anggaran.">
            <x-slot name="head">
                <th class="px-4 py-3">Tahun</th>
                <th class="px-4 py-3">Status</th>
                <th class="px-4 py-3"><span class="sr-only">Aksi</span></th>
            </x-slot>

            @foreach ($tahuns as $t)
                <tr @class(['bg-blue-50/40' => $t->status, 'hover:bg-slate-50' => ! $t->status])>
                    <td class="px-4 py-3 text-base font-semibold tabular-nums text-slate-900">{{ $t->tahun }}</td>
                    <td class="px-4 py-3">
                        @if ($t->status)
                            <x-ui.badge color="green"><span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span> Aktif</x-ui.badge>
                        @else
                            <x-ui.badge>Nonaktif</x-ui.badge>
                        @endif
                    </td>
                    <td class="whitespace-nowrap px-4 py-3 text-right">
                        @unless ($t->status)
                            <form method="POST" action="{{ route('tahun.activate', $t) }}" class="mr-1 inline">
                                @csrf
                                @method('PATCH')
                                <x-ui.button type="submit" variant="secondary" size="sm">Jadikan aktif</x-ui.button>
                            </form>
                        @endunless
                        <x-ui.edit-link :href="route('tahun.edit', $t)" :item="'tahun '.$t->tahun" />
                        @unless ($t->status)
                            <x-ui.delete-button :action="route('tahun.destroy', $t)" :item="'Tahun anggaran '.$t->tahun" />
                        @endunless
                    </td>
                </tr>
            @endforeach
        </x-ui.table>
    </div>
</x-app-layout>
