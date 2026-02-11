<?php

namespace App\Http\Controllers;

use App\Models\Documento;
use App\Models\DocumentoVersion;

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
}
