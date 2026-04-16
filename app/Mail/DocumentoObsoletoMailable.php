<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Bus\Queueable;

class DocumentoObsoletoMailable extends Mailable
{
         
    use Queueable, SerializesModels;

    public $documento;
    public $usuario;

    public function __construct($documento, $usuario)
    {
        $this->documento = $documento;
        $this->usuario = $usuario;
    }

    public function build()
    {
        return $this->subject('Documento marcado como obsoleto')
            ->view('emails.documento_obsoleto');
    }
}