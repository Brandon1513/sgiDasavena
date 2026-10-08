<?php

namespace App\Jobs;

use App\Actions\SharePoint\PublishDocumentoVersion;
use App\Mail\PublicacionSharePointFallidaMailable;
use App\Models\Documento;
use App\Models\DocumentoVersion;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class PublicarVersionEnSharePoint implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    /**
     * Minutos de espera antes de cada reintento.
     */
    public array $backoff = [60, 300, 900];

    // NO usar "readonly" aquí: rompe la (de)serialización de la cola — un
    // job real llegó a tronar en el worker con "must not be accessed before
    // initialization" en cuanto se marcaron readonly estas propiedades.
    public function __construct(
        public int $documentoId,
        public int $documentoVersionId,
        public string $filePathPublic,
        public ?int $versionAnteriorId = null,
        public bool $crearSubcarpetaCodigo = true,
        public ?string $nombreArchivoManual = null,
    ) {
    }

    public function handle(PublishDocumentoVersion $publicar): void
    {
        $documento = Documento::findOrFail($this->documentoId);
        $version = DocumentoVersion::findOrFail($this->documentoVersionId);
        $versionAnterior = $this->versionAnteriorId ? DocumentoVersion::find($this->versionAnteriorId) : null;

        $publicar->handle(
            $documento,
            $version,
            $this->filePathPublic,
            $versionAnterior,
            $this->crearSubcarpetaCodigo,
            $this->nombreArchivoManual,
        );
    }

    public function failed(?Throwable $exception): void
    {
        $version = DocumentoVersion::find($this->documentoVersionId);

        if (!$version) {
            return;
        }

        $mensaje = $exception?->getMessage() ?? 'Error desconocido.';

        // La versión sigue vigente en el SGI aunque la publicación en
        // SharePoint haya fallado definitivamente: los vencimientos no
        // dependen de esto.
        $version->update([
            'sp_estado' => 'error',
            'sp_error' => $mensaje,
        ]);

        Log::error('No se pudo publicar la versión en SharePoint tras agotar los reintentos.', [
            'documento_version_id' => $this->documentoVersionId,
            'error' => $mensaje,
        ]);

        $documento = Documento::find($this->documentoId);

        if (!$documento) {
            return;
        }

        $admins = User::role('administrador_sgi')->get();

        foreach ($admins as $admin) {
            if (!$admin->email) {
                continue;
            }

            try {
                Mail::to($admin->email)->send(
                    new PublicacionSharePointFallidaMailable($documento, $version, $mensaje)
                );
            } catch (Throwable $e) {
                Log::error('No se pudo enviar la notificación de fallo de publicación en SharePoint.', [
                    'email' => $admin->email,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }
}
