<?php

namespace App\Notifications;

use App\Models\SolicitudFormato;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SolicitudNotificacion extends Notification
{
    use Queueable;

    public function __construct(
        private readonly SolicitudFormato $solicitud,
        private readonly string $mensaje,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'solicitud_id' => $this->solicitud->id,
            'mensaje' => $this->mensaje,
            'url' => route('solicitudes.show', $this->solicitud->id),
        ];
    }
}
