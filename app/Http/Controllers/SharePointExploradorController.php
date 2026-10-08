<?php

namespace App\Http\Controllers;

use App\Services\SharePointService;
use Illuminate\Http\Request;

/**
 * Explorador de SharePoint (solo lectura): navega la estructura real de
 * documentos por Área/departamento, con sus carpetas y archivos, sin crear
 * ni mover nada. Independiente del selector de ubicación de finalize_form
 * (SharePointCarpetasController), que solo necesita carpetas.
 */
class SharePointExploradorController extends Controller
{
    public function index(SharePointService $sp)
    {
        $siteId = $sp->getSiteId();
        $driveId = $sp->getDriveId($siteId);

        $raizVigente = $sp->buscarCarpeta($driveId, config('sharepoint.root_folder'));
        $raizObsoleto = $sp->buscarCarpeta($driveId, config('sharepoint.obsoleto_root_folder'));

        $departamentosVigente = $raizVigente
            ? $this->mapearItems($sp->listarHijos($driveId, $raizVigente['id']), soloCarpetas: true)
            : collect();

        $departamentosObsoleto = $raizObsoleto
            ? $this->mapearItems($sp->listarHijos($driveId, $raizObsoleto['id']), soloCarpetas: true)
            : collect();

        return view('sharepoint.explorador', [
            'raizVigenteId' => $raizVigente['id'] ?? null,
            'raizObsoletoId' => $raizObsoleto['id'] ?? null,
            'departamentosVigente' => $departamentosVigente,
            'departamentosObsoleto' => $departamentosObsoleto,
        ]);
    }

    public function contenido(Request $request, SharePointService $sp)
    {
        $request->validate([
            'folder_id' => 'required|string',
        ]);

        $siteId = $sp->getSiteId();
        $driveId = $sp->getDriveId($siteId);

        $items = $this->mapearItems($sp->listarHijos($driveId, $request->string('folder_id')->toString()));

        return response()->json(['items' => $items]);
    }

    private function mapearItems(array $hijos, bool $soloCarpetas = false): \Illuminate\Support\Collection
    {
        return collect($hijos)
            ->when($soloCarpetas, fn ($c) => $c->filter(fn ($item) => isset($item['folder'])))
            ->map(fn ($item) => [
                'id' => $item['id'],
                'name' => $item['name'],
                'type' => isset($item['folder']) ? 'folder' : 'file',
                'has_children' => isset($item['folder']) && (int) ($item['folder']['childCount'] ?? 0) > 0,
                'web_url' => $item['webUrl'] ?? null,
                'size' => $item['size'] ?? null,
            ])
            ->values();
    }
}
