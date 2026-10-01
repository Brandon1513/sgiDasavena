<?php

namespace App\Mail;

use App\Domains\Incidencias\Models\AccionCorrectiva;
use App\Domains\Incidencias\Models\AcVerificacionCierre;
use App\Domains\Incidencias\Models\AcVerificacionEficacia;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AccionCorrectivaVerificacionMailable extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  'cierre'|'eficacia'  $tipo
     */
    public function __construct(
        public AccionCorrectiva $accionCorrectiva,
        public string $tipo,
        public AcVerificacionCierre|AcVerificacionEficacia $verificacion,
    ) {
    }

    public function build()
    {
        $aprobada = $this->tipo === 'cierre'
            ? $this->verificacion->resultado_verificacion === 'aprobada'
            : (bool) $this->verificacion->resultado_eficaz;

        $tituloTipo = $this->tipo === 'cierre' ? 'Verificación de cierre' : 'Verificación de eficacia';

        return $this->subject("{$tituloTipo} registrada: {$this->accionCorrectiva->codigo}")
            ->view('emails.ac_verificacion')
            ->with([
                'accionCorrectiva' => $this->accionCorrectiva,
                'tipo' => $this->tipo,
                'tituloTipo' => $tituloTipo,
                'verificacion' => $this->verificacion,
                'aprobada' => $aprobada,
            ]);
    }
}
