<?php

namespace App\Console\Commands;

use App\Jobs\PublicarVersionEnSharePoint;
use App\Models\DocumentoVersion;
use App\Services\SharePoint\ClasificadorDocumentoSharePoint as Clasificador;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class PublicarDocumentosExistentesEnSharePoint extends Command
{
    protected $signature = 'sharepoint:publicar-existentes
        {--dry-run : No despacha nada, solo reporta qué haría}
        {--documento= : Limita la migración a un solo documento por su ID}';

    protected $description = 'Publica en SharePoint las versiones vigentes que aún no tienen archivo publicado (sp_item_id)';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $documentoId = $this->option('documento');

        $query = DocumentoVersion::query()
            ->where('estatus', 'vigente')
            ->whereNull('sp_item_id')
            ->with('documento');

        if ($documentoId) {
            $query->where('documento_id', $documentoId);
        }

        $versiones = $query->get();

        $despachadas = 0;
        $reportadas = 0;

        foreach ($versiones as $version) {
            $doc = $version->documento;

            if (!$doc) {
                continue;
            }

            // Sin archivo local (solo liga_archivo externa): se reporta, no se toca.
            if (!$version->archivo_storage || !Storage::disk('public')->exists($version->archivo_storage)) {
                $this->line("[solo liga externa] {$doc->codigo} (versión #{$version->id}) — no se publica, no tiene archivo local.");
                $reportadas++;

                continue;
            }

            $tipoCanonico = Clasificador::normalizarTipo($doc->tipo_documento);

            if (!$tipoCanonico) {
                $this->line("[sin tipo identificable] {$doc->codigo} (versión #{$version->id}) — tipo_documento='{$doc->tipo_documento}', requiere asignar la carpeta a mano.");
                $reportadas++;

                continue;
            }

            $carpetaVigentePath = trim((config('sharepoint.root_folder') ? config('sharepoint.root_folder') . '/' : '') . Clasificador::carpetaVigente($tipoCanonico), '/');

            if ($dryRun) {
                $this->line("[dry-run] {$doc->codigo} (versión #{$version->id}) — se publicaría en '{$carpetaVigentePath}' con '{$version->archivo_storage}'.");
                $despachadas++;

                continue;
            }

            $version->update(['sp_estado' => 'pendiente', 'sp_folder_path' => $carpetaVigentePath]);

            PublicarVersionEnSharePoint::dispatch($doc->id, $version->id, $version->archivo_storage);

            $this->line("[encolada] {$doc->codigo} (versión #{$version->id}) → {$carpetaVigentePath}.");
            $despachadas++;
        }

        $this->info("Listo: {$despachadas} encoladas/reportadas como publicables, {$reportadas} solo con liga externa.");

        return self::SUCCESS;
    }
}
