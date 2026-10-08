<?php

namespace App\Console\Commands;

use App\Services\SharePointService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Prueba REAL de subida de archivo a SharePoint, aislada de la lógica de
 * publicación de documentos (no usa PublishDocumentoVersion ni toca ningún
 * documento real). Sube un .txt de prueba a una carpeta YA EXISTENTE (nunca
 * crea carpetas) y verifica con un GET que el archivo realmente quedó ahí.
 * No borra el archivo al terminar: queda para confirmarlo a simple vista.
 */
class SharePointPruebaSubida extends Command
{
    protected $signature = 'sharepoint:prueba-subida {--carpeta= : Ruta destino, por defecto "Sistema de Gestión de Inocuidad/SGI"}';

    protected $description = 'Sube un archivo de prueba real a SharePoint (aislado de PublishDocumentoVersion) y verifica que exista';

    public function handle(SharePointService $sp): int
    {
        $carpetaDestino = $this->option('carpeta') ?: 'Sistema de Gestión de Inocuidad/SGI';

        $siteId = $sp->getSiteId();
        $driveId = $sp->getDriveId($siteId);

        $this->line("Buscando carpeta destino (solo lectura, no se crea si falta): {$carpetaDestino}");

        $carpeta = $sp->buscarCarpeta($driveId, $carpetaDestino);

        if (!$carpeta) {
            $this->error("No se encontró la carpeta '{$carpetaDestino}'. No se creó nada ni se subió nada.");
            Log::error('[sharepoint:prueba-subida] Carpeta destino no encontrada.', ['carpeta' => $carpetaDestino]);

            return self::FAILURE;
        }

        $this->info("Carpeta encontrada (id={$carpeta['id']}).");

        $nombreArchivo = 'sharepoint-test.txt';
        $contenido = 'Prueba de integración SharePoint SGI - ' . now()->toDateTimeString();

        $localPath = tempnam(sys_get_temp_dir(), 'sp-test-');
        file_put_contents($localPath, $contenido);

        try {
            $this->line('Subiendo archivo de prueba (subida simple, archivo < 4 MB)...');

            $item = $sp->uploadFileToFolder($driveId, $carpeta['id'], $nombreArchivo, $localPath);

            $resultado = [
                'http_status' => 200,
                'nombre' => $item['name'] ?? null,
                'item_id' => $item['id'] ?? null,
                'web_url' => $item['webUrl'] ?? null,
                'tamano_bytes' => $item['size'] ?? null,
                'ruta_relativa' => $carpetaDestino . '/' . ($item['name'] ?? $nombreArchivo),
            ];

            $this->info('Subida completada:');
            foreach ($resultado as $clave => $valor) {
                $this->line("  {$clave}: {$valor}");
            }

            Log::info('[sharepoint:prueba-subida] Archivo subido.', $resultado);

            // Verificación: GET del item para confirmar que de verdad existe.
            $this->line('Verificando con GET que el archivo existe...');
            $verificado = $sp->obtenerItem($driveId, $item['id']);

            $existeOk = ($verificado['id'] ?? null) === $item['id'];

            $this->info($existeOk
                ? '✔ Verificado: el archivo existe en SharePoint (GET exitoso).'
                : '✘ La verificación no coincide con el item subido.');

            Log::info('[sharepoint:prueba-subida] Verificación GET.', [
                'item_id_subido' => $item['id'] ?? null,
                'item_id_verificado' => $verificado['id'] ?? null,
                'coincide' => $existeOk,
            ]);

            $this->comment('El archivo NO se borró: revísalo en SharePoint en ' . $resultado['ruta_relativa']);

            return $existeOk ? self::SUCCESS : self::FAILURE;
        } finally {
            @unlink($localPath);
        }
    }
}
