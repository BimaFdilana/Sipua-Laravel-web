<?php

namespace App\Http\Controllers;

use App\Models\Usulan;
use App\Models\UsulanDokumen;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

/** US-10: data dukung RAB. File disimpan privat dan hanya bisa diunduh lewat route yang dicek policy. */
class UsulanDokumenController extends Controller
{
    public const MAKS_DOKUMEN = 20;

    public function store(Request $request, Usulan $usulan): RedirectResponse
    {
        Gate::authorize('update', $usulan);

        $request->validate([
            'kategori' => ['required', Rule::in(array_keys(UsulanDokumen::KATEGORI))],
            'file' => ['required', 'file', 'mimes:pdf', 'mimetypes:application/pdf', 'max:5120'],
        ], [
            'file.required' => 'Pilih file PDF yang akan diunggah.',
            'file.mimes' => 'File harus berformat PDF.',
            'file.mimetypes' => 'File harus berformat PDF.',
            'file.max' => 'Ukuran file maksimal 5 MB.',
        ], ['kategori' => 'kategori dokumen']);

        if ($usulan->dokumens()->count() >= self::MAKS_DOKUMEN) {
            return back()->withErrors(['file' => 'Maksimal '.self::MAKS_DOKUMEN.' dokumen per usulan.']);
        }

        $file = $request->file('file');
        $path = $file->storeAs("usulan/{$usulan->id}", Str::uuid().'.pdf', 'local');

        $usulan->dokumens()->create([
            'kategori' => $request->kategori,
            'nama_asli' => Str::limit($file->getClientOriginalName(), 200, ''),
            'path' => $path,
            'ukuran' => $file->getSize(),
            'uploaded_by' => $request->user()->id,
        ]);

        return redirect()->route('usulan.edit', [$usulan, 'dokumen'])->with('success', 'Dokumen berhasil diunggah.');
    }

    public function download(Usulan $usulan, UsulanDokumen $dokumen): StreamedResponse
    {
        Gate::authorize('view', $usulan);

        abort_unless(Storage::disk('local')->exists($dokumen->path), 404);

        return Storage::disk('local')->response($dokumen->path, $dokumen->nama_asli, [
            'Content-Type' => 'application/pdf',
        ]);
    }

    public function destroy(Usulan $usulan, UsulanDokumen $dokumen): RedirectResponse
    {
        Gate::authorize('update', $usulan);

        Storage::disk('local')->delete($dokumen->path);
        $dokumen->delete();

        return redirect()->route('usulan.edit', [$usulan, 'dokumen'])->with('success', "Dokumen {$dokumen->nama_asli} dihapus.");
    }
}
