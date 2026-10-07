<?php

namespace App\Jobs;

use App\Actions\SharePoint\PublishDocumentoVersion;
use App\Models\Documento;
use App\Models\DocumentoVersion;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Mueve en SharePoint el archivo ya publicado de una versión a la carpeta
 * Obsoletos de su documento (al dar de baja el documento, o al marcar la
 * versión obsoleta manualmente). No afecta el estatus de la versión en el
 * SGI: eso ya lo hizo quien despachó este job.
 */
class MoverVersionASharePointObsoletos implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public array $backoff = [60, 300, 900];

    public function __construct(
        private int $documentoId,
        private int $documentoVersionId,
    ) {
    }

    public function handle(PublishDocumentoVersion $publicar): void
    {
        $documento = Documento::findOrFail($this->documentoId);
        $version = DocumentoVersion::findOrFail($this->documentoVersionId);

        $publicar->moverAObsoletos($documento, $version);
    }

    public function failed(?Throwable $exception): void
    {
        Log::error('No se pudo mover el archivo de una versión a Obsoletos en SharePoint.', [
            'documento_version_id' => $this->documentoVersionId,
            'error' => $exception?->getMessage(),
        ]);
    }
}
