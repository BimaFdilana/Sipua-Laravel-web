@php($editing = $akun->exists)

<x-app-layout>
    <x-slot name="title">{{ $editing ? 'Ubah' : 'Tambah' }} Akun Belanja</x-slot>
    <x-slot name="header"><span class="text-sm text-slate-500">Master Data · Akun Belanja</span></x-slot>

    <div class="mx-auto max-w-3xl space-y-5 px-4 py-6 sm:px-6 lg:px-8">
        <x-ui.page-header :title="$editing ? 'Ubah akun belanja' : 'Tambah akun belanja'" :back="route('akun-belanja.index')" />

        <form method="POST" action="{{ $editing ? route('akun-belanja.update', $akun) : route('akun-belanja.store') }}">
            @csrf
            @if ($editing) @method('PUT') @endif

            <x-ui.card>
                <div class="grid gap-5 sm:grid-cols-6">
                    <x-ui.field label="Kode akun" name="kode_akun" :required="! $editing"
                        :hint="$editing ? 'Kode akun tidak bisa diubah.' : '6 digit, sesuai Bagan Akun Standar.'" class="sm:col-span-2">
                        <x-ui.input name="kode_akun" :value="$akun->kode_akun" inputmode="numeric" maxlength="6" class="font-mono" :disabled="$editing" />
                    </x-ui.field>
                    <x-ui.field label="Nama akun" name="nama_akun" required class="sm:col-span-4">
                        <x-ui.input name="nama_akun" :value="$akun->nama_akun" />
                    </x-ui.field>
                    <x-ui.field label="Jenis belanja" name="jenis_belanja" required class="sm:col-span-2">
                        <x-ui.select name="jenis_belanja" :options="array_combine(\App\Models\AkunBelanja::JENIS, \App\Models\AkunBelanja::JENIS)" :value="$akun->jenis_belanja" />
                    </x-ui.field>
                    <x-ui.field label="Keterangan" name="keterangan" class="sm:col-span-6">
                        <x-ui.textarea name="keterangan" :value="$akun->keterangan" rows="2" />
                    </x-ui.field>
                    <div class="sm:col-span-6">
                        <x-ui.toggle name="is_active" :checked="$akun->is_active" label="Akun aktif"
                            description="Hanya akun aktif yang bisa dipilih saat menyusun RAB." />
                    </div>
                </div>

                <x-slot name="footer">
                    <x-ui.button variant="secondary" :href="route('akun-belanja.index')">Batal</x-ui.button>
                    <x-ui.button type="submit">{{ $editing ? 'Simpan perubahan' : 'Simpan akun' }}</x-ui.button>
                </x-slot>
            </x-ui.card>
        </form>
    </div>
</x-app-layout>
