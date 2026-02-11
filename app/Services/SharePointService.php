<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class SharePointService
{
    private function token(): string
    {
        $tenant = config('sharepoint.tenant_id');
        $clientId = config('sharepoint.client_id');
        $secret = config('sharepoint.client_secret');

        $res = Http::asForm()->post("https://login.microsoftonline.com/{$tenant}/oauth2/v2.0/token", [
            'client_id' => $clientId,
            'client_secret' => $secret,
            'scope' => 'https://graph.microsoft.com/.default',
            'grant_type' => 'client_credentials',
        ]);

        if (!$res->successful()) {
            throw new \RuntimeException('No se pudo obtener token Graph: '.$res->body());
        }

        return $res->json('access_token');
    }

    private function graph()
    {
        return Http::withToken($this->token())
            ->baseUrl('https://graph.microsoft.com/v1.0')
            ->acceptJson();
    }

    public function getSiteId(): string
    {
        $host = config('sharepoint.site_host');
        $path = config('sharepoint.site_path');

        $res = $this->graph()->get("/sites/{$host}:{$path}");
        if (!$res->successful()) throw new \RuntimeException("No se pudo obtener site: ".$res->body());

        return $res->json('id');
    }

    public function getDriveId(string $siteId): string
    {
        if (config('sharepoint.drive_id')) return config('sharepoint.drive_id');

        $driveName = config('sharepoint.drive_name');

        $res = $this->graph()->get("/sites/{$siteId}/drives");
        if (!$res->successful()) throw new \RuntimeException("No se pudo listar drives: ".$res->body());

        $drive = collect($res->json('value'))->firstWhere('name', $driveName);
        if (!$drive) throw new \RuntimeException("No encontré drive con nombre '{$driveName}'.");

        return $drive['id'];
    }

    /**
     * Asegura ruta de carpetas (crea si no existe) y regresa itemId de la carpeta final.
     * $path ejemplo: "SGI/Comercial/Vigentes/F-DIR-05"
     */
    public function ensureFolderPath(string $driveId, string $path): string
    {
        $parts = array_values(array_filter(explode('/', trim($path, '/'))));
        $parentId = 'root';

        foreach ($parts as $name) {
            $child = $this->findChildFolder($driveId, $parentId, $name);
            if (!$child) {
                $child = $this->createFolder($driveId, $parentId, $name);
            }
            $parentId = $child['id'];
        }

        return $parentId;
    }

    private function findChildFolder(string $driveId, string $parentId, string $name): ?array
    {
        // OJO: Graph permite children y filtrar por name, pero a veces requiere encoding
        $res = $this->graph()->get("/drives/{$driveId}/items/{$parentId}/children?\$select=id,name,folder,file");
        if (!$res->successful()) throw new \RuntimeException("No se pudo leer children: ".$res->body());

        return collect($res->json('value'))
            ->first(fn($i) => isset($i['folder']) && Str::lower($i['name']) === Str::lower($name));
    }

    private function createFolder(string $driveId, string $parentId, string $name): array
    {
        $res = $this->graph()->post("/drives/{$driveId}/items/{$parentId}/children", [
            'name' => $name,
            'folder' => new \stdClass(),
            '@microsoft.graph.conflictBehavior' => 'rename',
        ]);

        if (!$res->successful()) throw new \RuntimeException("No se pudo crear folder {$name}: ".$res->body());

        return $res->json();
    }

    /**
     * Sube archivo a una carpeta (por itemId). Devuelve driveItem json (id, webUrl, etc.)
     */
    public function uploadFileToFolder(string $driveId, string $folderItemId, string $filename, string $binaryContent): array
    {
        // Simple upload sirve bien si < 4MB. Para archivos grandes: upload session.
        $res = $this->graph()
            ->withBody($binaryContent, 'application/octet-stream')
            ->put("/drives/{$driveId}/items/{$folderItemId}:/{$filename}:/content");

        if (!$res->successful()) throw new \RuntimeException("No se pudo subir archivo: ".$res->body());

        return $res->json();
    }

    /**
     * Mueve un item a otra carpeta (itemId destino).
     */
    public function moveItem(string $driveId, string $itemId, string $destFolderItemId, ?string $newName = null): array
    {
        $payload = [
            'parentReference' => ['id' => $destFolderItemId],
        ];
        if ($newName) $payload['name'] = $newName;

        $res = $this->graph()->patch("/drives/{$driveId}/items/{$itemId}", $payload);
        if (!$res->successful()) throw new \RuntimeException("No se pudo mover item: ".$res->body());

        return $res->json();
    }
}
