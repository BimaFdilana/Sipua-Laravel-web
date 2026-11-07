<?php

namespace App\Http\Controllers;

use App\Models\Usulan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

/** US-09: Rencana Anggaran Biaya. Seluruh item disimpan sekaligus dari tabel di layar. */
class UsulanRabController extends Controller
{
    public function update(Request $request, Usulan $usulan): RedirectResponse
    {
        Gate::authorize('update', $usulan);

        $data = $request->validate([
            'items' => ['array', 'max:200'],
            'items.*.kode_akun' => ['required', Rule::exists('akun_belanjas', 'kode_akun')->where('is_active', true)],
            'items.*.uraian' => ['required', 'string', 'max:255'],
            'items.*.volume' => ['required', 'numeric', 'gt:0', 'max:9999999'],
            'items.*.satuan' => ['required', 'string', 'max:50'],
            'items.*.harga_satuan' => ['required', 'numeric', 'min:0', 'max:999999999999'],
        ], [], [
            'items.*.kode_akun' => 'akun belanja',
            'items.*.uraian' => 'uraian',
            'items.*.volume' => 'volume',
            'items.*.satuan' => 'satuan',
            'items.*.harga_satuan' => 'harga satuan',
        ]);

        DB::transaction(function () use ($usulan, $data) {
            $usulan->rabItems()->delete();

            foreach (array_values($data['items'] ?? []) as $i => $item) {
                $usulan->rabItems()->create([
                    'kode_akun' => $item['kode_akun'],
                    'uraian' => trim($item['uraian']),
                    'volume' => $item['volume'],
                    'satuan' => trim($item['satuan']),
                    'harga_satuan' => $item['harga_satuan'],
                    // Dihitung ulang di server, angka dari browser tidak dipercaya.
                    'jumlah' => round((float) $item['volume'] * (float) $item['harga_satuan'], 2),
                    'urutan' => $i,
                ]);
            }

            $usulan->hitungTotalRab();
        });

        return UsulanController::next($request, $usulan, 'rab');
    }
}
