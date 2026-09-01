<?php

namespace App\Domains\Incidencias\Actions;

use App\Domains\Incidencias\Models\AcCincoPorque;
use App\Domains\Incidencias\Models\AcCincoPorquePaso;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AgregarPorque
{
    public function ejecutar(
        AcCincoPorque $cincoPorque,
        string $respuesta
    ): AcCincoPorquePaso {
        return DB::transaction(function () use (
            $cincoPorque,
            $respuesta
        ) {

            if ($cincoPorque->estado !== 'en_proceso') {
                throw ValidationException::withMessages([
                    'cinco_porque' =>
                        'Esta cadena de 5 Porqués ya fue completada.',
                ]);
            }

            $siguienteNumero = (
                $cincoPorque->pasos()->max('numero') ?? 0
            ) + 1;

            if ($siguienteNumero > 5) {
                throw ValidationException::withMessages([
                    'numero' =>
                        'Una cadena de 5 Porqués no puede tener más de 5 pasos.',
                ]);
            }

            return AcCincoPorquePaso::create([
                'ac_cinco_porque_id' => $cincoPorque->id,
                'numero' => $siguienteNumero,
                'pregunta' => "¿Por qué ocurrió el problema en este nivel?",
                'respuesta' => $respuesta,
            ]);
        });
    }
}