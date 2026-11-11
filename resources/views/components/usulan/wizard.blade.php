@props(['usulan', 'step', 'steps', 'kelengkapan' => null])

@php
    $keys = array_keys($steps);
    $index = array_search($step, $keys, true);
@endphp

<x-app-layout>
    <x-slot name="title">{{ $usulan->exists ? $usulan->nomor : 'Usulan baru' }}</x-slot>
    <x-slot name="header"><span class="text-sm text-slate-500">Usulan Saya</span></x-slot>

    <div class="mx-auto max-w-none space-y-6 px-4 py-6 sm:px-6 lg:px-8">
        <x-ui.page-header :title="$usulan->exists ? ($usulan->judul_kegiatan ?: 'Tanpa judul') : 'Usulan anggaran baru'" :back="route('usulan.index')">
            <x-slot name="description">
                @if ($usulan->exists)
                    <span class="font-mono">{{ $usulan->nomor }}</span> · TA {{ $usulan->tahun->tahun }} ·
                    <x-ui.badge :color="$usulan->status->color()">{{ $usulan->status->label() }}</x-ui.badge>
                @else
                    Isi informasi kegiatan untuk membuat draft. Langkah berikutnya terbuka setelah draft tersimpan.
                @endif
            </x-slot>
            @if ($usulan->exists)
                <x-slot name="actions">
                    <x-ui.button variant="secondary" :href="route('usulan.show', $usulan)">Pratinjau</x-ui.button>
                </x-slot>
            @endif
        </x-ui.page-header>

        @if ($usulan->exists && $usulan->status === \App\Enums\StatusUsulan::PerluPerbaikan && ($catatan = $usulan->logs()->where('aksi', 'kembalikan')->latest('id')->first()))
            <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                <p class="font-semibold">Catatan perbaikan dari {{ ucfirst($catatan->tahap) }}</p>
                <p class="mt-1 whitespace-pre-line">{{ $catatan->catatan }}</p>
            </div>
        @endif

        {{-- Stepper --}}
        <nav aria-label="Langkah pengisian" class="-mx-4 overflow-x-auto px-4 sm:mx-0 sm:px-0">
            <ol class="flex min-w-max gap-2 sm:min-w-0 sm:grid sm:grid-cols-5">
                @foreach ($steps as $key => $label)
                    @php
                        $i = $loop->index;
                        $current = $key === $step;
                        $done = $kelengkapan?->get($key)['ok'] ?? false;
                        $reachable = $usulan->exists;
                    @endphp
                    <li>
                        <a @if ($reachable) href="{{ route('usulan.edit', [$usulan, $key]) }}" @endif
                            @if ($current) aria-current="step" @endif
                            @class([
                                'flex h-full items-center gap-3 rounded-lg border px-3 py-2.5 text-sm transition-colors',
                                'border-blue-600 bg-blue-50 text-blue-800' => $current,
                                'border-slate-200 bg-white text-slate-700 hover:border-slate-300' => ! $current && $reachable,
                                'cursor-not-allowed border-dashed border-slate-200 bg-white text-slate-400' => ! $reachable && ! $current,
                            ])>
                            <span @class([
                                'flex h-6 w-6 shrink-0 items-center justify-center rounded-full text-xs font-semibold',
                                'bg-blue-600 text-white' => $current,
                                'bg-emerald-100 text-emerald-700' => ! $current && $done,
                                'bg-slate-100 text-slate-500' => ! $current && ! $done,
                            ])>
                                @if ($done && ! $current)
                                    <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" /></svg>
                                    <span class="sr-only">Lengkap</span>
                                @else
                                    {{ $i + 1 }}
                                @endif
                            </span>
                            <span class="font-medium leading-tight">{{ $label }}</span>
                        </a>
                    </li>
                @endforeach
            </ol>
        </nav>

        {{ $slot }}
    </div>
</x-app-layout>
