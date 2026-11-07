@php
    // Baris awal: old() setelah gagal validasi, atau data tersimpan.
    $rows = collect(old('items', $items->map(fn ($i) => [
        'kode_akun' => $i->kode_akun,
        'uraian' => $i->uraian,
        'volume' => (float) $i->volume,
        'satuan' => $i->satuan,
        'harga_satuan' => (float) $i->harga_satuan,
    ])->all()))->values()->map(fn ($r) => [
        'kode_akun' => $r['kode_akun'] ?? '',
        'uraian' => $r['uraian'] ?? '',
        'volume' => is_numeric($r['volume'] ?? null) ? (float) $r['volume'] : null,
        'satuan' => $r['satuan'] ?? '',
        'harga_satuan' => is_numeric($r['harga_satuan'] ?? null) ? (float) $r['harga_satuan'] : null,
    ]);
@endphp

<x-usulan.wizard :usulan="$usulan" :step="$step" :steps="$steps" :kelengkapan="$kelengkapan">
    <form method="POST" action="{{ route('usulan.rab', $usulan) }}" class="space-y-5"
        x-data="rabEditor(@js($rows), @js((string) array_key_first($akunOptions)))">
        @csrf
        @method('PUT')

        <x-ui.card :padding="false">
            <div class="flex flex-col gap-1 border-b border-slate-200 px-5 py-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h2 class="text-sm font-semibold text-slate-900">Rencana Anggaran Biaya</h2>
                    <p class="text-sm text-slate-500">Jumlah = volume × harga satuan, dihitung otomatis.</p>
                </div>
                <div class="text-right">
                    <p class="text-xs text-slate-500">Total RAB</p>
                    <p class="text-xl font-semibold tabular-nums text-slate-900" x-text="rupiah(total)"></p>
                </div>
            </div>

            @if ($errors->has('items.*') || $errors->has('items'))
                <div class="border-b border-red-200 bg-red-50 px-5 py-3 text-sm text-red-700">
                    <p class="font-medium">Periksa kembali baris RAB:</p>
                    <ul class="mt-1 list-inside list-disc">
                        @foreach (collect($errors->getMessages())->filter(fn ($m, $k) => str_starts_with($k, 'items'))->flatten()->unique()->take(5) as $msg)
                            <li>{{ $msg }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (! $akunOptions)
                <p class="px-5 py-6 text-sm text-amber-700">Belum ada akun belanja aktif. Minta Administrator menambahkan akun belanja.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                            <tr>
                                <th class="w-8 px-3 py-2.5">#</th>
                                <th class="min-w-[14rem] px-2 py-2.5">Akun belanja</th>
                                <th class="min-w-[16rem] px-2 py-2.5">Uraian</th>
                                <th class="w-24 px-2 py-2.5">Volume</th>
                                <th class="w-28 px-2 py-2.5">Satuan</th>
                                <th class="w-40 px-2 py-2.5">Harga satuan (Rp)</th>
                                <th class="w-40 px-2 py-2.5 text-right">Jumlah</th>
                                <th class="w-10 px-2 py-2.5"><span class="sr-only">Hapus</span></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <template x-for="(row, i) in rows" :key="row.key">
                                <tr class="align-top">
                                    <td class="px-3 py-2.5 text-xs tabular-nums text-slate-400" x-text="i + 1"></td>
                                    <td class="px-2 py-2">
                                        <select :name="`items[${i}][kode_akun]`" x-model="row.kode_akun" required
                                            class="block w-full rounded-md border-0 py-1.5 pl-2 pr-8 text-sm ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-blue-600">
                                            @foreach ($akunOptions as $kode => $label)
                                                <option value="{{ $kode }}">{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="px-2 py-2">
                                        <input type="text" :name="`items[${i}][uraian]`" x-model="row.uraian" required maxlength="255" placeholder="mis. Konsumsi rapat"
                                            class="block w-full rounded-md border-0 px-2 py-1.5 text-sm ring-1 ring-inset ring-slate-300 placeholder:text-slate-400 focus:ring-2 focus:ring-blue-600">
                                    </td>
                                    <td class="px-2 py-2">
                                        <input type="number" :name="`items[${i}][volume]`" x-model.number="row.volume" required min="0.01" step="0.01"
                                            class="block w-full rounded-md border-0 px-2 py-1.5 text-right text-sm tabular-nums ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-blue-600">
                                    </td>
                                    <td class="px-2 py-2">
                                        <input type="text" :name="`items[${i}][satuan]`" x-model="row.satuan" required maxlength="50" placeholder="OK, Paket"
                                            list="satuan-umum"
                                            class="block w-full rounded-md border-0 px-2 py-1.5 text-sm ring-1 ring-inset ring-slate-300 placeholder:text-slate-400 focus:ring-2 focus:ring-blue-600">
                                    </td>
                                    <td class="px-2 py-2">
                                        <input type="hidden" :name="`items[${i}][harga_satuan]`" :value="row.harga_satuan ?? ''">
                                        <input type="text" inputmode="numeric" required :value="ribuan(row.harga_satuan)"
                                            @input="row.harga_satuan = angka($event.target.value); $event.target.value = ribuan(row.harga_satuan)"
                                            aria-label="Harga satuan"
                                            class="block w-full rounded-md border-0 px-2 py-1.5 text-right text-sm tabular-nums ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-blue-600">
                                    </td>
                                    <td class="px-2 py-2.5 text-right font-medium tabular-nums text-slate-900" x-text="rupiah(jumlah(row))"></td>
                                    <td class="px-2 py-2">
                                        <button type="button" @click="hapus(i)" title="Hapus baris"
                                            class="rounded-md p-1.5 text-slate-400 hover:bg-red-50 hover:text-red-600">
                                            <span class="sr-only">Hapus baris</span>
                                            <x-icon name="x" class="h-4 w-4" />
                                        </button>
                                    </td>
                                </tr>
                            </template>
                            <tr x-show="rows.length === 0">
                                <td colspan="8" class="px-5 py-10 text-center text-sm text-slate-500">Belum ada item. Tambahkan item pertama.</td>
                            </tr>
                        </tbody>
                        <tfoot class="border-t border-slate-200 bg-slate-50">
                            <tr>
                                <td colspan="6" class="px-3 py-3">
                                    <x-ui.button variant="secondary" size="sm" @click="tambah()">+ Tambah item</x-ui.button>
                                    <span class="ml-2 text-xs text-slate-500" x-text="`${rows.length} item`"></span>
                                </td>
                                <td class="px-2 py-3 text-right font-semibold tabular-nums text-slate-900" x-text="rupiah(total)"></td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <datalist id="satuan-umum">
                    @foreach (['OK', 'OH', 'OJ', 'OB', 'Paket', 'Kegiatan', 'Unit', 'Buah', 'Rim', 'Set', 'Kali', 'Dokumen'] as $s)
                        <option value="{{ $s }}"></option>
                    @endforeach
                </datalist>
            @endif
        </x-ui.card>

        <x-usulan.step-actions :usulan="$usulan" prev="kak" />
    </form>

    @push('scripts')
        <script>
            function rabEditor(initial, defaultAkun) {
                let seq = 0;
                const blank = () => ({ key: ++seq, kode_akun: defaultAkun ?? '', uraian: '', volume: 1, satuan: '', harga_satuan: null });

                return {
                    rows: initial.map(r => ({ key: ++seq, ...r })),
                    get total() {
                        return this.rows.reduce((sum, r) => sum + this.jumlah(r), 0);
                    },
                    jumlah(r) {
                        return Math.round((Number(r.volume) || 0) * (Number(r.harga_satuan) || 0) * 100) / 100;
                    },
                    tambah() {
                        this.rows.push(blank());
                        this.$nextTick(() => this.$root.querySelectorAll('tbody tr')[this.rows.length - 1]?.querySelector('input[type=text]')?.focus());
                    },
                    hapus(i) {
                        this.rows.splice(i, 1);
                    },
                    angka(text) {
                        const digits = String(text).replace(/[^0-9]/g, '');
                        return digits === '' ? null : Number(digits);
                    },
                    ribuan(n) {
                        return n === null || n === undefined || n === '' ? '' : Number(n).toLocaleString('id-ID');
                    },
                    rupiah(n) {
                        return 'Rp ' + Number(n || 0).toLocaleString('id-ID', { maximumFractionDigits: 2 });
                    },
                };
            }
        </script>
    @endpush
</x-usulan.wizard>
