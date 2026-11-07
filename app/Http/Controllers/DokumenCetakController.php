<?php

namespace App\Http\Controllers;

use App\Enums\StatusUsulan;
use App\Models\Tahun;
use App\Models\Usulan;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * US-28: cetak RKAKL (per usulan yang disahkan) dan RKT (rencana kerja tahunan unit).
 * Format resmi belum diterima; ini format tabel standar yang mudah disesuaikan di view dokumen/*.
 */
class DokumenCetakController extends Controller
{
    public function rkakl(Usulan $usulan): Response
    {
        Gate::authorize('view', $usulan);
        abort_unless($usulan->status === StatusUsulan::Disahkan, 404, 'RKAKL hanya tersedia untuk usulan yang sudah disahkan.');

        $usulan->load(['unit', 'tahun', 'rabItems.akun', 'ikus', 'logs.user']);
        $pengesahan = $usulan->logs->where('aksi', 'sahkan')->last();

        return Pdf::loadView('dokumen.rkakl', [
            'usulan' => $usulan,
            'perAkun' => $usulan->rabItems->groupBy('kode_akun'),
            'pengesahan' => $pengesahan,
        ])->setPaper('a4')->download(Str::slug('RKAKL '.$usulan->nomor).'.pdf');
    }

    public function rkt(Request $request): Response
    {
        $user = $request->user();
        $tahun = $request->filled('tahun') ? Tahun::findOrFail($request->integer('tahun')) : Tahun::aktif();
        abort_unless($tahun && $user->unit, 404);

        $usulans = Usulan::with(['ikus'])
            ->where('unit_id', $user->unit_id)
            ->where('tahun_id', $tahun->id)
            ->where('status', StatusUsulan::Disahkan)
            ->orderBy('waktu_mulai')
            ->get();

        return Pdf::loadView('dokumen.rkt', [
            'unit' => $user->unit,
            'tahun' => $tahun,
            'usulans' => $usulans,
            'dicetak' => now(),
            'user' => $user,
        ])->setPaper('a4', 'landscape')->download(Str::slug("RKT {$user->unit->unit_id} {$tahun->tahun}").'.pdf');
    }
}
