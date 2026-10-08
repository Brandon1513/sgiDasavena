<?php

namespace App\Http\Controllers;

use App\Services\SharePointService;
use Illuminate\Http\Request;

/**
 * Explorador de SharePoint (solo lectura): navega la estructura real de
 * carpetas y documentos por Área/departamento, sin crear ni mover nada.
 * Independiente del selector de ubicación de finalize_form
 * (SharePointCarpetasController), que solo necesita carpetas.
 */
class SharePointExploradorController extends Controller
{
    public function index(SharePointService $sp)
    {
        $siteId = $sp->getSiteId();
        $driveId = $sp->getDriveId($siteId);

        $raiz = $sp->buscarCarpeta($driveId, config('sharepoint.root_folder'));

        return view('sharepoint.explorador', [
            'raizId' => $raiz['id'] ?? null,
        ]);
    }

    public function contenido(Request $request, SharePointService $sp)
    {
        $request->validate([
            'folder_id' => 'required|string',
        ]);

        $siteId = $sp->getSiteId();
        $driveId = $sp->getDriveId($siteId);

        $items = collect($sp->listarHijos($driveId, $request->string('folder_id')->toString()))
            ->map(fn ($item) => [
                'id' => $item['id'],
                'name' => $item['name'],
                'type' => isset($item['folder']) ? 'folder' : 'file',
                'has_children' => isset($item['folder']) && (int) ($item['folder']['childCount'] ?? 0) > 0,
                'web_url' => $item['webUrl'] ?? null,
                'size' => $item['size'] ?? null,
            ])
            // Carpetas primero, luego archivos; alfabético dentro de cada grupo.
            ->sortBy(fn ($item) => ($item['type'] === 'folder' ? '0_' : '1_').mb_strtolower($item['name']))
            ->values();

        return response()->json(['items' => $items]);
    }
}
