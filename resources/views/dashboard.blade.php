<x-app-layout>
    <x-slot name="title">Dashboard</x-slot>
    <x-slot name="header">
        <h1 class="text-base font-semibold text-slate-900">Dashboard</h1>
    </x-slot>

    <div class="mx-auto max-w-7xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">

        <section>
            <p class="text-sm text-slate-500">Selamat datang,</p>
            <h2 class="text-2xl font-semibold tracking-tight text-slate-900">{{ $user->name }}</h2>
            <p class="mt-1 text-sm text-slate-600">
                {{ $user->roleLabel() }}@if ($user->unit) · {{ $user->unit->nama_unit }}@endif
                · Tahun anggaran aktif:
                <span class="font-medium text-slate-900">{{ $tahunAktif->tahun ?? 'belum ditetapkan' }}</span>
            </p>
        </section>

        @if ($stats)
            <section class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                @foreach ($stats as $stat)
                    <a href="{{ route($stat['route']) }}"
                        class="group rounded-xl border border-slate-200 bg-white p-5 transition hover:border-blue-300 hover:shadow-sm">
                        <div class="flex items-center justify-between">
                            <p class="text-sm font-medium text-slate-500">{{ $stat['label'] }}</p>
                            <x-icon :name="$stat['icon']" class="h-5 w-5 text-slate-400 group-hover:text-blue-600" />
                        </div>
                        <p class="mt-3 text-3xl font-semibold tabular-nums text-slate-900">{{ number_format($stat['value'], 0, ',', '.') }}</p>
                    </a>
                @endforeach
            </section>
        @else
            <section class="rounded-xl border border-dashed border-slate-300 bg-white p-6">
                <h3 class="text-sm font-semibold text-slate-900">Ringkasan usulan belum tersedia</h3>
                <p class="mt-1 max-w-prose text-sm text-slate-600">
                    Fitur pengajuan, verifikasi, dan dashboard untuk peran {{ $user->roleLabel() }} sedang dibangun.
                    Menu akan muncul di sidebar begitu fiturnya siap.
                </p>
            </section>
        @endif
    </div>
</x-app-layout>
