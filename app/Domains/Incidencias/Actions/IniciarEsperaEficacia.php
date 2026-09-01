<?php

namespace App\Domains\Incidencias\Actions;

use App\Domains\Incidencias\Models\AcEsperaEficacia;
use App\Domains\Incidencias\Models\AccionCorrectiva;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class IniciarEsperaEficacia
{
    public function ejecutar(
        AccionCorrectiva $accionCorrectiva,
        int $responsableId,
        int $diasEspera = 30,
        ?string $observaciones = null
    ): AcEsperaEficacia {
        return DB::transaction(function () use (
            $accionCorrectiva,
            $responsableId,
            $diasEspera,
            $observaciones
        ) {
            $accionCorrectiva->loadMissing([
                'estado',
            ]);

            /*
             * ============================================================
             * 1. La AC debe estar en verificación de cierre
             * ============================================================
             */
            if (
                $accionCorrectiva->estado->codigo !== 'verificacion_cierre'
            ) {
                throw ValidationException::withMessages([
                    'estado' =>
                        'La espera de eficacia solamente puede iniciarse '
                        . 'cuando la Acción Correctiva está en verificación de cierre.',
                ]);
            }

            /*
             * ============================================================
             * 2. Debe existir una verificación de cierre aprobada
             * ============================================================
             */
            $verificacion = \App\Domains\Incidencias\Models\AcVerificacionCierre::query()
                ->where(
                    'accion_correctiva_id',
                    $accionCorrectiva->id
                )
                ->where(
                    'ciclo',
                    $accionCorrectiva->ciclo_actual
                )
                ->where(
                    'resultado_verificacion',
                    'aprobada'
                )
                ->latest('id')
                ->first();

            if (!$verificacion) {
                throw ValidationException::withMessages([
                    'verificacion' =>
                        'No es posible iniciar la espera de eficacia. '
                        . 'Debe existir una verificación de cierre aprobada.',
                ]);
            }

            /*
             * ============================================================
             * 3. Evitar duplicar la espera del mismo ciclo
             * ============================================================
             */
            $existente = AcEsperaEficacia::query()
                ->where(
                    'accion_correctiva_id',
                    $accionCorrectiva->id
                )
                ->where(
                    'ciclo',
                    $accionCorrectiva->ciclo_actual
                )
                ->whereIn('estado', [
                    'en_espera',
                    'lista_verificacion',
                ])
                ->first();

            if ($existente) {
                throw ValidationException::withMessages([
                    'espera' =>
                        'Ya existe una espera de eficacia activa '
                        . 'para el ciclo actual.',
                ]);
            }

            /*
             * ============================================================
             * 4. Validar días
             * ============================================================
             */
            if ($diasEspera < 1) {
                throw ValidationException::withMessages([
                    'diasEspera' =>
                        'El periodo de espera debe ser de al menos un día.',
                ]);
            }

            /*
             * ============================================================
             * 5. Crear espera de eficacia
             * ============================================================
             */
            $fechaInicio = now()->startOfDay();

            $fechaVerificacion = $fechaInicio->copy()
                ->addDays($diasEspera);

            return AcEsperaEficacia::create([
                'accion_correctiva_id' => $accionCorrectiva->id,
                'ciclo' => $accionCorrectiva->ciclo_actual,
                'fecha_inicio' => $fechaInicio->toDateString(),
                'fecha_verificacion' => $fechaVerificacion->toDateString(),
                'responsable_id' => $responsableId,
                'estado' => 'en_espera',
                'observaciones' => $observaciones,
            ]);
        });
    }
}