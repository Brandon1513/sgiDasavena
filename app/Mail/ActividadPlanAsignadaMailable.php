<?php

namespace App\Mail;

use App\Domains\Incidencias\Models\AcActividad;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ActividadPlanAsignadaMailable extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public AcActividad $actividad)
    {
    }

    public function build()
    {
        $accionCorrectiva = $this->actividad->planAccion->accionCorrectiva;

        return $this->subject("Nueva actividad asignada: {$accionCorrectiva->codigo}")
            ->view('emails.ac_actividad_asignada')
            ->with([
                'actividad' => $this->actividad,
                'accionCorrectiva' => $accionCorrectiva,
            ]);
    }
}
