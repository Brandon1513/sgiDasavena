<?php

namespace App\Http\Controllers;

use App\Models\Documento;
use App\Models\DocumentoVersion;
use Illuminate\Support\Facades\DB;

class DocumentoVersionController extends Controller
{
    public function show(Documento $documento, DocumentoVersion $version)
    {
        // Evita que abran una versión que no pertenece a este documento
        if ($version->documento_id !== $documento->id) {
            abort(404);
        }

        $version->load([
            'documento',
            // si tienes revisiones
            // 'revisiones' => fn($q) => $q->orderByDesc('id'),
        ]);

        return view('documentos.versiones.show', compact('documento', 'version'));
    }

    public function marcarObsoleto(DocumentoVersion $version)
{
    $user = auth()->user();

    
    abort_unless($user->hasRole('administrador_sgi') || $user->hasRole('administrador'), 403);

    DB::transaction(function () use ($version) {
        $version->update(['estatus' => 'obsoleto']);

        if (!DocumentoVersion::where('documento_id', $version->documento_id)->where('estatus','vigente')->exists()) {
            $ultima = DocumentoVersion::where('documento_id', $version->documento_id)
                ->orderByDesc('id')
                ->first();

            if ($ultima) $ultima->update(['estatus' => 'vigente']);
        }
    });

    return back()->with('success', 'Versión marcada como obsoleta.');
}
}
