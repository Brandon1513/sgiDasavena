<?php

namespace App\Mail;

use App\Models\SolicitudFormato;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DocumentoAltaMailable extends Mailable
{
    use Queueable, SerializesModels;

    // 1. Declaramos la variable pública para que la vista del correo pueda usarla
    public $solicitud;

    /**
     * Create a new message instance.
     */
    public function __construct(SolicitudFormato $solicitud)
    {
        // 2. Inyectamos la solicitud al construir el mailable
        $this->solicitud = $solicitud;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Nueva Alta/Actualización de Documento Atendida - SGI',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            // 3. CAMBIA ESTO: Apunta a una vista real que crearemos para el diseño del correo
            view: 'emails.documento_alta', 
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [];
    }
}