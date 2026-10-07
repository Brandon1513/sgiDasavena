<?php

namespace App\Console\Commands;

use App\Services\Graph\GraphAccessTokenProvider;
use App\Services\SharePointService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Prueba mínima de conectividad Graph → SharePoint: NO crea carpetas, NO
 * sube archivos, NO cambia nada. Solo valida que el token, el sitio, la
 * biblioteca y la carpeta raíz configurados sean alcanzables con el permiso
 * ya otorgado.
 */
class SharePointDiagnostico extends Command
{
    protected $signature = 'sharepoint:diagnostico';

    protected $description = 'Valida conectividad y permisos de Graph hacia SharePoint sin modificar nada';

    public function handle(GraphAccessTokenProvider $tokenProvider, SharePointService $sp): int
    {
        // 1) Token
        try {
            $token = $tokenProvider->getToken();
            $this->ok('Token de Microsoft Graph obtenido correctamente.');
            Log::info('[sharepoint:diagnostico] Token de Graph obtenido correctamente.');
        } catch (Throwable $e) {
            $this->reportarError('No se pudo obtener el token de Graph.', $e);

            return self::FAILURE;
        }

        // 2) Sitio (llamada real a Graph, no solo leer el config)
        $siteId = config('sharepoint.site_id');

        try {
            $res = Http::withToken($token)
                ->baseUrl('https://graph.microsoft.com/v1.0')
                ->timeout(30)
                ->get("/sites/{$siteId}");

            if ($res->successful()) {
                $this->ok("Sitio alcanzado: {$res->json('displayName')} ({$res->json('webUrl')}).");
                Log::info('[sharepoint:diagnostico] GET /sites/{site} OK.', ['status' => $res->status(), 'endpoint' => "/sites/{$siteId}"]);
            } else {
                $this->fallaHttp('GET /sites/{site}', "/sites/{$siteId}", $res->status(), $res->body());
            }
        } catch (Throwable $e) {
            $this->reportarError('Error consultando el sitio.', $e);
        }

        // 3) Biblioteca (drive)
        $driveId = config('sharepoint.drive_id');

        try {
            $res = Http::withToken($token)
                ->baseUrl('https://graph.microsoft.com/v1.0')
                ->timeout(30)
                ->get("/drives/{$driveId}");

            if ($res->successful()) {
                $this->ok("Biblioteca alcanzada: {$res->json('name')}.");
                Log::info('[sharepoint:diagnostico] GET /drives/{drive} OK.', ['status' => $res->status(), 'endpoint' => "/drives/{$driveId}"]);
            } else {
                $this->fallaHttp('GET /drives/{drive}', "/drives/{$driveId}", $res->status(), $res->body());
            }
        } catch (Throwable $e) {
            $this->reportarError('Error consultando la biblioteca.', $e);
        }

        // 4) Carpeta raíz (solo búsqueda, nunca crea nada)
        $rootFolder = config('sharepoint.root_folder');

        if (!$rootFolder) {
            $this->line('SP_ROOT_FOLDER está vacío: la raíz es la biblioteca misma, no hay nada que resolver.');
        } else {
            try {
                $folder = $sp->buscarCarpeta($driveId, $rootFolder);

                if ($folder) {
                    $this->ok("Carpeta raíz encontrada: '{$rootFolder}' (id={$folder['id']}).");
                    Log::info('[sharepoint:diagnostico] Carpeta raíz encontrada.', ['path' => $rootFolder, 'id' => $folder['id']]);
                } else {
                    $this->error("La carpeta raíz '{$rootFolder}' no existe en la biblioteca (o algún tramo intermedio no existe).");
                    Log::warning('[sharepoint:diagnostico] Carpeta raíz no encontrada.', ['path' => $rootFolder]);
                }
            } catch (Throwable $e) {
                $this->reportarError('Error buscando la carpeta raíz.', $e);
            }
        }

        $this->info('Diagnóstico terminado. Revisa storage/logs para el detalle completo.');

        return self::SUCCESS;
    }

    private function ok(string $mensaje): void
    {
        $this->line("<fg=green>✔</> {$mensaje}");
    }

    private function reportarError(string $mensaje, Throwable $e): void
    {
        $this->error($mensaje);
        Log::error("[sharepoint:diagnostico] {$mensaje}", ['error' => $e->getMessage()]);
    }

    private function fallaHttp(string $paso, string $endpoint, int $status, string $body): void
    {
        $this->error("{$paso} falló: HTTP {$status} en {$endpoint}.");
        Log::error("[sharepoint:diagnostico] {$paso} falló.", [
            'endpoint' => $endpoint,
            'status' => $status,
            'body' => $body,
        ]);
    }
}
