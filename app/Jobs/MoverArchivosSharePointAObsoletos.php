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
 * Mueve a Obsoletos archivos de SharePoint elegidos a mano en el selector
 * (archivos subidos antes del sistema, sin sp_item_id) cuando no hay un
 * archivo nuevo que publicar — p. ej. al atender una solicitud de baja. En
 * una publicación normal esto lo hace PublicarVersionEnSharePoint antes de
 * subir el archivo nuevo.
 */
class MoverArchivosSharePointAObsoletos implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public array $backoff = [60, 300, 900];

    public function __construct(
        public int $documentoId,
        public array $itemIds,
        public ?int $versionALigarId = null,
    ) {
    }

    public function handle(PublishDocumentoVersion $publicar): void
    {
        $documento = Documento::findOrFail($this->documentoId);
        $version = $this->versionALigarId ? DocumentoVersion::find($this->versionALigarId) : null;

        $publicar->moverArchivosAObsoletos($documento, $this->itemIds, $version);
    }

    public function failed(?Throwable $exception): void
    {
        Log::error('No se pudieron mover a Obsoletos los archivos elegidos en SharePoint.', [
            'documento_id' => $this->documentoId,
            'items' => $this->itemIds,
            'error' => $exception?->getMessage(),
        ]);
    }
}
