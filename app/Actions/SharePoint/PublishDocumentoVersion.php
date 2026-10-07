<?php

namespace App\Actions\SharePoint;

use App\Models\Documento;
use App\Models\DocumentoVersion;
use App\Services\SharePointService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PublishDocumentoVersion
{
    public function __construct(private SharePointService $sp)
    {
    }

    /**
     * Sube el archivo oficial de $newVer a SharePoint (carpeta Vigentes) y,
     * si había una versión vigente anterior distinta, mueve su archivo a
     * Obsoletos. $filePathPublic es una ruta relativa dentro del disco
     * "public" (por ejemplo la que regresa UploadedFile::store()).
     */
    public function handle(Documento $doc, DocumentoVersion $newVer, string $filePathPublic, ?DocumentoVersion $versionAnterior = null): DocumentoVersion
    {
        $siteId = $this->sp->getSiteId();
        $driveId = $this->sp->getDriveId($siteId);

        $area = $doc->area;

        if (!$area) {
            Log::warning('Documento sin área asignada: se publica en SharePoint bajo "Sin área".', [
                'documento_id' => $doc->id,
            ]);
            $area = 'Sin área';
        }

        $codigo = $doc->codigo;

        $root = config('sharepoint.root_folder');
        $prefix = $root ? "{$root}/" : '';

        $vigentesPath = "{$prefix}{$area}/Vigentes/{$codigo}";
        $obsoPath = "{$prefix}{$area}/Obsoletos/{$codigo}";

        $vigFolderId = $this->sp->ensureFolderPath($driveId, $vigentesPath);

        // 1) La vigente anterior (la que se pasó explícitamente, de antes de
        // reasignar version_vigente_id a la nueva) se mueve a Obsoletos si ya
        // tenía archivo publicado.
        if ($versionAnterior && $versionAnterior->id !== $newVer->id && $versionAnterior->sp_item_id) {
            $obsFolderId = $this->sp->ensureFolderPath($driveId, $obsoPath);
            $this->sp->moveItem($driveId, $versionAnterior->sp_item_id, $obsFolderId);
        }

        // 2) Subir el archivo nuevo a Vigentes (renombrado, conservando su extensión real).
        $localPath = Storage::disk('public')->path($filePathPublic);
        $filename = $this->buildFilename($doc, $newVer, $filePathPublic);

        $item = $this->sp->uploadFileToFolder($driveId, $vigFolderId, $filename, $localPath);

        $newVer->sp_drive_id = $driveId;
        $newVer->sp_item_id = $item['id'] ?? null;
        $newVer->sp_web_url = $item['webUrl'] ?? null;
        $newVer->sp_folder_path = $vigentesPath;
        $newVer->sp_estado = 'publicado';
        $newVer->sp_error = null;
        $newVer->save();

        return $newVer;
    }

    /**
     * Mueve el archivo ya publicado de una versión a la carpeta Obsoletos de
     * su documento (usado al dar de baja un documento o marcar una versión
     * obsoleta manualmente).
     */
    public function moverAObsoletos(Documento $doc, DocumentoVersion $version): void
    {
        if (!$version->sp_item_id) {
            return;
        }

        $siteId = $this->sp->getSiteId();
        $driveId = $this->sp->getDriveId($siteId);

        $area = $doc->area ?: 'Sin área';
        $root = config('sharepoint.root_folder');
        $prefix = $root ? "{$root}/" : '';

        $obsoPath = "{$prefix}{$area}/Obsoletos/{$doc->codigo}";
        $obsFolderId = $this->sp->ensureFolderPath($driveId, $obsoPath);

        $this->sp->moveItem($driveId, $version->sp_item_id, $obsFolderId);

        $version->update(['sp_folder_path' => $obsoPath]);
    }

    private function buildFilename(Documento $doc, DocumentoVersion $ver, string $filePathPublic): string
    {
        $codigo = $doc->codigo;
        $v = $ver->version ?? 'v?';
        $rev = $ver->revision_actual ?? 'rev?';
        $date = optional($ver->publicado_en)->format('Y-m-d') ?? now()->format('Y-m-d');
        $extension = pathinfo($filePathPublic, PATHINFO_EXTENSION);
        $extension = $extension ? '.' . $extension : '';

        return $this->limpiar("{$codigo}_V{$v}_R{$rev}_{$date}{$extension}");
    }

    private function limpiar(string $nombre): string
    {
        return preg_replace('/[^\w\-. ]+/u', '', $nombre);
    }
}
