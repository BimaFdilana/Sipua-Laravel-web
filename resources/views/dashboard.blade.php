@php
    use App\Models\Usulan;
    use App\Support\Format;

    $aksen = [
        'blue' => 'before:bg-blue-500', 'amber' => 'before:bg-amber-500', 'green' => 'before:bg-emerald-500',
        'red' => 'before:bg-red-500', 'slate' => 'before:bg-slate-300',
    ];
    $statusTampil = $perStatus->filter(fn ($s) => $s['jumlah'] > 0);
    $totalJumlah = max(1, $perStatus->sum('jumlah'));
    $warnaStatus = ['slate' => 'bg-slate-400', 'blue' => 'bg-blue-500', 'amber' => 'bg-amber-500', 'green' => 'bg-emerald-500', 'red' => 'bg-red-500'];
    $isPengusul = $user->hasRole('pengusul');
@endphp

<x-app-layout>
    <x-slot name="title">Dashboard</x-slot>
    <x-slot name="header">
        <h1 class="text-base font-semibold text-slate-900">Dashboard</h1>
    </x-slot>

    <div class="mx-auto max-w-7xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">

        {{-- Sapaan + pilih tahun --}}
        <section class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm text-slate-500">Selamat datang,</p>
                <h2 class="text-2xl font-semibold tracking-tight text-slate-900">{{ $user->name }}</h2>
                <p class="mt-1 text-sm text-slate-600">{{ $user->roleLabel() }}@if ($user->unit) · {{ $user->unit->nama_unit }}@endif</p>
            </div>
            <form method="GET" class="flex items-center gap-2">
                <label for="tahun" class="text-sm text-slate-500">Tahun anggaran</label>
                <x-ui.select name="tahun" :options="$tahunOptions" :value="$tahun?->id" class="w-28" onchange="this.form.submit()" />
                <noscript><x-ui.button type="submit" variant="secondary">Tampilkan</x-ui.button></noscript>
            </form>
        </section>

        @if ($tahun && $tahunAktif && $tahun->id !== $tahunAktif->id)
            <p class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-600">
                Menampilkan data tahun {{ $tahun->tahun }}. Tahun anggaran aktif: {{ $tahunAktif->tahun }}.
                <a href="{{ route('dashboard') }}" class="font-medium text-blue-700 hover:underline">Kembali ke tahun aktif</a>
            </p>
        @endif

        {{-- KPI --}}
        <section @class(["grid grid-cols-2 gap-3 md:grid-cols-3", "xl:grid-cols-5" => count($kpi) === 5, "xl:grid-cols-6" => count($kpi) === 6])>
            @foreach ($kpi as $k)
                @php($tag = $k['link'] ? 'a' : 'div')
                <{{ $tag }} @if ($k['link']) href="{{ $k['link'] }}" @endif
                    class="relative overflow-hidden rounded-xl border border-slate-200 bg-white px-4 py-3.5 before:absolute before:inset-y-0 before:left-0 before:w-1 {{ $aksen[$k['warna']] }} {{ $k['link'] ? 'transition hover:border-slate-300 hover:shadow-sm' : '' }}">
                    <p class="text-xs font-medium text-slate-500">{{ $k['label'] }}</p>
                    <p class="mt-1 text-2xl font-semibold tabular-nums text-slate-900">{{ number_format($k['jumlah'], 0, ',', '.') }}</p>
                    <p class="truncate text-xs tabular-nums text-slate-500">{{ Format::rupiah($k['nilai']) }}</p>
                </{{ $tag }}>
            @endforeach
        </section>

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="space-y-6 lg:col-span-2">
                {{-- Sebaran status --}}
                <x-ui.card title="Sebaran status" :description="number_format($total['jumlah'], 0, ',', '.').' usulan · '.Format::rupiah($total['nilai'])">
                    @if ($statusTampil->isEmpty())
                        <p class="py-4 text-center text-sm text-slate-500">Belum ada usulan pada tahun ini.</p>
                    @else
                        <div class="flex h-3 overflow-hidden rounded-full bg-slate-100" role="img" aria-label="Proporsi usulan per status">
                            @foreach ($statusTampil as $s)
                                <div class="{{ $warnaStatus[$s['status']->color()] }} border-r-2 border-white last:border-r-0" style="width: {{ $s['jumlah'] / $totalJumlah * 100 }}%"></div>
                            @endforeach
                        </div>
                        <dl class="mt-4 grid grid-cols-1 gap-x-6 gap-y-2 sm:grid-cols-2">
                            @foreach ($statusTampil as $s)
                                <div class="flex items-center justify-between gap-3 text-sm">
                                    <dt class="flex items-center gap-2 text-slate-600">
                                        <span class="h-2.5 w-2.5 rounded-sm {{ $warnaStatus[$s['status']->color()] }}"></span>{{ $s['status']->label() }}
                                    </dt>
                                    <dd class="tabular-nums text-slate-900"><span class="font-semibold">{{ $s['jumlah'] }}</span>
                                        <span class="text-xs text-slate-500">· {{ Format::rupiah($s['nilai']) }}</span></dd>
                                </div>
                            @endforeach
                        </dl>
                    @endif
                </x-ui.card>

                @if ($tren !== null)
                    <x-dash.chart-pagu :tren="$tren" :judul-usulan="$isPengusul ? 'Diusulkan unit' : 'Diusulkan'" />
                @endif

                {{-- Rekap per unit --}}
                @if ($perUnit !== null)
                    <x-ui.card title="Rekap per unit kerja" :padding="false">
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-sm">
                                <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                    <tr>
                                        <th class="px-4 py-2.5">Unit kerja</th>
                                        <th class="px-4 py-2.5 text-right">Usulan</th>
                                        <th class="px-4 py-2.5 text-right">Diproses</th>
                                        <th class="px-4 py-2.5 text-right">Disahkan</th>
                                        <th class="px-4 py-2.5 text-right">Nilai usulan</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @forelse ($perUnit as $u)
                                        <tr>
                                            <td class="px-4 py-2.5"><span class="font-mono text-xs text-slate-500">{{ $u->kode }}</span> {{ $u->nama_unit }}</td>
                                            <td class="px-4 py-2.5 text-right tabular-nums">{{ $u->jumlah }}</td>
                                            <td class="px-4 py-2.5 text-right tabular-nums">{{ $u->diproses }}</td>
                                            <td class="px-4 py-2.5 text-right tabular-nums">{{ $u->disahkan }}</td>
                                            <td class="whitespace-nowrap px-4 py-2.5 text-right tabular-nums">{{ Format::rupiah($u->nilai) }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="5" class="px-4 py-8 text-center text-slate-500">Belum ada usulan.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </x-ui.card>
                @endif
            </div>

            <div class="space-y-6">
                {{-- Daftar menunggu tindakan --}}
                <x-ui.card :title="$isPengusul ? 'Perlu Anda lengkapi' : 'Menunggu Anda'" :padding="false">
                    <ul class="divide-y divide-slate-100">
                        @forelse ($menunggu as $u)
                            <li>
                                <a href="{{ $isPengusul ? route('usulan.edit', $u) : route('usulan.show', $u) }}" class="block px-5 py-3 hover:bg-slate-50">
                                    <p class="truncate text-sm font-medium text-slate-900">{{ $u->judul_kegiatan }}</p>
                                    <p class="mt-0.5 flex items-center gap-2 text-xs text-slate-500">
                                        @if ($isPengusul)
                                            <x-ui.badge :color="$u->status->color()">{{ $u->status->label() }}</x-ui.badge>
                                        @else
                                            {{ $u->unit->unit_id }} · {{ $u->submitted_at?->locale('id')->diffForHumans() }}
                                        @endif
                                        <span class="ml-auto tabular-nums">{{ Format::rupiah($u->total_rab) }}</span>
                                    </p>
                                </a>
                            </li>
                        @empty
                            <li class="px-5 py-6 text-center text-sm text-slate-500">Tidak ada yang menunggu.</li>
                        @endforelse
                    </ul>
                    @if (! $isPengusul && $menunggu->isNotEmpty())
                        <x-slot name="footer">
                            <a href="{{ route('verifikasi.index') }}" class="text-sm font-medium text-blue-700 hover:underline">Buka antrean &rarr;</a>
                        </x-slot>
                    @endif
                </x-ui.card>

                {{-- Prioritas --}}
                @if ($perPrioritas !== null)
                    <x-ui.card title="Berdasarkan prioritas">
                        <dl class="space-y-3">
                            @foreach ($perPrioritas as $p => $row)
                                @continue($p === 'Belum' && ! $row['jumlah'])
                                <div class="flex items-center justify-between gap-3 text-sm">
                                    <dt>
                                        @if ($p === 'Belum')
                                            <span class="text-slate-500">Belum diprioritaskan</span>
                                        @else
                                            <x-ui.badge :color="Usulan::PRIORITAS[$p]">{{ $p }}</x-ui.badge>
                                        @endif
                                    </dt>
                                    <dd class="text-right tabular-nums">
                                        <span class="font-semibold text-slate-900">{{ $row['jumlah'] }}</span>
                                        <span class="block text-xs text-slate-500">{{ Format::rupiah($row['nilai']) }}</span>
                                    </dd>
                                </div>
                            @endforeach
                        </dl>
                    </x-ui.card>
                @endif

                {{-- US-27: IKU PTN dan kriteria prioritas --}}
                @if ($isPengusul)
                    <x-ui.card title="IKU PTN {{ $tahun?->tahun }}">
                        <ul class="space-y-2">
                            @forelse ($ikus as $iku)
                                <li class="flex gap-2 text-sm">
                                    <span class="font-mono text-xs font-semibold text-slate-900">{{ $iku->kode_iku }}</span>
                                    <span class="flex-1 text-slate-700">{{ $iku->nama_iku }}@if ($iku->target) <span class="text-xs text-slate-500">· target {{ $iku->target }}</span>@endif</span>
                                    @if ($iku->sifat === 'Wajib')
                                        <x-ui.badge color="amber">Wajib</x-ui.badge>
                                    @endif
                                </li>
                            @empty
                                <li class="text-sm text-slate-500">Belum ada IKU untuk tahun ini.</li>
                            @endforelse
                        </ul>
                    </x-ui.card>

                    <x-ui.card title="Kriteria prioritas">
                        <dl class="space-y-2.5 text-sm">
                            <div><dt><x-ui.badge color="red">Tinggi</x-ui.badge></dt><dd class="mt-1 text-slate-600">Mendukung IKU wajib secara langsung atau kebutuhan mendesak operasional.</dd></div>
                            <div><dt><x-ui.badge color="amber">Sedang</x-ui.badge></dt><dd class="mt-1 text-slate-600">Mendukung IKU pilihan atau peningkatan mutu layanan.</dd></div>
                            <div><dt><x-ui.badge>Rendah</x-ui.badge></dt><dd class="mt-1 text-slate-600">Bermanfaat tetapi dapat ditunda ke tahun berikutnya.</dd></div>
                        </dl>
                        <p class="mt-3 text-xs text-slate-500">Prioritas ditetapkan oleh Perencanaan saat verifikasi.</p>
                    </x-ui.card>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
