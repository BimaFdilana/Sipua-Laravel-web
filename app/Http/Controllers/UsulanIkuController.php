<?php

namespace App\Http\Controllers;

use App\Models\Usulan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

/** US-11: keterkaitan usulan dengan IKU tahun anggaran yang sama. */
class UsulanIkuController extends Controller
{
    public function update(Request $request, Usulan $usulan): RedirectResponse
    {
        Gate::authorize('update', $usulan);

        $data = $request->validate([
            'ikus' => ['array'],
            'ikus.*' => ['integer', Rule::exists('ikus', 'id')->where('tahun', $usulan->tahun->tahun)],
        ], ['ikus.*.exists' => 'IKU yang dipilih bukan IKU tahun anggaran usulan ini.']);

        $usulan->ikus()->sync($data['ikus'] ?? []);

        return UsulanController::next($request, $usulan, 'iku');
    }
}
