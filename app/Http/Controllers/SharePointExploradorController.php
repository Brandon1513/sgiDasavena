<?php

namespace App\Http\Controllers;

use App\Services\SharePointService;
use Illuminate\Http\Request;

/**
 * Explorador de SharePoint (solo lectura, solo carpetas): navega la
 * estructura real de carpetas por Área/departamento, sin crear ni mover
 * nada. Independiente del selector de ubicación de finalize_form
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

        $carpetas = collect($sp->listarHijos($driveId, $request->string('folder_id')->toString()))
            ->filter(fn ($item) => isset($item['folder']))
            ->map(fn ($item) => [
                'id' => $item['id'],
                'name' => $item['name'],
                'has_children' => (int) ($item['folder']['childCount'] ?? 0) > 0,
            ])
            ->sortBy(fn ($item) => mb_strtolower($item['name']))
            ->values();

        return response()->json(['carpetas' => $carpetas]);
    }
}
