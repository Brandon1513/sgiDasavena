<?php

namespace App\Mail;

use App\Models\SolicitudFormato;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SolicitudAprobadaSgiMailable extends Mailable
{
    use Queueable, SerializesModels;

    public $solicitud;

    public function __construct(SolicitudFormato $solicitud)
    {
        $this->solicitud = $solicitud;
    }

    public function build()
    {
        return $this->subject('Nueva Solicitud Aprobada por el Jefe')
                    ->markdown('emails.solicitud_aprobada_sgi');
    }
}

