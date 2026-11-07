@php
    use App\Enums\StatusUsulan;
    use App\Models\Usulan;
    use App\Support\Format;

    $query = request()->except('format');
    $jumlah = match ($jenis) {
        'iku' => $perIku->count().' IKU · '.$tanpaUsulan.' belum punya usulan',
        default => $rows->count().' usulan',
    };
@endphp

<x-app-layout>
    <x-slot name="title">{{ $judul }}</x-slot>
    <x-slot name="header"><span class="text-sm text-slate-500">Laporan</span></x-slot>

    @push('scripts')
        @include('laporan._style')
    @endpush

    <div class="mx-auto max-w-7xl space-y-5 px-4 py-6 sm:px-6 lg:px-8">
        <x-ui.page-header :title="$judul" :description="$jumlah.' · total '.Format::rupiah($total)">
            <x-slot name="actions">
                <x-ui.button variant="secondary" :href="route('laporan.show', [$jenis] + $query + ['format' => 'cetak'])" target="_blank">Cetak</x-ui.button>
                <x-ui.button variant="secondary" :href="route('laporan.show', [$jenis] + $query + ['format' => 'xlsx'])">Excel</x-ui.button>
                <x-ui.button :href="route('laporan.show', [$jenis] + $query + ['format' => 'pdf'])">Unduh PDF</x-ui.button>
            </x-slot>
        </x-ui.page-header>

        {{-- US-36 AC1: pilih jenis laporan --}}
        @if ($tersedia->count() > 1)
            <nav class="flex flex-wrap gap-1 border-b border-slate-200" aria-label="Jenis laporan">
                @foreach ($tersedia as $key => $label)
                    <a href="{{ route('laporan.show', [$key] + request()->only('tahun', 'unit')) }}" @if ($jenis === $key) aria-current="page" @endif
                        @class(['-mb-px border-b-2 px-3 py-2 text-sm font-medium',
                            'border-blue-600 text-blue-700' => $jenis === $key,
                            'border-transparent text-slate-500 hover:text-slate-800' => $jenis !== $key])>{{ $label }}</a>
                @endforeach
            </nav>
        @endif

        {{-- US-36 AC2: filter --}}
        <form method="GET" class="flex flex-col gap-2 sm:flex-row sm:flex-wrap sm:items-center">
            @if (in_array('tahun', $filters))
                <x-ui.select name="tahun" :options="$tahunOptions" :value="$tahun?->id" :placeholder="$jenis === 'iku' ? null : 'Semua tahun'" class="sm:w-36" aria-label="Tahun anggaran" />
            @endif
            @if (in_array('unit', $filters))
                <x-ui.select name="unit" :options="$unitOptions" :value="request('unit')" placeholder="Semua unit" class="sm:w-60" aria-label="Unit kerja" />
            @endif
            @if (in_array('status', $filters))
                <x-ui.select name="status" :options="StatusUsulan::options()" :value="request('status')" placeholder="Semua status" class="sm:w-48" aria-label="Status" />
            @endif
            @if (in_array('prioritas', $filters))
                <x-ui.select name="prioritas" :options="array_combine(array_keys(Usulan::PRIORITAS), array_keys(Usulan::PRIORITAS))" :value="request('prioritas')" placeholder="Semua prioritas" class="sm:w-40" aria-label="Prioritas" />
            @endif
            <x-ui.button type="submit" variant="secondary">Terapkan</x-ui.button>
            @if (collect(request()->except('page'))->filter()->isNotEmpty())
                <x-ui.button variant="ghost" :href="route('laporan.show', $jenis)">Reset</x-ui.button>
            @endif
        </form>

        <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white p-4">
            @include('laporan._tabel')
        </div>
        <p class="text-xs text-slate-500">Cetak, PDF, dan Excel berisi data yang sama dengan tabel di atas, lengkap dengan tahun anggaran dan tanggal pembuatan.</p>
    </div>
</x-app-layout>
