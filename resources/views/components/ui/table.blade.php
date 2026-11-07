@props(['paginator' => null, 'empty' => 'Belum ada data.'])

{{--
    Tabel standar. Isi slot "head" dengan <th>, slot utama dengan <tr>.
    Jika slot utama kosong, tampilkan pesan $empty.
--}}
<div class="relative overflow-hidden rounded-xl border border-slate-200 bg-white">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                <tr>{{ $head }}</tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-slate-700">
                @if (trim($slot) === '')
                    <tr>
                        <td colspan="99" class="px-4 py-12 text-center text-sm text-slate-500">{{ $empty }}</td>
                    </tr>
                @else
                    {{ $slot }}
                @endif
            </tbody>
        </table>
    </div>
    @if ($paginator && $paginator->hasPages())
        <div class="border-t border-slate-200 px-4 py-3">
            {{ $paginator->links() }}
        </div>
    @endif
</div>
