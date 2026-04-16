<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

class DocumentoBajaMailable extends Mailable
{
    public $documento;

    public function __construct($documento)
    {
        $this->documento = $documento;
    }

    public function build()
    {
        return $this->subject('Documento marcado como obsoleto')
            ->view('emails.documento_baja');
    }
}
