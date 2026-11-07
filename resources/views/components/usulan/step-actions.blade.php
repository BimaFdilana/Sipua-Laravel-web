@props(['usulan', 'prev' => null, 'next' => true, 'nextLabel' => 'Simpan & lanjut'])

{{-- Tombol di kaki setiap langkah wizard. Nilai name="aksi" menentukan tujuan setelah simpan. --}}
<div class="flex flex-col-reverse gap-2 sm:flex-row sm:items-center sm:justify-between">
    <div>
        @if ($prev && $usulan->exists)
            <x-ui.button variant="ghost" :href="route('usulan.edit', [$usulan, $prev])">&larr; Sebelumnya</x-ui.button>
        @endif
    </div>
    <div class="flex flex-col-reverse gap-2 sm:flex-row">
        @if ($usulan->exists)
            <x-ui.button type="submit" name="aksi" value="simpan" variant="secondary">Simpan draft</x-ui.button>
        @endif
        @if ($next)
            <x-ui.button type="submit" name="aksi" value="lanjut">{{ $nextLabel }} &rarr;</x-ui.button>
        @endif
    </div>
</div>
