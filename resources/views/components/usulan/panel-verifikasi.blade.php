@props(['usulan'])

@php
    use App\Models\Usulan;
    use App\Services\UsulanWorkflow;
    use App\Support\Format;

    $user = auth()->user();
    $bisaVerifikasi = $user->can('verify', $usulan);
    $bisaPrioritas = $user->can('setPrioritas', $usulan);
    $aksiValid = array_keys(UsulanWorkflow::TRANSISI[$usulan->status->value] ?? []);

    $tujuan = [
        'diajukan' => 'Wadir', 'di_wadir' => 'SPI', 'di_spi' => 'Direktur',
    ][$usulan->status->value] ?? null;

    $opsi = [
        'teruskan' => ['Setujui & teruskan', $tujuan ? "Usulan diteruskan ke {$tujuan}." : '', 'border-emerald-600 bg-emerald-50/60'],
        'sahkan' => ['Sahkan usulan', 'Status menjadi final Disahkan dan dapat dicetak sebagai RKAKL.', 'border-emerald-600 bg-emerald-50/60'],
        'kembalikan' => ['Kembalikan untuk perbaikan', 'Pengusul memperbaiki lalu mengirim ulang ke Perencanaan.', 'border-amber-500 bg-amber-50/60'],
        'tolak' => ['Tolak usulan', 'Status final Ditolak. Alur berhenti.', 'border-red-600 bg-red-50/60'],
    ];

    // Hasil keputusan terakhir dari setiap tahap verifikasi (US-21, US-23).
    $hasil = $usulan->logs
        ->whereIn('aksi', ['teruskan', 'kembalikan', 'tolak', 'sahkan'])
        ->groupBy('tahap')
        ->map->last();
    $logPrioritas = $usulan->logs->where('aksi', 'prioritas')->last();
@endphp

{{-- Hasil verifikasi tahap sebelumnya --}}
@if ($hasil->isNotEmpty() || $logPrioritas)
    <x-ui.card title="Hasil verifikasi">
        <ul class="space-y-4">
            @foreach (['perencanaan' => 'Perencanaan', 'wadir' => 'Wadir', 'spi' => 'SPI', 'direktur' => 'Direktur'] as $tahap => $label)
                @if ($log = $hasil->get($tahap))
                    @php($warna = ['teruskan' => 'green', 'sahkan' => 'green', 'kembalikan' => 'amber', 'tolak' => 'red'][$log->aksi])
                    <li class="text-sm">
                        <div class="flex items-center justify-between gap-2">
                            <span class="font-medium text-slate-900">{{ $label }}</span>
                            <x-ui.badge :color="$warna">{{ ['teruskan' => 'Disetujui', 'sahkan' => 'Disahkan', 'kembalikan' => 'Dikembalikan', 'tolak' => 'Ditolak'][$log->aksi] }}</x-ui.badge>
                        </div>
                        <p class="text-xs text-slate-500">{{ $log->user->name }} · {{ Format::tanggal($log->created_at, true) }}</p>
                        @if ($log->catatan)
                            <p class="mt-1 whitespace-pre-line rounded-md bg-slate-50 px-2.5 py-1.5 text-xs text-slate-700">{{ $log->catatan }}</p>
                        @endif
                    </li>
                @endif
            @endforeach
            @if ($logPrioritas)
                <li class="border-t border-slate-100 pt-3 text-xs text-slate-500">
                    Prioritas ditetapkan {{ $logPrioritas->user->name }} · {{ Format::tanggal($logPrioritas->created_at, true) }}
                </li>
            @endif
        </ul>
    </x-ui.card>
@endif

{{-- Prioritas (Perencanaan) --}}
@if ($bisaPrioritas)
    @php($ikuWajib = $usulan->ikus->where('sifat', 'Wajib')->count())
    <x-ui.card title="Tingkat prioritas" description="Wajib ditetapkan sebelum usulan diteruskan ke Wadir.">
        <form method="POST" action="{{ route('verifikasi.prioritas', $usulan) }}" class="space-y-3">
            @csrf
            <div class="grid grid-cols-3 gap-2" role="radiogroup" aria-label="Tingkat prioritas">
                @foreach (Usulan::PRIORITAS as $p => $warna)
                    <label class="cursor-pointer">
                        <input type="radio" name="prioritas" value="{{ $p }}" class="peer sr-only" @checked(old('prioritas', $usulan->prioritas) === $p)>
                        <span class="block rounded-md border border-slate-200 px-2 py-2 text-center text-sm font-medium text-slate-700 peer-checked:border-blue-600 peer-checked:bg-blue-50 peer-checked:text-blue-800 peer-focus-visible:ring-2 peer-focus-visible:ring-blue-500">{{ $p }}</span>
                    </label>
                @endforeach
            </div>
            @error('prioritas')
                <p class="text-sm text-red-600">{{ $message }}</p>
            @enderror
            <p class="text-xs text-slate-500">
                Pertimbangan: usulan ini mendukung <strong class="text-slate-700">{{ $ikuWajib }} IKU wajib</strong>
                dari {{ $usulan->ikus->count() }} IKU terpilih.
            </p>
            <x-ui.textarea name="alasan_prioritas" rows="2" placeholder="Alasan (opsional)" />
            <x-ui.button type="submit" variant="secondary" class="w-full">{{ $usulan->prioritas ? 'Ubah prioritas' : 'Simpan prioritas' }}</x-ui.button>
        </form>
    </x-ui.card>
@endif

{{-- Keputusan verifikasi --}}
@if ($bisaVerifikasi)
    <x-ui.card :title="$user->hasRole('direktur') ? 'Pengesahan' : 'Keputusan verifikasi'" class="ring-2 ring-blue-600/20">
        @if ($user->hasRole('perencanaan'))
            {{-- US-16: kelengkapan administrasi --}}
            <div class="mb-4 rounded-lg bg-slate-50 p-3">
                <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-500">Kelengkapan dokumen</p>
                <ul class="space-y-1.5">
                    @foreach ($usulan->kelengkapan() as $cek)
                        <li class="flex items-start gap-2 text-sm">
                            @if ($cek['ok'])
                                <x-icon name="check-circle" class="h-4 w-4 text-emerald-600" />
                            @else
                                <x-icon name="alert" class="h-4 w-4 text-amber-600" />
                            @endif
                            <span @class(['text-slate-700', 'text-amber-800' => ! $cek['ok']])>{{ $cek['label'] }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('verifikasi.proses', $usulan) }}"
            x-data="{ keputusan: @js(old('keputusan')), konfirmasi: false }" class="space-y-4">
            @csrf
            <input type="hidden" name="status" value="{{ $usulan->status->value }}">

            <fieldset class="space-y-2">
                <legend class="sr-only">Keputusan</legend>
                @foreach ($aksiValid as $aksi)
                    @php([$label, $bantuan, $aktif] = $opsi[$aksi])
                    @php($terkunci = $aksi === 'teruskan' && $user->hasRole('perencanaan') && ! $usulan->prioritas)
                    <label @class(['flex gap-3 rounded-lg border border-slate-200 p-3', 'cursor-pointer hover:border-slate-300' => ! $terkunci, 'cursor-not-allowed opacity-60' => $terkunci])
                        :class="keputusan === '{{ $aksi }}' && '{{ $aktif }}'">
                        <input type="radio" name="keputusan" value="{{ $aksi }}" x-model="keputusan" @disabled($terkunci)
                            class="mt-0.5 border-slate-300 text-blue-600 focus:ring-blue-600">
                        <span>
                            <span class="block text-sm font-medium text-slate-900">{{ $label }}</span>
                            <span class="block text-xs text-slate-500">{{ $terkunci ? 'Tetapkan prioritas dulu.' : $bantuan }}</span>
                        </span>
                    </label>
                @endforeach
            </fieldset>
            @error('keputusan')
                <p class="text-sm text-red-600">{{ $message }}</p>
            @enderror

            <div>
                <label for="catatan" class="block text-sm font-medium text-slate-700">
                    <span x-text="keputusan === 'kembalikan' ? 'Catatan perbaikan' : (keputusan === 'tolak' ? 'Alasan penolakan' : 'Catatan / rekomendasi')">Catatan / rekomendasi</span>
                    <span x-show="['kembalikan', 'tolak'].includes(keputusan)" x-cloak class="text-red-500">*</span>
                </label>
                <x-ui.textarea name="catatan" rows="3" class="mt-1.5"
                    ::required="['kembalikan', 'tolak'].includes(keputusan)"
                    ::placeholder="keputusan === 'kembalikan' ? 'Tuliskan bagian yang harus diperbaiki Pengusul…' : 'Opsional'" />
                @error('catatan')
                    <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <template x-if="! konfirmasi">
                <x-ui.button class="w-full" ::disabled="! keputusan" @click="konfirmasi = true">Lanjutkan</x-ui.button>
            </template>
            <template x-if="konfirmasi">
                <div class="space-y-2 rounded-lg border border-slate-200 bg-slate-50 p-3">
                    <p class="text-sm text-slate-700">Keputusan ini tidak bisa dibatalkan. Yakin?</p>
                    <div class="flex gap-2">
                        <x-ui.button variant="secondary" class="flex-1" @click="konfirmasi = false">Batal</x-ui.button>
                        <x-ui.button type="submit" class="flex-1" ::class="['kembalikan', 'tolak'].includes(keputusan) && '!bg-red-600 hover:!bg-red-700'">Ya, simpan</x-ui.button>
                    </div>
                </div>
            </template>
        </form>
    </x-ui.card>
@endif
