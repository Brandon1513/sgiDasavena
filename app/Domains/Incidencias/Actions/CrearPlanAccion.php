<?php

namespace App\Domains\Incidencias\Actions;

use App\Domains\Incidencias\Models\AccionCorrectiva;
use App\Domains\Incidencias\Models\AcPlanAccion;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CrearPlanAccion
{
    public function ejecutar(
        AccionCorrectiva $accionCorrectiva,
        ?string $observaciones = null
    ): AcPlanAccion {
        return DB::transaction(function () use (
            $accionCorrectiva,
            $observaciones
        ) {

            $accionCorrectiva->loadMissing([
                'estado',
                'analisis.causasRaiz',
            ]);

            /*
             * La AC debe estar en plan de acción.
             */
            if ($accionCorrectiva->estado->codigo !== 'plan_accion') {
                throw ValidationException::withMessages([
                    'accion_correctiva' =>
                        'La Acción Correctiva no se encuentra en la etapa de Plan de Acción.',
                ]);
            }

            /*
             * Buscar el análisis del ciclo actual.
             */
            $analisis = $accionCorrectiva->analisis
                ->where('ciclo', $accionCorrectiva->ciclo_actual)
                ->first();

            if (!$analisis) {
                throw ValidationException::withMessages([
                    'analisis' =>
                        'No existe un análisis para el ciclo actual.',
                ]);
            }

            /*
             * Debe existir una causa raíz aprobada.
             */
            $causaRaizAprobada = $analisis->causasRaiz
                ->where('estado_validacion', 'aprobada')
                ->first();

            if (!$causaRaizAprobada) {
                throw ValidationException::withMessages([
                    'causa_raiz' =>
                        'No existe una causa raíz aprobada para este ciclo.',
                ]);
            }

            /*
             * No permitir dos planes para el mismo ciclo.
             */
            $yaExiste = AcPlanAccion::query()
                ->where('accion_correctiva_id', $accionCorrectiva->id)
                ->where('ciclo', $accionCorrectiva->ciclo_actual)
                ->exists();

            if ($yaExiste) {
                throw ValidationException::withMessages([
                    'plan_accion' =>
                        'Ya existe un Plan de Acción para el ciclo actual.',
                ]);
            }

            return AcPlanAccion::create([
                'accion_correctiva_id' => $accionCorrectiva->id,
                'ac_analisis_id' => $analisis->id,
                'ciclo' => $accionCorrectiva->ciclo_actual,
                'estado' => 'en_proceso',
                'fecha_inicio' => now()->toDateString(),
                'observaciones' => $observaciones,
            ]);
        });
    }
}