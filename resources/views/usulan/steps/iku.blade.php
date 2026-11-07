<x-usulan.wizard :usulan="$usulan" :step="$step" :steps="$steps" :kelengkapan="$kelengkapan">
    <form method="POST" action="{{ route('usulan.iku', $usulan) }}" class="space-y-5"
        x-data="{ dipilih: @js(array_map('intval', old('ikus', $terpilih))) }">
        @csrf
        @method('PUT')

        <x-ui.card title="Keterkaitan dengan IKU" description="Pilih satu atau lebih IKU tahun {{ $usulan->tahun->tahun }} yang didukung kegiatan ini.">
            <div x-show="dipilih.length === 0" class="mb-4 flex gap-2 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2.5 text-sm text-amber-900">
                <x-icon name="alert" class="mt-0.5 h-4 w-4" />
                <p>Usulan belum terkait IKU. Minimal satu IKU harus dipilih sebelum usulan dikirim.</p>
            </div>

            @error('ikus.*')
                <p class="mb-4 text-sm text-red-600">{{ $message }}</p>
            @enderror

            @forelse ($ikus->groupBy('sifat') as $sifat => $daftar)
                <fieldset class="mb-5 last:mb-0">
                    <legend class="mb-2 flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-slate-500">
                        IKU {{ $sifat }}
                        @if ($sifat === 'Wajib')
                            <span class="font-normal normal-case tracking-normal text-slate-400">— harus didukung oleh usulan institusi</span>
                        @endif
                    </legend>
                    <div class="space-y-2">
                        @foreach ($daftar as $iku)
                            <label class="flex cursor-pointer gap-3 rounded-lg border border-slate-200 p-3 hover:border-slate-300 has-[:checked]:border-blue-600 has-[:checked]:bg-blue-50/50">
                                <input type="checkbox" name="ikus[]" value="{{ $iku->id }}" x-model.number="dipilih"
                                    class="mt-0.5 rounded border-slate-300 text-blue-600 focus:ring-blue-600">
                                <span class="min-w-0">
                                    <span class="flex flex-wrap items-center gap-2">
                                        <span class="font-mono text-xs font-semibold text-slate-900">{{ $iku->kode_iku }}</span>
                                        <span class="text-sm font-medium text-slate-900">{{ $iku->nama_iku }}</span>
                                    </span>
                                    @if ($iku->target)
                                        <span class="mt-0.5 block text-xs text-slate-500">Target {{ $iku->target }}</span>
                                    @endif
                                </span>
                            </label>
                        @endforeach
                    </div>
                </fieldset>
            @empty
                <p class="text-sm text-slate-500">Belum ada IKU untuk tahun {{ $usulan->tahun->tahun }}. Hubungi Administrator.</p>
            @endforelse

            <p class="mt-4 text-xs text-slate-500" x-text="`${dipilih.length} IKU dipilih`"></p>
        </x-ui.card>

        <x-usulan.step-actions :usulan="$usulan" prev="rab" />
    </form>
</x-usulan.wizard>
