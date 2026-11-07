<x-usulan.wizard :usulan="$usulan" :step="$step" :steps="$steps" :kelengkapan="$kelengkapan ?? null">
    <form method="POST" action="{{ $usulan->exists ? route('usulan.info', $usulan) : route('usulan.store') }}" class="space-y-5">
        @csrf
        @if ($usulan->exists) @method('PUT') @endif

        <x-ui.card title="Informasi kegiatan" description="Tanda * wajib diisi sebelum usulan dikirim. Draft boleh disimpan walau belum lengkap.">
            <div class="grid gap-5 sm:grid-cols-6">
                <div class="rounded-lg bg-slate-50 px-3 py-2.5 text-sm sm:col-span-3">
                    <p class="text-xs text-slate-500">Unit kerja</p>
                    <p class="font-medium text-slate-900">{{ ($usulan->unit ?? auth()->user()->unit)?->nama_unit }}</p>
                </div>
                <div class="rounded-lg bg-slate-50 px-3 py-2.5 text-sm sm:col-span-3">
                    <p class="text-xs text-slate-500">Tahun anggaran</p>
                    <p class="font-medium text-slate-900">{{ $usulan->tahun->tahun ?? \App\Models\Tahun::aktif()?->tahun }}</p>
                </div>

                <x-ui.field label="Nama / judul kegiatan" name="judul_kegiatan" required class="sm:col-span-6">
                    <x-ui.input name="judul_kegiatan" :value="$usulan->judul_kegiatan" placeholder="mis. Pelatihan Sertifikasi Kompetensi Mahasiswa" autofocus />
                </x-ui.field>
                <x-ui.field label="Program" name="program" required class="sm:col-span-3">
                    <x-ui.input name="program" :value="$usulan->program" placeholder="mis. Program Pendidikan Tinggi" />
                </x-ui.field>
                <x-ui.field label="Hasil (outcome)" name="hasil" required class="sm:col-span-3">
                    <x-ui.input name="hasil" :value="$usulan->hasil" />
                </x-ui.field>
                <x-ui.field label="Jenis output" name="jenis_output" required class="sm:col-span-6">
                    <x-ui.input name="jenis_output" :value="$usulan->jenis_output" placeholder="mis. Mahasiswa tersertifikasi" />
                </x-ui.field>
                <x-ui.field label="Volume output" name="volume_output" required class="sm:col-span-3">
                    <x-ui.input type="number" name="volume_output" :value="$usulan->volume_output ? (float) $usulan->volume_output : null" step="0.01" min="0" />
                </x-ui.field>
                <x-ui.field label="Satuan output" name="satuan_output" required class="sm:col-span-3">
                    <x-ui.input name="satuan_output" :value="$usulan->satuan_output" placeholder="mis. Orang, Dokumen, Paket" />
                </x-ui.field>
                <x-ui.field label="Waktu mulai" name="waktu_mulai" required class="sm:col-span-3">
                    <x-ui.input type="date" name="waktu_mulai" :value="$usulan->waktu_mulai?->format('Y-m-d')" />
                </x-ui.field>
                <x-ui.field label="Waktu selesai" name="waktu_selesai" required class="sm:col-span-3">
                    <x-ui.input type="date" name="waktu_selesai" :value="$usulan->waktu_selesai?->format('Y-m-d')" />
                </x-ui.field>
            </div>
        </x-ui.card>

        <x-usulan.step-actions :usulan="$usulan" :next-label="$usulan->exists ? 'Simpan & lanjut' : 'Buat draft & lanjut'" />
    </form>
</x-usulan.wizard>
