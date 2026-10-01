<?php

namespace App\Mail;

use App\Domains\Incidencias\Models\AcActividad;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ActividadPlanPorVencerMailable extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public AcActividad $actividad,
        public int $dias,
    ) {
    }

    public function build()
    {
        $accionCorrectiva = $this->actividad->planAccion->accionCorrectiva;

        $asunto = $this->dias < 0
            ? "Actividad vencida ({$accionCorrectiva->codigo})"
            : "Actividad próxima a vencer en {$this->dias} día" . ($this->dias === 1 ? '' : 's') . " ({$accionCorrectiva->codigo})";

        return $this->subject($asunto)
            ->view('emails.ac_actividad_por_vencer')
            ->with([
                'actividad' => $this->actividad,
                'accionCorrectiva' => $accionCorrectiva,
                'dias' => $this->dias,
            ]);
    }
}
