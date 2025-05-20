<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use App\Models\SolicitudFormato;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Contracts\Queue\ShouldQueue;

class DivulgacionFormatoMailable extends Mailable
{
    use Queueable, SerializesModels;
    public $solicitud;

    public function __construct(SolicitudFormato $solicitud)
    {
        $this->solicitud = $solicitud;
    }

    public function build()
    {
        return $this->subject('Divulgación de Actualización de Formato')
            ->markdown('emails.divulgacion_formato');
    }

}
