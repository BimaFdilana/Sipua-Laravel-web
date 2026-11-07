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
                Administrator · Tahun anggaran aktif:
                <span class="font-medium text-slate-900">{{ $tahunAktif->tahun ?? 'belum ditetapkan' }}</span>
                @unless ($tahunAktif)
                    · <a href="{{ route('tahun.index') }}" class="font-medium text-blue-700 hover:underline">Atur sekarang</a>
                @endunless
            </p>
        </section>

        <section class="grid grid-cols-2 gap-4 lg:grid-cols-4">
            @foreach ($stats as $stat)
                <a href="{{ route($stat['route']) }}"
                    class="group rounded-xl border border-slate-200 bg-white p-5 transition hover:border-blue-300 hover:shadow-sm">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-medium text-slate-500">{{ $stat['label'] }}</p>
                        <x-icon :name="$stat['icon']" class="h-5 w-5 text-slate-400 group-hover:text-blue-600" />
                    </div>
                    <p class="mt-3 text-3xl font-semibold tabular-nums text-slate-900">{{ number_format($stat['value'], 0, ',', '.') }}</p>
                    <p class="mt-1 text-xs text-slate-500">{{ $stat['sub'] }}</p>
                </a>
            @endforeach
        </section>

        <x-ui.card title="Pengguna per peran" :description="$jumlahUsulan.' usulan tercatat di sistem.'">
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
                @foreach (\App\Models\User::ROLES as $kode => $label)
                    <a href="{{ route('users.index', ['role' => $kode]) }}" class="rounded-lg bg-slate-50 px-3 py-2.5 hover:bg-slate-100">
                        <p class="text-xs text-slate-500">{{ $label }}</p>
                        <p class="text-lg font-semibold tabular-nums text-slate-900">{{ $perPeran[$kode] ?? 0 }}</p>
                    </a>
                @endforeach
            </div>
        </x-ui.card>
    </div>
</x-app-layout>
