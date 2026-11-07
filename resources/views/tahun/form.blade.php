@php($editing = $tahun->exists)

<x-app-layout>
    <x-slot name="title">{{ $editing ? 'Ubah' : 'Tambah' }} Tahun Anggaran</x-slot>
    <x-slot name="header"><span class="text-sm text-slate-500">Master Data · Tahun Anggaran</span></x-slot>

    <div class="mx-auto max-w-xl space-y-5 px-4 py-6 sm:px-6 lg:px-8">
        <x-ui.page-header :title="$editing ? 'Ubah tahun anggaran' : 'Tambah tahun anggaran'" :back="route('tahun.index')" />

        <form method="POST" action="{{ $editing ? route('tahun.update', $tahun) : route('tahun.store') }}">
            @csrf
            @if ($editing) @method('PUT') @endif

            <x-ui.card>
                <div class="space-y-5">
                    <x-ui.field label="Tahun anggaran" name="tahun" required class="max-w-[10rem]">
                        <x-ui.input type="number" name="tahun" :value="$tahun->tahun" min="2000" max="2100" class="tabular-nums" autofocus />
                    </x-ui.field>
                    <x-ui.toggle name="status" :checked="$tahun->status" label="Jadikan tahun aktif"
                        description="Tahun yang sedang aktif akan otomatis dinonaktifkan." />
                </div>

                <x-slot name="footer">
                    <x-ui.button variant="secondary" :href="route('tahun.index')">Batal</x-ui.button>
                    <x-ui.button type="submit">{{ $editing ? 'Simpan perubahan' : 'Simpan tahun' }}</x-ui.button>
                </x-slot>
            </x-ui.card>
        </form>
    </div>
</x-app-layout>
