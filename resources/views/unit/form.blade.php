@php($editing = $unit->exists)

<x-app-layout>
    <x-slot name="title">{{ $editing ? 'Ubah' : 'Tambah' }} Unit Kerja</x-slot>
    <x-slot name="header"><span class="text-sm text-slate-500">Master Data · Unit Kerja</span></x-slot>

    <div class="mx-auto max-w-3xl space-y-5 px-4 py-6 sm:px-6 lg:px-8">
        <x-ui.page-header :title="$editing ? 'Ubah unit kerja' : 'Tambah unit kerja'" :back="route('unit.index')" />

        <form method="POST" action="{{ $editing ? route('unit.update', $unit) : route('unit.store') }}">
            @csrf
            @if ($editing) @method('PUT') @endif

            <x-ui.card>
                <div class="grid gap-5 sm:grid-cols-6">
                    <x-ui.field label="Kode unit" name="unit_id" required hint="Huruf kapital/angka, mis. TI atau P3M." class="sm:col-span-2">
                        <x-ui.input name="unit_id" :value="$unit->unit_id" maxlength="20" class="font-mono uppercase" autofocus />
                    </x-ui.field>
                    <x-ui.field label="Nama unit" name="nama_unit" required class="sm:col-span-4">
                        <x-ui.input name="nama_unit" :value="$unit->nama_unit" />
                    </x-ui.field>
                    <x-ui.field label="Jenis" name="jenis" required class="sm:col-span-2">
                        <x-ui.select name="jenis" :options="array_combine(\App\Models\Unit::JENIS, \App\Models\Unit::JENIS)" :value="$unit->jenis" />
                    </x-ui.field>
                    <x-ui.field label="Keterangan" name="keterangan" class="sm:col-span-6">
                        <x-ui.textarea name="keterangan" :value="$unit->keterangan" rows="2" />
                    </x-ui.field>
                    <div class="sm:col-span-6">
                        <x-ui.toggle name="is_active" :checked="$unit->is_active" label="Unit aktif"
                            description="Hanya unit aktif yang bisa dipilih saat membuat pengguna dan usulan." />
                    </div>
                </div>

                <x-slot name="footer">
                    <x-ui.button variant="secondary" :href="route('unit.index')">Batal</x-ui.button>
                    <x-ui.button type="submit">{{ $editing ? 'Simpan perubahan' : 'Simpan unit' }}</x-ui.button>
                </x-slot>
            </x-ui.card>
        </form>
    </div>
</x-app-layout>
