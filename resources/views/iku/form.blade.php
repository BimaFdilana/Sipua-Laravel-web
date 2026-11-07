@php($editing = $iku->exists)

<x-app-layout>
    <x-slot name="title">{{ $editing ? 'Ubah' : 'Tambah' }} IKU</x-slot>
    <x-slot name="header"><span class="text-sm text-slate-500">Master Data · IKU</span></x-slot>

    <div class="mx-auto max-w-3xl space-y-5 px-4 py-6 sm:px-6 lg:px-8">
        <x-ui.page-header :title="$editing ? 'Ubah IKU' : 'Tambah IKU'" :back="route('iku.index')" />

        <form method="POST" action="{{ $editing ? route('iku.update', $iku) : route('iku.store') }}">
            @csrf
            @if ($editing) @method('PUT') @endif

            <x-ui.card>
                <div class="grid gap-5 sm:grid-cols-6">
                    <x-ui.field label="Kode IKU" name="kode_iku" required hint="Unik per tahun, mis. IKU1." class="sm:col-span-2">
                        <x-ui.input name="kode_iku" :value="$iku->kode_iku" maxlength="20" class="font-mono uppercase" autofocus />
                    </x-ui.field>
                    <x-ui.field label="Tahun" name="tahun" required class="sm:col-span-2">
                        <x-ui.select name="tahun" :options="$tahunOptions" :value="$iku->tahun" placeholder="Pilih tahun" />
                    </x-ui.field>
                    <x-ui.field label="Target" name="target" hint="mis. 80% atau 25 publikasi" class="sm:col-span-2">
                        <x-ui.input name="target" :value="$iku->target" />
                    </x-ui.field>
                    <x-ui.field label="Nama indikator" name="nama_iku" required class="sm:col-span-6">
                        <x-ui.input name="nama_iku" :value="$iku->nama_iku" />
                    </x-ui.field>

                    <fieldset class="sm:col-span-6">
                        <legend class="text-sm font-medium text-slate-700">Sifat <span class="text-red-500" aria-hidden="true">*</span></legend>
                        <div class="mt-2 grid gap-3 sm:grid-cols-2">
                            @foreach (['Wajib' => 'Harus didukung oleh usulan unit kerja.', 'Pilihan' => 'Boleh dipilih jika relevan dengan kegiatan.'] as $value => $help)
                                <label class="flex cursor-pointer gap-3 rounded-lg border border-slate-200 p-3 has-[:checked]:border-blue-600 has-[:checked]:bg-blue-50/50">
                                    <input type="radio" name="sifat" value="{{ $value }}" @checked(old('sifat', $iku->sifat) === $value)
                                        class="mt-0.5 border-slate-300 text-blue-600 focus:ring-blue-600">
                                    <span>
                                        <span class="block text-sm font-medium text-slate-900">{{ $value }}</span>
                                        <span class="block text-xs text-slate-500">{{ $help }}</span>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                        @error('sifat')
                            <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </fieldset>

                    <x-ui.field label="Deskripsi" name="deskripsi" class="sm:col-span-6">
                        <x-ui.textarea name="deskripsi" :value="$iku->deskripsi" rows="4" />
                    </x-ui.field>
                    <x-ui.field label="Kriteria" name="kriteria" hint="Satu kriteria per baris." class="sm:col-span-6">
                        <x-ui.textarea name="kriteria" :value="$iku->kriteria" rows="4" />
                    </x-ui.field>
                </div>

                <x-slot name="footer">
                    <x-ui.button variant="secondary" :href="route('iku.index')">Batal</x-ui.button>
                    <x-ui.button type="submit">{{ $editing ? 'Simpan perubahan' : 'Simpan IKU' }}</x-ui.button>
                </x-slot>
            </x-ui.card>
        </form>
    </div>
</x-app-layout>
