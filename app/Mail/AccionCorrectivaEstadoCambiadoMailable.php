<?php

namespace App\Mail;

use App\Domains\Incidencias\Models\AccionCorrectiva;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AccionCorrectivaEstadoCambiadoMailable extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public AccionCorrectiva $accionCorrectiva,
        public string $estadoAnterior,
        public string $estadoNuevo,
    ) {
    }

    public function build()
    {
        return $this->subject("Acción Correctiva {$this->accionCorrectiva->codigo}: cambio de estado")
            ->view('emails.ac_estado_cambiado')
            ->with([
                'accionCorrectiva' => $this->accionCorrectiva,
                'estadoAnterior' => $this->estadoAnterior,
                'estadoNuevo' => $this->estadoNuevo,
            ]);
    }
}
