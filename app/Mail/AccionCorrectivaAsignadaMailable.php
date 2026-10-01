<?php

namespace App\Mail;

use App\Domains\Incidencias\Models\AccionCorrectiva;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AccionCorrectivaAsignadaMailable extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public AccionCorrectiva $accionCorrectiva)
    {
    }

    public function build()
    {
        return $this->subject("Nueva Acción Correctiva asignada: {$this->accionCorrectiva->codigo}")
            ->view('emails.ac_asignada')
            ->with(['accionCorrectiva' => $this->accionCorrectiva]);
    }
}
