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

public function marcarObsoleto(DocumentoVersion $documentoVersion)
{
    // 1. Verificación de seguridad (Opcional si usas el middleware de rol en la ruta)
    if (!auth()->user()->hasAnyRole(['administrador_sgi', 'administrador'])) {
        abort(403, 'No tienes permisos para realizar esta acción.');
    }

    try {
        DB::transaction(function () use ($documentoVersion) {
            // 2. Cambiamos el estatus de la versión
            $documentoVersion->update([
                'estatus' => 'obsoleto',
                'observaciones' => $documentoVersion->observaciones . "\n-- Marcado como obsoleto por " . auth()->user()->name . " el " . now()->format('d/m/Y H:i')
            ]);

            // 3. IMPORTANTÍSIMO: También marcamos como obsoletas todas sus revisiones 
            // para que no sigan apareciendo alertas en el calendario
            $documentoVersion->revisiones()->update(['estatus' => 'obsoleto']);
        });

        return back()->with('success', "La versión {$documentoVersion->version} ha sido marcada como obsoleta.");
        
    } catch (\Exception $e) {
        \Log::error("Error al marcar obsoleto: " . $e->getMessage());
        return back()->with('error', 'Ocurrió un error al procesar la solicitud.');
    }
}
}
