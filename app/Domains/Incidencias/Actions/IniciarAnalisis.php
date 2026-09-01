<?php

namespace App\Domains\Incidencias\Actions;

use App\Domains\Incidencias\Models\AcAnalisis;
use App\Domains\Incidencias\Models\AccionCorrectiva;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class IniciarAnalisis
{
    public function ejecutar(
        AccionCorrectiva $accionCorrectiva,
        int $responsableId
    ): AcAnalisis {
        return DB::transaction(function () use (
            $accionCorrectiva,
            $responsableId
        ) {

            $accionCorrectiva->loadMissing([
                'estado',
                'analisis',
            ]);

            /*
             * El análisis solamente puede iniciarse
             * cuando la AC se encuentra en análisis.
             */
            if ($accionCorrectiva->estado->codigo !== 'analisis') {
                throw ValidationException::withMessages([
                    'accion_correctiva' =>
                        'No se puede iniciar el análisis de causa raíz porque la Acción Correctiva no se encuentra en la etapa de análisis.',
                ]);
            }

            /*
             * El análisis corresponde al ciclo actual
             * de la Acción Correctiva.
             */
            $ciclo = $accionCorrectiva->ciclo_actual;

            /*
             * Evitar duplicar el análisis del mismo ciclo.
             */
            $existente = $accionCorrectiva->analisis()
                ->where('ciclo', $ciclo)
                ->first();

            if ($existente) {
                throw ValidationException::withMessages([
                    'analisis' =>
                        "Ya existe un análisis para el ciclo {$ciclo} de esta Acción Correctiva.",
                ]);
            }

            return AcAnalisis::create([
                'accion_correctiva_id' => $accionCorrectiva->id,
                'ciclo' => $ciclo,
                'fecha_inicio' => now()->toDateString(),
                'fecha_cierre' => null,
                'responsable_id' => $responsableId,
                'estado' => 'en_proceso',
                'observaciones' => null,
            ]);
        });
    }
}