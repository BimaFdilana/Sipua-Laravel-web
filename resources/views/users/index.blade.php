<x-app-layout>
    <x-slot name="title">Pengguna</x-slot>
    <x-slot name="header"><span class="text-sm text-slate-500">Master Data</span></x-slot>

    @php
        $roleColor = ['admin' => 'violet', 'pengusul' => 'blue', 'perencanaan' => 'amber', 'wadir' => 'slate', 'spi' => 'slate', 'direktur' => 'slate'];
    @endphp

    <div class="mx-auto max-w-7xl space-y-5 px-4 py-6 sm:px-6 lg:px-8">
        <x-ui.page-header title="Pengguna" description="Akun yang bisa masuk ke SIPUA beserta peran dan unit kerjanya.">
            <x-slot name="actions">
                <x-ui.button :href="route('users.create')">+ Tambah pengguna</x-ui.button>
            </x-slot>
        </x-ui.page-header>

        <x-ui.search placeholder="Cari nama, email, atau NIP…">
            <x-ui.select name="role" :options="\App\Models\User::ROLES" :value="request('role')" placeholder="Semua peran" class="sm:w-44" />
            <x-ui.select name="status" :options="['aktif' => 'Aktif', 'nonaktif' => 'Nonaktif']" :value="request('status')" placeholder="Semua status" class="sm:w-40" />
        </x-ui.search>

        <x-ui.table :paginator="$users" empty="Tidak ada pengguna yang cocok.">
            <x-slot name="head">
                <th class="px-4 py-3">Nama</th>
                <th class="px-4 py-3">Peran</th>
                <th class="px-4 py-3">Unit kerja</th>
                <th class="px-4 py-3">Status</th>
                <th class="px-4 py-3"><span class="sr-only">Aksi</span></th>
            </x-slot>

            @foreach ($users as $u)
                <tr class="hover:bg-slate-50">
                    <td class="px-4 py-3">
                        <p class="font-medium text-slate-900">
                            {{ $u->name }}
                            @if ($u->is(auth()->user()))
                                <span class="ml-1 text-xs font-normal text-slate-400">(Anda)</span>
                            @endif
                        </p>
                        <p class="text-xs text-slate-500">{{ $u->email }}@if ($u->nip) · NIP {{ $u->nip }}@endif</p>
                    </td>
                    <td class="px-4 py-3">
                        <x-ui.badge :color="$roleColor[$u->role] ?? 'slate'">{{ $u->roleLabel() }}</x-ui.badge>
                    </td>
                    <td class="px-4 py-3 text-slate-600">{{ $u->unit?->nama_unit ?? '—' }}</td>
                    <td class="px-4 py-3">
                        <x-ui.badge :color="$u->is_active ? 'green' : 'red'">{{ $u->is_active ? 'Aktif' : 'Nonaktif' }}</x-ui.badge>
                    </td>
                    <td class="whitespace-nowrap px-4 py-3 text-right">
                        <x-ui.edit-link :href="route('users.edit', $u)" :item="$u->name" />
                        @unless ($u->is(auth()->user()))
                            <x-ui.delete-button :action="route('users.destroy', $u)" :item="$u->name" />
                        @endunless
                    </td>
                </tr>
            @endforeach
        </x-ui.table>
    </div>
</x-app-layout>
