<?php

namespace App\Http\Controllers;

use App\Services\SharePointService;
use Illuminate\Http\Request;

/**
 * Selector/navegador de carpetas reales de SharePoint para el
 * administrador_sgi (solo lectura: nunca crea ni mueve nada). Se usa cuando
 * decide cambiar la ubicación sugerida automáticamente al finalizar una
 * solicitud.
 */
class SharePointCarpetasController extends Controller
{
    public function hijos(Request $request, SharePointService $sp)
    {
        $siteId = $sp->getSiteId();
        $driveId = $sp->getDriveId($siteId);

        $folderId = $request->string('folder_id')->toString();

        if (!$folderId) {
            // Sin folder_id: se empieza siempre desde la carpeta raíz
            // configurada (SP_ROOT_FOLDER), no desde la raíz literal del
            // drive, para no exponer/navegar contenido fuera de esa rama.
            $raiz = $sp->buscarCarpeta($driveId, config('sharepoint.root_folder'));
            $folderId = $raiz['id'] ?? 'root';
        }

        $hijos = collect($sp->listarHijos($driveId, $folderId))
            ->filter(fn ($item) => isset($item['folder']))
            ->map(fn ($item) => [
                'id' => $item['id'],
                'name' => $item['name'],
                'has_children' => (int) ($item['folder']['childCount'] ?? 0) > 0,
            ])
            ->values();

        return response()->json([
            'current_id' => $folderId,
            'folders' => $hijos,
        ]);
    }
}
