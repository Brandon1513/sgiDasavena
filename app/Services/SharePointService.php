<?php

namespace App\Services;

use App\Services\Graph\GraphAccessTokenProvider;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SharePointService
{
    /**
     * Múltiplo de 320 KiB que Graph exige para los fragmentos de un upload
     * session (usamos ~8 fragmentos de 320 KiB = 2.5 MiB, dentro del rango
     * de 5-10 MiB que pide el fragmento final salvo el último).
     */
    private const UPLOAD_CHUNK_SIZE = 320 * 1024 * 8; // 2.5 MiB

    private const SIMPLE_UPLOAD_LIMIT = 4 * 1024 * 1024; // 4 MB

    private const MAX_ATTEMPTS = 3;

    private const RETRY_SLEEP_MS = 500;

    public function __construct(private GraphAccessTokenProvider $tokenProvider)
    {
    }

    private function graph(): PendingRequest
    {
        return Http::withToken($this->tokenProvider->getToken())
            ->baseUrl('https://graph.microsoft.com/v1.0')
            ->acceptJson()
            ->timeout(30);
    }

    /**
     * Ejecuta una petición Graph con reintentos (hasta 3 intentos) solo en
     * 429 y 5xx, respetando el header Retry-After si viene presente.
     */
    private function send(callable $request): Response
    {
        $response = null;

        for ($attempt = 1; $attempt <= self::MAX_ATTEMPTS; $attempt++) {
            $response = $request();

            if ($response->successful()) {
                return $response;
            }

            $status = $response->status();
            $esReintentable = $status === 429 || $status >= 500;

            if (!$esReintentable || $attempt === self::MAX_ATTEMPTS) {
                return $response;
            }

            $retryAfter = (int) ($response->header('Retry-After') ?: 0);
            $sleepMs = $retryAfter > 0 ? $retryAfter * 1000 : self::RETRY_SLEEP_MS * $attempt;

            usleep($sleepMs * 1000);
        }

        return $response;
    }

    /**
     * Registra el detalle del error en el log (puede incluir el cuerpo de la
     * respuesta de Azure) y lanza una excepción genérica, sin ese detalle,
     * para no exponerlo a quien la capture más arriba.
     */
    private function fallar(string $mensajeLog, Response $response, string $mensajePublico): never
    {
        Log::error($mensajeLog, [
            'status' => $response->status(),
            'body' => $response->body(),
        ]);

        throw new \RuntimeException($mensajePublico);
    }

    public function getSiteId(): string
    {
        if (config('sharepoint.site_id')) {
            return config('sharepoint.site_id');
        }

        $host = config('sharepoint.site_host');
        $path = config('sharepoint.site_path');

        return Cache::remember(
            "sharepoint:site_id:{$host}:{$path}",
            config('sharepoint.cache_ttl', 3600),
            function () use ($host, $path) {
                $res = $this->send(fn () => $this->graph()->get("/sites/{$host}:{$path}"));

                if (!$res->successful()) {
                    $this->fallar('No se pudo resolver el sitio de SharePoint.', $res, 'No se pudo conectar con el sitio de SharePoint.');
                }

                return $res->json('id');
            }
        );
    }

    public function getDriveId(string $siteId): string
    {
        if (config('sharepoint.drive_id')) {
            return config('sharepoint.drive_id');
        }

        $driveName = config('sharepoint.drive_name');

        return Cache::remember(
            "sharepoint:drive_id:{$siteId}:{$driveName}",
            config('sharepoint.cache_ttl', 3600),
            function () use ($siteId, $driveName) {
                $res = $this->send(fn () => $this->graph()->get("/sites/{$siteId}/drives"));

                if (!$res->successful()) {
                    $this->fallar('No se pudo listar las bibliotecas del sitio de SharePoint.', $res, 'No se pudo conectar con SharePoint.');
                }

                $drive = collect($res->json('value'))->firstWhere('name', $driveName);

                if (!$drive) {
                    throw new \RuntimeException("No se encontró la biblioteca '{$driveName}' en el sitio de SharePoint.");
                }

                return $drive['id'];
            }
        );
    }

    /**
     * Asegura ruta de carpetas (crea si no existe) y regresa el itemId de la
     * carpeta final. $path ejemplo: "SGI/Comercial/Vigentes/F-DIR-05".
     * Cada tramo resuelto se cachea por separado.
     */
    public function ensureFolderPath(string $driveId, string $path): string
    {
        $parts = array_values(array_filter(explode('/', trim($path, '/'))));
        $parentId = 'root';
        $acumulado = '';

        foreach ($parts as $name) {
            $acumulado .= '/' . $name;

            $parentId = Cache::remember(
                "sharepoint:folder:{$driveId}:{$acumulado}",
                config('sharepoint.cache_ttl', 3600),
                function () use ($driveId, $parentId, $name) {
                    $child = $this->findChildFolder($driveId, $parentId, $name);

                    return $child['id'] ?? $this->createFolder($driveId, $parentId, $name)['id'];
                }
            );
        }

        return $parentId;
    }

    /**
     * Busca una ruta de carpetas ya existente, sin crear nada. Regresa el
     * driveItem de la carpeta final, o null si algún tramo no existe.
     * $path ejemplo: "Sistema de Gestión de Inocuidad/SGI".
     */
    public function buscarCarpeta(string $driveId, string $path): ?array
    {
        $parts = array_values(array_filter(explode('/', trim($path, '/'))));
        $parentId = 'root';
        $folder = null;

        foreach ($parts as $name) {
            $folder = $this->findChildFolder($driveId, $parentId, $name);

            if (!$folder) {
                return null;
            }

            $parentId = $folder['id'];
        }

        return $folder;
    }

    private function findChildFolder(string $driveId, string $parentId, string $name): ?array
    {
        return collect($this->listarHijos($driveId, $parentId))
            ->first(fn ($i) => isset($i['folder']) && Str::lower($i['name']) === Str::lower($name));
    }

    /**
     * Lista los hijos directos (carpetas y archivos) de una carpeta, sin
     * crear ni modificar nada. Público para explorar/navegar la estructura
     * real (diagnóstico, selector de carpetas).
     */
    public function listarHijos(string $driveId, string $parentId = 'root'): array
    {
        $res = $this->send(fn () => $this->graph()->get("/drives/{$driveId}/items/{$parentId}/children?\$select=id,name,folder,file,webUrl,size"));

        if (!$res->successful()) {
            $this->fallar('No se pudo leer el contenido de una carpeta en SharePoint.', $res, 'No se pudo conectar con SharePoint.');
        }

        return $res->json('value') ?? [];
    }

    private function createFolder(string $driveId, string $parentId, string $name): array
    {
        $res = $this->send(fn () => $this->graph()->post("/drives/{$driveId}/items/{$parentId}/children", [
            'name' => $name,
            'folder' => new \stdClass(),
            '@microsoft.graph.conflictBehavior' => 'rename',
        ]));

        if (!$res->successful()) {
            $this->fallar("No se pudo crear la carpeta '{$name}' en SharePoint.", $res, 'No se pudo crear una carpeta en SharePoint.');
        }

        return $res->json();
    }

    /**
     * Sube un archivo a una carpeta (por itemId) a partir de una ruta local
     * en disco. Usa upload simple si pesa 4 MB o menos; si no, un upload
     * session fragmentado (Graph lo exige arriba de ese límite).
     */
    public function uploadFileToFolder(string $driveId, string $folderItemId, string $filename, string $localPath): array
    {
        $filename = $this->sanitizarNombreArchivo($filename);
        $tamano = filesize($localPath);

        if ($tamano === false) {
            throw new \RuntimeException('No se pudo leer el archivo a publicar.');
        }

        if ($tamano <= self::SIMPLE_UPLOAD_LIMIT) {
            return $this->subirSimple($driveId, $folderItemId, $filename, $localPath);
        }

        return $this->subirPorSesion($driveId, $folderItemId, $filename, $localPath, $tamano);
    }

    private function subirSimple(string $driveId, string $folderItemId, string $filename, string $localPath): array
    {
        $res = $this->send(fn () => $this->graph()
            ->withBody(file_get_contents($localPath), 'application/octet-stream')
            ->put("/drives/{$driveId}/items/{$folderItemId}:/{$filename}:/content"));

        if (!$res->successful()) {
            $this->fallar("No se pudo subir el archivo '{$filename}' a SharePoint.", $res, 'No se pudo subir el archivo a SharePoint.');
        }

        return $res->json();
    }

    private function subirPorSesion(string $driveId, string $folderItemId, string $filename, string $localPath, int $tamano): array
    {
        $resSesion = $this->send(fn () => $this->graph()->post(
            "/drives/{$driveId}/items/{$folderItemId}:/{$filename}:/createUploadSession",
            ['item' => ['@microsoft.graph.conflictBehavior' => 'replace']]
        ));

        if (!$resSesion->successful()) {
            $this->fallar("No se pudo iniciar la sesión de carga de '{$filename}' en SharePoint.", $resSesion, 'No se pudo iniciar la subida del archivo a SharePoint.');
        }

        $uploadUrl = $resSesion->json('uploadUrl');
        $handle = fopen($localPath, 'rb');

        if ($handle === false) {
            throw new \RuntimeException('No se pudo leer el archivo a publicar.');
        }

        try {
            $offset = 0;
            $ultimaRespuesta = null;

            while ($offset < $tamano) {
                $largoFragmento = min(self::UPLOAD_CHUNK_SIZE, $tamano - $offset);
                $fragmento = fread($handle, $largoFragmento);
                $desde = $offset;
                $hasta = $offset + $largoFragmento - 1;

                $ultimaRespuesta = $this->send(fn () => Http::timeout(60)
                    ->withBody($fragmento, 'application/octet-stream')
                    ->withHeaders([
                        'Content-Range' => "bytes {$desde}-{$hasta}/{$tamano}",
                    ])
                    ->put($uploadUrl));

                if (!$ultimaRespuesta->successful()) {
                    $this->fallar("No se pudo subir un fragmento de '{$filename}' a SharePoint.", $ultimaRespuesta, 'No se pudo subir el archivo a SharePoint.');
                }

                $offset += $largoFragmento;
            }

            return $ultimaRespuesta->json();
        } finally {
            fclose($handle);
        }
    }

    /**
     * Mueve un item a otra carpeta (itemId destino).
     */
    public function moveItem(string $driveId, string $itemId, string $destFolderItemId, ?string $newName = null): array
    {
        $payload = [
            'parentReference' => ['id' => $destFolderItemId],
        ];

        if ($newName) {
            $payload['name'] = $newName;
        }

        $res = $this->send(fn () => $this->graph()->patch("/drives/{$driveId}/items/{$itemId}", $payload));

        if (!$res->successful()) {
            $this->fallar('No se pudo mover un archivo en SharePoint.', $res, 'No se pudo mover el archivo en SharePoint.');
        }

        return $res->json();
    }

    /**
     * Consulta un driveItem por id (solo lectura) — útil para verificar que
     * una subida realmente existe en SharePoint después de publicarla.
     */
    public function obtenerItem(string $driveId, string $itemId): array
    {
        $res = $this->send(fn () => $this->graph()->get("/drives/{$driveId}/items/{$itemId}"));

        if (!$res->successful()) {
            $this->fallar('No se pudo consultar un archivo en SharePoint.', $res, 'No se pudo verificar el archivo en SharePoint.');
        }

        return $res->json();
    }

    /**
     * Limpia caracteres que SharePoint no acepta en nombres de archivo y
     * conserva la extensión real (nunca se fuerza a .pdf).
     */
    public function sanitizarNombreArchivo(string $filename): string
    {
        $limpio = preg_replace('/["*:<>?\/\\\\|]/u', '', $filename);
        $limpio = trim($limpio);
        $limpio = rtrim($limpio, '.');
        $limpio = trim($limpio);

        return $limpio === '' ? 'archivo' : $limpio;
    }
}
