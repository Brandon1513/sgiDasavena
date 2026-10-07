<?php

namespace App\Actions\SharePoint;

use App\Models\Documento;
use App\Models\DocumentoVersion;
use App\Services\SharePoint\ClasificadorDocumentoSharePoint as Clasificador;
use App\Services\SharePointService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PublishDocumentoVersion
{
    public function __construct(private SharePointService $sp)
    {
    }

    /**
     * Sube el archivo oficial de $newVer a la carpeta que ya quedó decidida
     * en $newVer->sp_folder_path (sugerida automáticamente por tipo de
     * documento, o elegida a mano por el administrador_sgi con el selector
     * de carpetas — en ambos casos ya resuelta ANTES de llegar aquí). Si
     * había una versión vigente anterior distinta, intenta moverla a
     * Obsoletos automáticamente (nunca es una elección manual).
     */
    public function handle(Documento $doc, DocumentoVersion $newVer, string $filePathPublic, ?DocumentoVersion $versionAnterior = null): DocumentoVersion
    {
        if (!$newVer->sp_folder_path) {
            throw new \RuntimeException('La versión no tiene una carpeta de destino asignada en SharePoint.');
        }

        $siteId = $this->sp->getSiteId();
        $driveId = $this->sp->getDriveId($siteId);

        $vigFolderId = $this->sp->ensureFolderPath($driveId, $newVer->sp_folder_path);

        // 1) La vigente anterior se mueve a Obsoletos automáticamente (el
        // administrador nunca elige esto a mano). Si no se puede resolver
        // Área o tipo con confianza, se omite el movimiento (se deja el
        // archivo donde está) en vez de arriesgar una carpeta equivocada.
        if ($versionAnterior && $versionAnterior->id !== $newVer->id && $versionAnterior->sp_item_id) {
            $this->moverAObsoletos($doc, $versionAnterior, $driveId);
        }

        // 2) Subir el archivo nuevo (renombrado, conservando su extensión real).
        $localPath = Storage::disk('public')->path($filePathPublic);
        $filename = $this->buildFilename($doc, $newVer, $filePathPublic);

        $item = $this->sp->uploadFileToFolder($driveId, $vigFolderId, $filename, $localPath);

        $newVer->sp_drive_id = $driveId;
        $newVer->sp_item_id = $item['id'] ?? null;
        $newVer->sp_web_url = $item['webUrl'] ?? null;
        $newVer->sp_estado = 'publicado';
        $newVer->sp_error = null;
        $newVer->save();

        return $newVer;
    }

    /**
     * Mueve el archivo ya publicado de una versión a la carpeta de
     * Obsoletos que le corresponde dentro de la estructura real
     * "Sistema de Gestión Obsoleto/{Área}/{Tipo} obsoletos". Si no se puede
     * identificar el Área o el tipo con confianza, no mueve nada (se
     * registra en el log para revisión manual) — nunca crea una carpeta de
     * Área nueva por su cuenta.
     */
    public function moverAObsoletos(Documento $doc, DocumentoVersion $version, ?string $driveId = null): void
    {
        if (!$version->sp_item_id) {
            return;
        }

        $driveId ??= $this->sp->getDriveId($this->sp->getSiteId());

        $root = config('sharepoint.root_folder');
        $obsoletoRootPath = trim(($root ? "{$root}/" : '') . 'Sistema de Gestión Obsoleto', '/');

        $obsoletoRoot = $this->sp->buscarCarpeta($driveId, $obsoletoRootPath);

        if (!$obsoletoRoot) {
            Log::warning('No se encontró la carpeta "Sistema de Gestión Obsoleto": no se movió el archivo.', [
                'documento_version_id' => $version->id,
            ]);

            return;
        }

        $carpetasArea = $this->sp->listarHijos($driveId, $obsoletoRoot['id']);
        $nombresArea = collect($carpetasArea)->filter(fn ($i) => isset($i['folder']))->pluck('name')->all();
        $nombreArea = Clasificador::buscarNombreAreaExistente($nombresArea, $doc->area);

        if (!$nombreArea) {
            Log::warning('No se pudo identificar con confianza la carpeta de Área en "Sistema de Gestión Obsoleto": no se movió el archivo.', [
                'documento_version_id' => $version->id,
                'area' => $doc->area,
            ]);

            return;
        }

        $tipoCanonico = Clasificador::normalizarTipo($doc->tipo_documento);

        if (!$tipoCanonico) {
            Log::warning('No se pudo identificar el tipo de documento para archivarlo en Obsoletos: no se movió el archivo.', [
                'documento_version_id' => $version->id,
                'tipo_documento' => $doc->tipo_documento,
            ]);

            return;
        }

        $carpetaArea = collect($carpetasArea)->first(fn ($i) => $i['name'] === $nombreArea);
        $hijosArea = $this->sp->listarHijos($driveId, $carpetaArea['id']);
        $nombresObsoletos = collect($hijosArea)->filter(fn ($i) => isset($i['folder']))->pluck('name')->all();

        $nombreExistente = Clasificador::buscarNombreObsoletosExistente($nombresObsoletos, $tipoCanonico);

        if ($nombreExistente) {
            $destinoId = collect($hijosArea)->first(fn ($i) => $i['name'] === $nombreExistente)['id'];
        } else {
            // Nunca existió esa combinación Área+Tipo: se crea siguiendo el
            // patrón más común observado ("{Tipo plural} obsoletos").
            $nombreFallback = Clasificador::nombreObsoletosFallback($tipoCanonico);
            $destinoId = $this->sp->ensureFolderPath($driveId, "{$obsoletoRootPath}/{$nombreArea}/{$nombreFallback}");
        }

        $this->sp->moveItem($driveId, $version->sp_item_id, $destinoId);

        $version->update(['sp_folder_path' => "{$obsoletoRootPath}/{$nombreArea}/" . ($nombreExistente ?? Clasificador::nombreObsoletosFallback($tipoCanonico))]);
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
