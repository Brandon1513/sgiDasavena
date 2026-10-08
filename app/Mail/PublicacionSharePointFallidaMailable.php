<?php

namespace App\Mail;

use App\Models\Documento;
use App\Models\DocumentoVersion;
use Illuminate\Mail\Mailable;

class PublicacionSharePointFallidaMailable extends Mailable
{
    public function __construct(
        public Documento $documento,
        public DocumentoVersion $version,
        public string $error,
    ) {
    }

    public function build()
    {
        return $this->subject("No se pudo publicar en SharePoint: {$this->documento->codigo}")
            ->view('emails.sharepoint_publicacion_fallida');
    }
}
