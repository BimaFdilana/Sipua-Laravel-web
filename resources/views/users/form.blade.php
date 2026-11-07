@php($editing = $user->exists)

<x-app-layout>
    <x-slot name="title">{{ $editing ? 'Ubah' : 'Tambah' }} Pengguna</x-slot>
    <x-slot name="header"><span class="text-sm text-slate-500">Master Data · Pengguna</span></x-slot>

    <div class="mx-auto max-w-3xl space-y-5 px-4 py-6 sm:px-6 lg:px-8">
        <x-ui.page-header :title="$editing ? 'Ubah pengguna' : 'Tambah pengguna'" :back="route('users.index')"
            :description="$editing ? $user->email : 'Akun baru langsung bisa dipakai login jika statusnya aktif.'" />

        <form method="POST" action="{{ $editing ? route('users.update', $user) : route('users.store') }}"
            x-data="{ role: @js(old('role', $user->role)) }" class="space-y-5">
            @csrf
            @if ($editing) @method('PUT') @endif

            <x-ui.card title="Akun" description="Dipakai untuk masuk ke aplikasi.">
                <div class="grid gap-5 sm:grid-cols-2">
                    <x-ui.field label="Nama tampilan" name="name" required>
                        <x-ui.input name="name" :value="$user->name" autocomplete="off" autofocus />
                    </x-ui.field>
                    <x-ui.field label="Email" name="email" required>
                        <x-ui.input type="email" name="email" :value="$user->email" autocomplete="off" />
                    </x-ui.field>
                    <x-ui.field label="Password" name="password" :required="! $editing"
                        :hint="$editing ? 'Kosongkan jika tidak ingin mengganti password.' : 'Minimal 8 karakter.'">
                        <x-ui.input type="password" name="password" autocomplete="new-password" />
                    </x-ui.field>
                    <x-ui.field label="Ulangi password" name="password_confirmation" :required="! $editing">
                        <x-ui.input type="password" name="password_confirmation" autocomplete="new-password" />
                    </x-ui.field>
                    <x-ui.field label="Peran" name="role" required>
                        <x-ui.select name="role" :options="\App\Models\User::ROLES" :value="$user->role" x-model="role" />
                    </x-ui.field>
                    <div class="flex items-end pb-1">
                        <x-ui.toggle name="is_active" :checked="$user->is_active" label="Akun aktif"
                            description="Akun nonaktif tidak bisa login." class="w-full" />
                    </div>
                    @error('is_active')
                        <p class="text-sm text-red-600 sm:col-span-2">{{ $message }}</p>
                    @enderror
                </div>
            </x-ui.card>

            <x-ui.card title="Profil kepegawaian">
                <div class="grid gap-5 sm:grid-cols-2">
                    <x-ui.field label="Unit kerja" name="unit_id" class="sm:col-span-2">
                        <x-ui.select name="unit_id" :options="$units" :value="$user->unit_id" placeholder="— Tidak terikat unit —" />
                        <p x-show="role === 'pengusul'" x-cloak class="text-xs text-amber-700">Wajib dipilih untuk Pengusul: usulan dibuat atas nama unit ini.</p>
                    </x-ui.field>
                    <x-ui.field label="Nama lengkap & gelar" name="nama_lengkap" class="sm:col-span-2">
                        <x-ui.input name="nama_lengkap" :value="$user->nama_lengkap" placeholder="mis. Johny Custer, S.T., M.T." />
                    </x-ui.field>
                    <x-ui.field label="NIP" name="nip">
                        <x-ui.input name="nip" :value="$user->nip" inputmode="numeric" class="font-mono" />
                    </x-ui.field>
                    <x-ui.field label="Jabatan" name="jabatan">
                        <x-ui.input name="jabatan" :value="$user->jabatan" />
                    </x-ui.field>
                    <x-ui.field label="Nomor HP" name="no_hp">
                        <x-ui.input type="tel" name="no_hp" :value="$user->no_hp" placeholder="08xxxxxxxxxx" />
                    </x-ui.field>
                </div>

                <x-slot name="footer">
                    <x-ui.button variant="secondary" :href="route('users.index')">Batal</x-ui.button>
                    <x-ui.button type="submit">{{ $editing ? 'Simpan perubahan' : 'Simpan pengguna' }}</x-ui.button>
                </x-slot>
            </x-ui.card>
        </form>
    </div>
</x-app-layout>
