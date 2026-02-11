<?php

namespace App\Mail;

use App\Models\Documento;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DocumentoNecesitaActualizacionMailable extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Documento $documento,
        public string $mensaje,
        public string $remitenteNombre
    ) {}

    public function build()
    {
        return $this
            ->subject("Actualización requerida: {$this->documento->codigo} - {$this->documento->nombre}")
            ->view('emails.documento_necesita_actualizacion');
    }
}
