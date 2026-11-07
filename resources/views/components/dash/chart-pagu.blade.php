@props(['tren', 'judulUsulan' => 'Diusulkan'])

{{--
    US-27 Grafik pagu anggaran tahunan.
    Batang: nilai diusulkan dan disahkan; garis: pagu institusi (jika diisi Admin).
--}}
<x-ui.card title="Pagu dan usulan per tahun" description="Garis = pagu institusi. Batang = nilai usulan (tidak termasuk yang ditolak) dan yang disahkan.">
    @if ($tren->isEmpty())
        <p class="py-8 text-center text-sm text-slate-500">Belum ada data pagu atau usulan yang diajukan.</p>
    @else
        <div class="relative h-64"
            x-data="{
                data: @js($tren),
                async init() {
                    const Chart = await window.loadChart();
                    const rp = (v) => 'Rp ' + Number(v).toLocaleString('id-ID');
                    const singkat = (v) => v >= 1e9 ? (v / 1e9).toLocaleString('id-ID', { maximumFractionDigits: 1 }) + ' M'
                        : v >= 1e6 ? (v / 1e6).toLocaleString('id-ID', { maximumFractionDigits: 0 }) + ' jt' : v.toLocaleString('id-ID');
                    new Chart(this.$refs.canvas, {
                        data: {
                            labels: this.data.map(r => r.tahun),
                            datasets: [
                                { type: 'line', label: 'Pagu', data: this.data.map(r => r.pagu), borderColor: '#0f172a', backgroundColor: '#0f172a', borderWidth: 2, pointRadius: 3, spanGaps: true, order: 0 },
                                { type: 'bar', label: @js($judulUsulan), data: this.data.map(r => r.diusulkan), backgroundColor: '#93c5fd', borderRadius: 4, order: 1 },
                                { type: 'bar', label: 'Disahkan', data: this.data.map(r => r.disahkan), backgroundColor: '#2563eb', borderRadius: 4, order: 1 },
                            ],
                        },
                        options: {
                            maintainAspectRatio: false,
                            interaction: { mode: 'index', intersect: false },
                            plugins: {
                                legend: { position: 'bottom', labels: { boxWidth: 12, usePointStyle: true } },
                                tooltip: { callbacks: { label: (c) => `${c.dataset.label}: ${c.raw === null ? '—' : rp(c.raw)}` } },
                            },
                            scales: {
                                y: { beginAtZero: true, ticks: { callback: singkat }, grid: { color: '#f1f5f9' } },
                                x: { grid: { display: false } },
                            },
                        },
                    });
                },
            }">
            <canvas x-ref="canvas" role="img" aria-label="Grafik pagu dan nilai usulan per tahun"></canvas>
        </div>
        {{-- Alternatif teks untuk pembaca layar & cetak --}}
        <table class="sr-only">
            <caption>Pagu dan usulan per tahun</caption>
            <tr><th>Tahun</th><th>Pagu</th><th>{{ $judulUsulan }}</th><th>Disahkan</th></tr>
            @foreach ($tren as $r)
                <tr>
                    <td>{{ $r['tahun'] }}</td>
                    <td>{{ $r['pagu'] !== null ? \App\Support\Format::rupiah($r['pagu']) : '—' }}</td>
                    <td>{{ \App\Support\Format::rupiah($r['diusulkan']) }}</td>
                    <td>{{ \App\Support\Format::rupiah($r['disahkan']) }}</td>
                </tr>
            @endforeach
        </table>
    @endif
</x-ui.card>
