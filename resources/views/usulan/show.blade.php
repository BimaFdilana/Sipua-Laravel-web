@php
    use App\Support\Format;
@endphp

<x-app-layout>
    <x-slot name="title">{{ $usulan->nomor }}</x-slot>
    <x-slot name="header"><span class="text-sm text-slate-500">Detail usulan</span></x-slot>

    <div class="mx-auto max-w-7xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">
        <x-ui.page-header :title="$usulan->judul_kegiatan" :back="auth()->user()->hasRole('pengusul') ? route('usulan.index') : route('verifikasi.index')">
            <x-slot name="description">
                <span class="font-mono">{{ $usulan->nomor }}</span> · {{ $usulan->unit->nama_unit }} · TA {{ $usulan->tahun->tahun }}
            </x-slot>
            <x-slot name="actions">
                @can('update', $usulan)
                    <x-ui.button :href="route('usulan.edit', $usulan)">
                        {{ $usulan->status === \App\Enums\StatusUsulan::PerluPerbaikan ? 'Perbaiki usulan' : 'Ubah usulan' }}
                    </x-ui.button>
                @endcan
            </x-slot>
        </x-ui.page-header>

        {{-- Ringkasan --}}
        <section class="grid grid-cols-2 gap-3 lg:grid-cols-4">
            <div class="rounded-xl border border-slate-200 bg-white px-4 py-3">
                <p class="text-xs font-medium text-slate-500">Status</p>
                <x-ui.badge :color="$usulan->status->color()" class="mt-1.5 text-sm">{{ $usulan->status->label() }}</x-ui.badge>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white px-4 py-3">
                <p class="text-xs font-medium text-slate-500">Total RAB</p>
                <p class="mt-1 text-lg font-semibold tabular-nums text-slate-900">{{ Format::rupiah($usulan->total_rab) }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white px-4 py-3">
                <p class="text-xs font-medium text-slate-500">Diajukan</p>
                <p class="mt-1 text-sm font-medium text-slate-900">{{ Format::tanggal($usulan->submitted_at, true) }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white px-4 py-3">
                <p class="text-xs font-medium text-slate-500">Prioritas</p>
                @if ($usulan->prioritas)
                    <x-ui.badge :color="\App\Models\Usulan::PRIORITAS[$usulan->prioritas]" class="mt-1.5 text-sm">{{ $usulan->prioritas }}</x-ui.badge>
                @else
                    <p class="mt-1 text-sm font-medium text-slate-400">Belum ditetapkan</p>
                @endif
            </div>
        </section>

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="space-y-6 lg:col-span-2">
                {{-- Informasi kegiatan --}}
                <x-ui.card title="Informasi kegiatan">
                    <dl class="grid gap-x-6 gap-y-4 text-sm sm:grid-cols-2">
                        @foreach ([
                            'Program' => $usulan->program,
                            'Hasil (outcome)' => $usulan->hasil,
                            'Jenis output' => $usulan->jenis_output,
                            'Volume output' => $usulan->volume_output ? Format::angka($usulan->volume_output).' '.$usulan->satuan_output : null,
                            'Waktu pelaksanaan' => $usulan->waktu_mulai ? Format::tanggal($usulan->waktu_mulai).' – '.Format::tanggal($usulan->waktu_selesai) : null,
                            'Dibuat oleh' => $usulan->pembuat->name,
                        ] as $label => $nilai)
                            <div>
                                <dt class="text-xs font-medium text-slate-500">{{ $label }}</dt>
                                <dd class="mt-0.5 text-slate-900">{{ $nilai ?: '—' }}</dd>
                            </div>
                        @endforeach
                    </dl>
                </x-ui.card>

                {{-- KAK/TOR --}}
                <x-ui.card title="Kerangka Acuan Kerja (KAK/TOR)">
                    <dl class="space-y-5 text-sm">
                        @foreach (\App\Models\Usulan::WAJIB_KAK as $field => $label)
                            <div>
                                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ $label }}</dt>
                                <dd class="mt-1 whitespace-pre-line leading-relaxed text-slate-800">{{ $usulan->{$field} ?: '—' }}</dd>
                            </div>
                        @endforeach
                    </dl>
                </x-ui.card>

                {{-- RAB --}}
                <x-ui.card title="Rencana Anggaran Biaya" :padding="false">
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                <tr>
                                    <th class="px-4 py-2.5">Akun</th>
                                    <th class="px-4 py-2.5">Uraian</th>
                                    <th class="px-4 py-2.5 text-right">Volume</th>
                                    <th class="px-4 py-2.5 text-right">Harga satuan</th>
                                    <th class="px-4 py-2.5 text-right">Jumlah</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse ($usulan->rabItems as $item)
                                    <tr>
                                        <td class="whitespace-nowrap px-4 py-2.5 font-mono text-xs" title="{{ $item->akun?->nama_akun }}">{{ $item->kode_akun }}</td>
                                        <td class="px-4 py-2.5">{{ $item->uraian }}</td>
                                        <td class="whitespace-nowrap px-4 py-2.5 text-right tabular-nums">{{ Format::angka($item->volume) }} {{ $item->satuan }}</td>
                                        <td class="whitespace-nowrap px-4 py-2.5 text-right tabular-nums">{{ Format::rupiah($item->harga_satuan) }}</td>
                                        <td class="whitespace-nowrap px-4 py-2.5 text-right font-medium tabular-nums">{{ Format::rupiah($item->jumlah) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="px-4 py-8 text-center text-slate-500">Belum ada item RAB.</td></tr>
                                @endforelse
                            </tbody>
                            @if ($usulan->rabItems->isNotEmpty())
                                <tfoot class="border-t border-slate-200 bg-slate-50">
                                    <tr>
                                        <td colspan="4" class="px-4 py-2.5 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Total</td>
                                        <td class="whitespace-nowrap px-4 py-2.5 text-right font-semibold tabular-nums text-slate-900">{{ Format::rupiah($usulan->total_rab) }}</td>
                                    </tr>
                                </tfoot>
                            @endif
                        </table>
                    </div>
                </x-ui.card>
            </div>

            <div class="space-y-6">
                <x-usulan.panel-verifikasi :usulan="$usulan" />

                {{-- IKU --}}
                <x-ui.card title="IKU terkait">
                    @forelse ($usulan->ikus as $iku)
                        <div class="flex gap-2 py-1.5 text-sm">
                            <span class="font-mono text-xs font-semibold text-slate-900">{{ $iku->kode_iku }}</span>
                            <span class="flex-1 text-slate-700">{{ $iku->nama_iku }}</span>
                            <x-ui.badge :color="$iku->sifat === 'Wajib' ? 'amber' : 'slate'">{{ $iku->sifat }}</x-ui.badge>
                        </div>
                    @empty
                        <p class="text-sm text-slate-500">Belum ada IKU dipilih.</p>
                    @endforelse
                </x-ui.card>

                {{-- Dokumen --}}
                <x-ui.card title="Data dukung">
                    <ul class="space-y-2">
                        @forelse ($usulan->dokumens as $dok)
                            <li class="flex items-center gap-3">
                                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded bg-red-50 text-[10px] font-bold text-red-600">PDF</span>
                                <div class="min-w-0">
                                    <a href="{{ route('usulan.dokumen.download', [$usulan, $dok]) }}" target="_blank"
                                        class="block truncate text-sm font-medium text-slate-900 hover:text-blue-700 hover:underline">{{ $dok->nama_asli }}</a>
                                    <p class="text-xs text-slate-500">{{ $dok->kategoriLabel() }} · {{ $dok->ukuranLabel() }}</p>
                                </div>
                            </li>
                        @empty
                            <li class="text-sm text-slate-500">Belum ada dokumen.</li>
                        @endforelse
                    </ul>
                </x-ui.card>

                {{-- Riwayat --}}
                <x-ui.card title="Riwayat status">
                    <ol class="relative space-y-5 border-l border-slate-200 pl-5">
                        @foreach ($usulan->logs->reverse() as $log)
                            <li class="relative">
                                <span @class([
                                    'absolute -left-[26px] top-1 h-3 w-3 rounded-full ring-4 ring-white',
                                    'bg-blue-600' => $loop->first,
                                    'bg-slate-300' => ! $loop->first,
                                ])></span>
                                <p class="text-sm font-medium text-slate-900">{{ $log->aksiLabel() }}</p>
                                <p class="text-xs text-slate-500">
                                    {{ $log->user->name }} · {{ Format::tanggal($log->created_at, true) }}
                                </p>
                                <x-ui.badge :color="$log->status_ke->color()" class="mt-1">{{ $log->status_ke->label() }}</x-ui.badge>
                                @if ($log->catatan)
                                    <p class="mt-1.5 whitespace-pre-line rounded-md bg-slate-50 px-2.5 py-1.5 text-xs text-slate-700">{{ $log->catatan }}</p>
                                @endif
                            </li>
                        @endforeach
                    </ol>
                </x-ui.card>
            </div>
        </div>
    </div>
</x-app-layout>
