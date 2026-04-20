<?php

namespace App\Actions\SharePoint;
use App\Models\Documento;
use App\Models\DocumentoVersion;
use App\Services\SharePointService;
use Illuminate\Support\Facades\Storage;

class PublishDocumentoVersion
{
    public function __construct(private SharePointService $sp) {}

    public function handle(Documento $doc, DocumentoVersion $newVer, ?string $filePathPublic): DocumentoVersion
    {
        $siteId  = $this->sp->getSiteId();
        $driveId = $this->sp->getDriveId($siteId);

        $area   = $doc->area ?? 'General';
        $codigo = $doc->codigo;

        // ✅ Carpeta raíz (donde están los departamentos)
        $root = config('sharepoint.root_folder');
        $prefix = $root ? "{$root}/" : '';

        $vigentesPath = "{$prefix}{$area}/Vigentes/{$codigo}";
        $obsoPath     = "{$prefix}{$area}/Obsoletos/{$codigo}";

        $vigFolderId = $this->sp->ensureFolderPath($driveId, $vigentesPath);
        $obsFolderId = $this->sp->ensureFolderPath($driveId, $obsoPath);

        // 1) Si hay una vigente anterior → mover a Obsoletos (si tiene sp_item_id)
        if ($doc->versionVigente && $doc->versionVigente->id !== $newVer->id) {
            $old = $doc->versionVigente;
            if ($old->sp_item_id) {
                $this->sp->moveItem($driveId, $old->sp_item_id, $obsFolderId);
            }
        }

        // 2) Subir el archivo nuevo a Vigentes (renombrado)
        if ($filePathPublic) {
            $binary = Storage::disk('public')->get($filePathPublic);

            // ✅ aquí renombramos como quieres
            $filename = $this->buildFilename($doc, $newVer);

            $item = $this->sp->uploadFileToFolder($driveId, $vigFolderId, $filename, $binary);

            $newVer->sp_drive_id = $driveId;
            $newVer->sp_item_id  = $item['id'] ?? null;
            $newVer->sp_web_url  = $item['webUrl'] ?? null;
            $newVer->sp_folder_path = $vigentesPath;
            $newVer->save();
        }

        return $newVer;
    }

    private function buildFilename(Documento $doc, DocumentoVersion $ver): string
    {
        // Personaliza como quieras:
        // CODIGO_V{version}_R{rev}_YYYY-MM-DD.pdf
        $codigo = $doc->codigo;
        $v = $ver->version ?? ($ver->folio_version ?? 'v?'); // usa tu campo real
        $rev = $ver->revision_actual ?? 'rev?';
        $date = optional($ver->publicado_en)->format('Y-m-d') ?? now()->format('Y-m-d');

        // limpia caracteres raros
        $safe = preg_replace('/[^\w\-. ]+/u', '', "{$codigo}_V{$v}_R{$rev}_{$date}.pdf");
        return $safe;
    }
}