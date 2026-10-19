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
     * Calcula la carpeta BASE sugerida para un vigente nuevo:
     * "{root_folder}/{Área real}/{Tipo real}" (sin la subcarpeta de Código,
     * que el sistema siempre gestiona aparte). Requiere resolver el Área y
     * el tipo con confianza; si cualquiera de los dos falla, regresa null a
     * propósito — es preferible exigir selección manual que archivar en el
     * lugar equivocado o duplicar una carpeta.
     */
    public function sugerirCarpetaVigente(?string $area, ?string $tipoDocumento): ?string
    {
        $tipoCanonico = Clasificador::normalizarTipo($tipoDocumento);

        if (!$tipoCanonico) {
            return null;
        }

        $siteId = $this->sp->getSiteId();
        $driveId = $this->sp->getDriveId($siteId);

        $root = trim(config('sharepoint.root_folder'), '/');
        $raiz = $this->sp->buscarCarpeta($driveId, $root);

        if (!$raiz) {
            Log::warning('No se encontró la carpeta raíz de vigentes configurada.', ['root_folder' => $root]);

            return null;
        }

        $carpetasArea = $this->sp->listarHijos($driveId, $raiz['id']);
        $nombresArea = collect($carpetasArea)->filter(fn ($i) => isset($i['folder']))->pluck('name')->all();
        $nombreArea = Clasificador::buscarNombreAreaExistente($nombresArea, $area);

        if (!$nombreArea) {
            return null;
        }

        $carpetaArea = collect($carpetasArea)->first(fn ($i) => $i['name'] === $nombreArea);
        $hijosArea = $this->sp->listarHijos($driveId, $carpetaArea['id']);
        $nombresTipo = collect($hijosArea)->filter(fn ($i) => isset($i['folder']))->pluck('name')->all();

        $nombreTipo = Clasificador::buscarNombreTipoExistente($nombresTipo, $tipoCanonico)
            ?? Clasificador::carpetaVigente($tipoCanonico);

        return "{$root}/{$nombreArea}/{$nombreTipo}";
    }

    /**
     * Sube el archivo oficial de $newVer a la base ya decidida en
     * $newVer->sp_folder_path (sugerida automáticamente como
     * {Raíz}/{Área}/{Tipo}, o elegida a mano por el administrador_sgi con
     * el selector de carpetas). Si $crearSubcarpetaCodigo es true (default),
     * se crea/usa además una subcarpeta con el código del documento dentro
     * de esa base — el administrador decide esto explícitamente en el
     * formulario, nunca es automático sin preguntar. Si había una versión
     * vigente anterior con archivo ya ligado (sp_item_id), se mueve a
     * Obsoletos automáticamente.
     *
     * $itemsObsoletos son archivos que ya estaban en SharePoint antes del
     * sistema (subidos a mano, sin sp_item_id) y que el administrador marcó
     * en el selector para retirarlos: se mueven a Obsoletos ANTES de subir
     * el archivo nuevo, para que una subida con el mismo nombre no los
     * sobrescriba. El primero queda ligado a la versión anterior si esta no
     * tenía archivo, para conservar la trazabilidad.
     */
    public function handle(
        Documento $doc,
        DocumentoVersion $newVer,
        string $filePathPublic,
        ?DocumentoVersion $versionAnterior = null,
        bool $crearSubcarpetaCodigo = true,
        ?string $nombreArchivoManual = null,
        array $itemsObsoletos = [],
    ): DocumentoVersion {
        if (!$newVer->sp_folder_path) {
            throw new \RuntimeException('La versión no tiene una carpeta de destino asignada en SharePoint.');
        }

        $siteId = $this->sp->getSiteId();
        $driveId = $this->sp->getDriveId($siteId);

        $carpetaBase = $newVer->sp_folder_path;
        $carpetaDestino = $crearSubcarpetaCodigo ? "{$carpetaBase}/{$doc->codigo}" : $carpetaBase;
        $vigFolderId = $this->sp->ensureFolderPath($driveId, $carpetaDestino);

        $anteriorDistinta = $versionAnterior && $versionAnterior->id !== $newVer->id ? $versionAnterior : null;

        // 1) La vigente anterior se mueve a Obsoletos automáticamente. Si no
        // se puede resolver Área o tipo con confianza, se omite el
        // movimiento (se deja el archivo donde está) en vez de arriesgar una
        // carpeta equivocada.
        if ($anteriorDistinta?->sp_item_id) {
            $this->moverAObsoletos($doc, $anteriorDistinta, $driveId);
        }

        // 1b) Archivos previos al sistema que el administrador marcó a mano.
        if ($itemsObsoletos) {
            $this->moverArchivosAObsoletos($doc, $itemsObsoletos, $anteriorDistinta, $driveId);
        }

        // 2) Subir el archivo nuevo (nombre elegido a mano, o autogenerado
        // conservando la extensión real).
        $localPath = Storage::disk('public')->path($filePathPublic);
        $filename = $this->buildFilename($doc, $newVer, $filePathPublic, $nombreArchivoManual);

        $item = $this->sp->uploadFileToFolder($driveId, $vigFolderId, $filename, $localPath);

        $newVer->sp_drive_id = $driveId;
        $newVer->sp_item_id = $item['id'] ?? null;
        $newVer->sp_web_url = $item['webUrl'] ?? null;
        $newVer->sp_folder_path = $carpetaDestino;
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

        $destino = $this->carpetaObsoletos($doc, $driveId, ['documento_version_id' => $version->id]);

        if (!$destino) {
            return;
        }

        $this->sp->moveItem($driveId, $version->sp_item_id, $destino['id'], renombrarSiExiste: true);

        $version->update(['sp_folder_path' => $destino['path']]);
    }

    /**
     * Mueve a Obsoletos archivos de SharePoint elegidos a mano por el
     * administrador (archivos previos al sistema, sin sp_item_id). Por
     * seguridad solo mueve archivos (no carpetas) que estén dentro de la
     * rama de vigentes; lo que ya esté en Obsoletos o fuera de esa rama se
     * omite, lo que además hace seguro reintentar el job. Un archivo que
     * falle no detiene a los demás ni la publicación.
     *
     * Si $ligarA (la versión que queda obsoleta) no tenía archivo ligado, se
     * le liga el primer archivo movido para conservar la trazabilidad.
     */
    public function moverArchivosAObsoletos(Documento $doc, array $itemIds, ?DocumentoVersion $ligarA = null, ?string $driveId = null): void
    {
        $itemIds = array_values(array_unique(array_filter($itemIds, 'is_string')));

        if (!$itemIds) {
            return;
        }

        $driveId ??= $this->sp->getDriveId($this->sp->getSiteId());

        $destino = $this->carpetaObsoletos($doc, $driveId, ['documento_id' => $doc->id, 'items' => $itemIds]);

        if (!$destino) {
            return;
        }

        foreach ($itemIds as $itemId) {
            try {
                $item = $this->sp->obtenerItem($driveId, $itemId);

                if (!isset($item['file']) || !$this->estaEnVigentes($item)) {
                    Log::warning('Se omitió mover a Obsoletos un elemento que no es un archivo vigente.', [
                        'documento_id' => $doc->id,
                        'item_id' => $itemId,
                        'ruta' => $item['parentReference']['path'] ?? null,
                    ]);

                    continue;
                }

                $movido = $this->sp->moveItem($driveId, $itemId, $destino['id'], renombrarSiExiste: true);

                if ($ligarA && !$ligarA->sp_item_id) {
                    $ligarA->update([
                        'sp_drive_id' => $driveId,
                        'sp_item_id' => $movido['id'] ?? $itemId,
                        'sp_web_url' => $movido['webUrl'] ?? null,
                        'sp_folder_path' => $destino['path'],
                    ]);
                }

                Log::info('Archivo de SharePoint movido a Obsoletos a petición del administrador.', [
                    'documento_id' => $doc->id,
                    'item_id' => $itemId,
                    'nombre' => $item['name'] ?? null,
                    'destino' => $destino['path'],
                ]);
            } catch (\Throwable $e) {
                Log::error('No se pudo mover a Obsoletos un archivo de SharePoint.', [
                    'documento_id' => $doc->id,
                    'item_id' => $itemId,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Carpeta de Obsoletos que le corresponde al documento dentro de la
     * estructura real "Sistema de Gestión Obsoleto/{Área}/{Tipo} obsoletos":
     * ['id' => ..., 'path' => ...]. Si no se puede identificar el Área o el
     * tipo con confianza, regresa null (y lo registra para revisión manual)
     * — nunca crea una carpeta de Área nueva por su cuenta.
     */
    private function carpetaObsoletos(Documento $doc, string $driveId, array $contexto): ?array
    {
        // Ruta fija e independiente de la raíz de vigentes: "Sistema de
        // Gestión Obsoleto" vive en una rama aparte que no se mueve si
        // cambia sharepoint.root_folder.
        $obsoletoRootPath = trim(config('sharepoint.obsoleto_root_folder'), '/');

        $obsoletoRoot = $this->sp->buscarCarpeta($driveId, $obsoletoRootPath);

        if (!$obsoletoRoot) {
            Log::warning('No se encontró la carpeta de Obsoletos configurada: no se movió el archivo.', $contexto + [
                'obsoleto_root_folder' => $obsoletoRootPath,
            ]);

            return null;
        }

        $carpetasArea = $this->sp->listarHijos($driveId, $obsoletoRoot['id']);
        $nombresArea = collect($carpetasArea)->filter(fn ($i) => isset($i['folder']))->pluck('name')->all();
        $nombreArea = Clasificador::buscarNombreAreaExistente($nombresArea, $doc->area);

        if (!$nombreArea) {
            Log::warning('No se pudo identificar con confianza la carpeta de Área en "Sistema de Gestión Obsoleto": no se movió el archivo.', $contexto + [
                'area' => $doc->area,
            ]);

            return null;
        }

        $tipoCanonico = Clasificador::normalizarTipo($doc->tipo_documento);

        if (!$tipoCanonico) {
            Log::warning('No se pudo identificar el tipo de documento para archivarlo en Obsoletos: no se movió el archivo.', $contexto + [
                'tipo_documento' => $doc->tipo_documento,
            ]);

            return null;
        }

        $carpetaArea = collect($carpetasArea)->first(fn ($i) => $i['name'] === $nombreArea);
        $hijosArea = $this->sp->listarHijos($driveId, $carpetaArea['id']);
        $nombresObsoletos = collect($hijosArea)->filter(fn ($i) => isset($i['folder']))->pluck('name')->all();

        $nombreExistente = Clasificador::buscarNombreObsoletosExistente($nombresObsoletos, $tipoCanonico);

        if ($nombreExistente) {
            return [
                'id' => collect($hijosArea)->first(fn ($i) => $i['name'] === $nombreExistente)['id'],
                'path' => "{$obsoletoRootPath}/{$nombreArea}/{$nombreExistente}",
            ];
        }

        // Nunca existió esa combinación Área+Tipo: se crea siguiendo el
        // patrón más común observado ("{Tipo plural} obsoletos").
        $nombreFallback = Clasificador::nombreObsoletosFallback($tipoCanonico);
        $path = "{$obsoletoRootPath}/{$nombreArea}/{$nombreFallback}";

        return ['id' => $this->sp->ensureFolderPath($driveId, $path), 'path' => $path];
    }

    /**
     * Si el item vive dentro de la rama de vigentes (root_folder) y no en la
     * de Obsoletos. parentReference.path tiene la forma
     * "/drives/{id}/root:/Carpeta/Subcarpeta".
     */
    private function estaEnVigentes(array $item): bool
    {
        $ruta = rawurldecode((string) ($item['parentReference']['path'] ?? ''));
        $pos = strpos($ruta, 'root:');

        if ($pos === false) {
            return false;
        }

        $ruta = mb_strtolower(trim(substr($ruta, $pos + 5), '/'));
        $dentroDe = fn (string $base) => $base === '' || $ruta === $base || str_starts_with($ruta, "{$base}/");

        $vigentes = mb_strtolower(trim((string) config('sharepoint.root_folder'), '/'));
        $obsoletos = mb_strtolower(trim((string) config('sharepoint.obsoleto_root_folder'), '/'));

        if ($obsoletos !== '' && $dentroDe($obsoletos)) {
            return false;
        }

        return $dentroDe($vigentes);
    }

    private function buildFilename(Documento $doc, DocumentoVersion $ver, string $filePathPublic, ?string $nombreManual = null): string
    {
        $extensionReal = pathinfo($filePathPublic, PATHINFO_EXTENSION);

        if ($nombreManual) {
            $yaTieneExtension = pathinfo($nombreManual, PATHINFO_EXTENSION) !== '';
            $nombre = $yaTieneExtension ? $nombreManual : "{$nombreManual}.{$extensionReal}";

            return $this->limpiar($nombre);
        }

        $codigo = $doc->codigo;
        $v = $ver->version ?? 'v?';
        $rev = $ver->revision_actual ?? 'rev?';
        $date = optional($ver->publicado_en)->format('Y-m-d') ?? now()->format('Y-m-d');
        $extension = $extensionReal ? '.' . $extensionReal : '';

        return $this->limpiar("{$codigo}_V{$v}_R{$rev}_{$date}{$extension}");
    }

    private function limpiar(string $nombre): string
    {
        return preg_replace('/[^\w\-. ]+/u', '', $nombre);
    }
}
