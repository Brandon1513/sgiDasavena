<?php

namespace App\Http\Controllers;

use App\Services\SharePointService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Selector/navegador de carpetas reales de SharePoint para el
 * administrador_sgi (solo lectura: nunca crea ni mueve nada). Se usa al
 * finalizar una solicitud para cambiar la ubicación sugerida y para marcar
 * archivos previos al sistema que deben pasar a Obsoletos (el movimiento lo
 * hace la publicación, no este controlador). Devuelve carpetas (navegables
 * y seleccionables) y documentos.
 */
class SharePointCarpetasController extends Controller
{
    public function hijos(Request $request, SharePointService $sp)
    {
        $request->validate([
            'folder_id' => 'nullable|string|max:120',
            'ruta' => 'nullable|string|max:500',
        ]);

        $siteId = $sp->getSiteId();
        $driveId = $sp->getDriveId($siteId);

        $folderId = $request->string('folder_id')->toString();
        $breadcrumb = null;

        if (!$folderId) {
            // Sin folder_id: se empieza siempre desde la carpeta raíz
            // configurada (SP_ROOT_FOLDER), no desde la raíz literal del
            // drive, para no exponer/navegar contenido fuera de esa rama.
            $raiz = $sp->buscarCarpeta($driveId, config('sharepoint.root_folder'));
            $folderId = $raiz['id'] ?? 'root';

            // Con ?ruta= se abre directo en esa carpeta (p. ej. la ubicación
            // sugerida), hasta donde exista, y se regresan las migas con sus
            // IDs para que el selector pueda subir de nivel.
            if ($request->filled('ruta')) {
                $breadcrumb = [];

                foreach ($this->segmentosBajoRaiz($request->string('ruta')->toString()) as $segmento) {
                    $siguiente = collect($sp->listarHijos($driveId, $folderId))
                        ->first(fn ($i) => isset($i['folder']) && Str::lower($i['name']) === Str::lower($segmento));

                    if (!$siguiente) {
                        break;
                    }

                    $folderId = $siguiente['id'];
                    $breadcrumb[] = ['id' => $siguiente['id'], 'name' => $siguiente['name']];
                }
            }
        }

        $hijos = collect($sp->listarHijos($driveId, $folderId));
        $orden = fn ($item) => mb_strtolower($item['name']);

        $carpetas = $hijos
            ->filter(fn ($item) => isset($item['folder']))
            ->sortBy($orden)
            ->map(fn ($item) => [
                'id' => $item['id'],
                'name' => $item['name'],
                'has_children' => (int) ($item['folder']['childCount'] ?? 0) > 0,
            ])
            ->values();

        $archivos = $hijos
            ->filter(fn ($item) => isset($item['file']))
            ->sortBy($orden)
            ->map(fn ($item) => [
                'id' => $item['id'],
                'name' => $item['name'],
                'web_url' => $item['webUrl'] ?? null,
                'size' => $item['size'] ?? null,
                'modified_at' => $item['lastModifiedDateTime'] ?? null,
            ])
            ->values();

        return response()->json([
            'current_id' => $folderId,
            'breadcrumb' => $breadcrumb,
            'folders' => $carpetas,
            'files' => $archivos,
        ]);
    }

    /**
     * Segmentos de una ruta completa ("{raíz}/{Área}/{Tipo}") relativos a la
     * carpeta raíz configurada. Si la ruta no está bajo la raíz, no se
     * navega (se queda en la raíz).
     */
    private function segmentosBajoRaiz(string $ruta): array
    {
        $ruta = trim($ruta, '/');
        $raiz = trim((string) config('sharepoint.root_folder'), '/');

        if ($raiz !== '') {
            if (Str::lower($ruta) === Str::lower($raiz)) {
                return [];
            }

            if (!Str::startsWith(Str::lower($ruta), Str::lower($raiz) . '/')) {
                return [];
            }

            $ruta = substr($ruta, strlen($raiz) + 1);
        }

        return array_values(array_filter(explode('/', $ruta), fn ($s) => $s !== ''));
    }
}
