@props(['action', 'item', 'label' => 'Hapus'])

{{-- Tombol hapus dengan dialog konfirmasi. $item = nama data yang dihapus, ditampilkan di dialog. --}}
<div x-data="{ open: false }" class="inline-block">
    <button type="button" @click="open = true"
        class="rounded-md p-1.5 text-slate-400 hover:bg-red-50 hover:text-red-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-red-500"
        title="{{ $label }}">
        <span class="sr-only">{{ $label }} {{ $item }}</span>
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
    </button>

    <template x-teleport="body">
        <div x-show="open" x-cloak @keydown.escape.window="open = false" class="fixed inset-0 z-50 flex items-end justify-center p-4 sm:items-center"
            role="dialog" aria-modal="true">
            <div x-show="open" x-transition.opacity @click="open = false" class="absolute inset-0 bg-slate-900/40"></div>
            <div x-show="open" x-transition x-trap.noscroll="open"
                class="relative w-full max-w-sm rounded-xl bg-white p-5 text-left shadow-xl">
                <h2 class="text-base font-semibold text-slate-900">{{ $label }} data?</h2>
                <p class="mt-2 text-sm text-slate-600">
                    <span class="font-medium text-slate-900">{{ $item }}</span> akan dihapus permanen dan tidak bisa dikembalikan.
                </p>
                <form method="POST" action="{{ $action }}" class="mt-5 flex justify-end gap-2">
                    @csrf
                    @method('DELETE')
                    <x-ui.button variant="secondary" @click="open = false">Batal</x-ui.button>
                    <x-ui.button variant="danger" type="submit">{{ $label }}</x-ui.button>
                </form>
            </div>
        </div>
    </template>
</div>
