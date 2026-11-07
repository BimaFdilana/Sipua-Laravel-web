<x-usulan.wizard :usulan="$usulan" :step="$step" :steps="$steps" :kelengkapan="$kelengkapan">
    <div class="grid gap-5 lg:grid-cols-5">
        <div class="space-y-5 lg:col-span-3">
            {{-- Unggah --}}
            <x-ui.card title="Data dukung" description="PDF, maksimal 5 MB per file. Lampirkan referensi harga dan dokumen pendukung RAB.">
                <form method="POST" action="{{ route('usulan.dokumen.store', $usulan) }}" enctype="multipart/form-data"
                    x-data="{ nama: '', besar: false }" class="space-y-4">
                    @csrf
                    <x-ui.field label="Kategori" name="kategori" required>
                        <x-ui.select name="kategori" :options="$kategori" />
                    </x-ui.field>

                    <div>
                        <label for="file" class="flex cursor-pointer flex-col items-center justify-center gap-1 rounded-lg border-2 border-dashed px-4 py-6 text-center transition-colors
                            {{ $errors->has('file') ? 'border-red-300 bg-red-50' : 'border-slate-300 hover:border-blue-400 hover:bg-blue-50/40' }}">
                            <x-icon name="document" class="h-6 w-6 text-slate-400" />
                            <span class="text-sm font-medium text-slate-700" x-text="nama || 'Pilih file PDF'">Pilih file PDF</span>
                            <span class="text-xs" :class="besar ? 'text-red-600' : 'text-slate-500'"
                                x-text="besar ? 'File lebih dari 5 MB, pilih file lain.' : 'atau seret file ke sini'">atau seret file ke sini</span>
                            <input id="file" name="file" type="file" accept="application/pdf,.pdf" class="sr-only" required
                                @change="const f = $event.target.files[0]; nama = f ? f.name : ''; besar = f ? f.size > 5 * 1024 * 1024 : false">
                        </label>
                        @error('file')
                            <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex justify-end">
                        <x-ui.button type="submit" ::disabled="! nama || besar">Unggah dokumen</x-ui.button>
                    </div>
                </form>

                @if ($dokumens->isNotEmpty())
                    <ul class="mt-5 divide-y divide-slate-100 rounded-lg border border-slate-200">
                        @foreach ($dokumens as $dok)
                            <li class="flex items-center gap-3 px-3 py-2.5">
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-md bg-red-50 text-[10px] font-bold text-red-600">PDF</span>
                                <div class="min-w-0 flex-1">
                                    <a href="{{ route('usulan.dokumen.download', [$usulan, $dok]) }}" target="_blank"
                                        class="block truncate text-sm font-medium text-slate-900 hover:text-blue-700 hover:underline">{{ $dok->nama_asli }}</a>
                                    <p class="text-xs text-slate-500">{{ $dok->kategoriLabel() }} · {{ $dok->ukuranLabel() }}</p>
                                </div>
                                <x-ui.delete-button :action="route('usulan.dokumen.destroy', [$usulan, $dok])" :item="$dok->nama_asli" />
                            </li>
                        @endforeach
                    </ul>
                @endif
            </x-ui.card>
        </div>

        {{-- Daftar periksa + kirim --}}
        <div class="lg:col-span-2">
            <x-ui.card title="Daftar periksa">
                @php($lengkap = $kelengkapan->every('ok'))
                <ul class="space-y-3">
                    @foreach ($kelengkapan as $cek)
                        <li class="flex gap-3">
                            @if ($cek['ok'])
                                <x-icon name="check-circle" class="h-5 w-5 text-emerald-600" />
                            @else
                                <span class="mt-0.5 h-4 w-4 shrink-0 rounded-full border-2 border-slate-300"></span>
                            @endif
                            <div class="min-w-0 text-sm">
                                <p @class(['text-slate-900' => $cek['ok'], 'text-slate-700' => ! $cek['ok']])>{{ $cek['label'] }}</p>
                                @if (! $cek['ok'])
                                    @if ($cek['detail'])
                                        <p class="text-xs text-slate-500">{{ $cek['detail'] }}</p>
                                    @endif
                                    @if ($cek['step'] !== 'dokumen')
                                        <a href="{{ route('usulan.edit', [$usulan, $cek['step']]) }}" class="text-xs font-medium text-blue-700 hover:underline">Lengkapi &rarr;</a>
                                    @endif
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ul>

                @error('kirim')
                    <p class="mt-4 text-sm text-red-600">{{ $message }}</p>
                @enderror

                <div class="mt-5 border-t border-slate-200 pt-4">
                    <dl class="mb-4 flex items-baseline justify-between text-sm">
                        <dt class="text-slate-500">Total RAB</dt>
                        <dd class="font-semibold tabular-nums text-slate-900">{{ \App\Support\Format::rupiah($usulan->total_rab) }}</dd>
                    </dl>

                    <div x-data="{ open: false }">
                        <x-ui.button class="w-full" :disabled="! $lengkap" @click="open = true">
                            {{ $usulan->status === \App\Enums\StatusUsulan::PerluPerbaikan ? 'Kirim ulang ke Perencanaan' : 'Kirim ke Perencanaan' }}
                        </x-ui.button>
                        @unless ($lengkap)
                            <p class="mt-2 text-center text-xs text-slate-500">Lengkapi semua syarat untuk bisa mengirim.</p>
                        @endunless

                        <template x-teleport="body">
                            <div x-show="open" x-cloak @keydown.escape.window="open = false" class="fixed inset-0 z-50 flex items-end justify-center p-4 sm:items-center" role="dialog" aria-modal="true">
                                <div class="absolute inset-0 bg-slate-900/40" @click="open = false"></div>
                                <div x-trap.noscroll="open" class="relative w-full max-w-md rounded-xl bg-white p-5 shadow-xl">
                                    <h2 class="text-base font-semibold text-slate-900">Kirim usulan untuk diverifikasi?</h2>
                                    <p class="mt-2 text-sm text-slate-600">
                                        <span class="font-medium text-slate-900">{{ $usulan->judul_kegiatan }}</span>
                                        senilai <span class="font-medium tabular-nums text-slate-900">{{ \App\Support\Format::rupiah($usulan->total_rab) }}</span>
                                        akan dikirim ke Perencanaan. Setelah dikirim, usulan <strong>tidak bisa diubah</strong> kecuali dikembalikan untuk perbaikan.
                                    </p>
                                    <form method="POST" action="{{ route('usulan.kirim', $usulan) }}" class="mt-5 flex justify-end gap-2">
                                        @csrf
                                        <x-ui.button variant="secondary" @click="open = false">Batal</x-ui.button>
                                        <x-ui.button type="submit">Ya, kirim usulan</x-ui.button>
                                    </form>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </x-ui.card>
        </div>
    </div>

    <div>
        <x-ui.button variant="ghost" :href="route('usulan.edit', [$usulan, 'iku'])">&larr; Sebelumnya</x-ui.button>
    </div>
</x-usulan.wizard>
