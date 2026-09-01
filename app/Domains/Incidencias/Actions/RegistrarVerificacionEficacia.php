<?php

namespace App\Domains\Incidencias\Actions;

use App\Domains\Incidencias\Models\AcEsperaEficacia;
use App\Domains\Incidencias\Models\AcVerificacionEficacia;
use App\Domains\Incidencias\Models\AccionCorrectiva;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RegistrarVerificacionEficacia
{
    public function ejecutar(
        AccionCorrectiva $accionCorrectiva,
        int $verificadoPorId,
        bool $criteriosCumplidos,
        bool $resultadoEficaz,
        string $resultado,
        ?string $observaciones = null
    ): AcVerificacionEficacia {
        return DB::transaction(function () use (
            $accionCorrectiva,
            $verificadoPorId,
            $criteriosCumplidos,
            $resultadoEficaz,
            $resultado,
            $observaciones
        ) {

            $accionCorrectiva->loadMissing([
                'estado',
            ]);

            /*
             * ============================================================
             * 1. La AC debe estar en espera de eficacia
             * ============================================================
             */
            if (
                $accionCorrectiva->estado->codigo !== 'verificacion_eficacia'
            ) {
                throw ValidationException::withMessages([
                    'estado' =>
                    'La verificación de eficacia solamente puede realizarse cuando la Acción Correctiva está en estado de verificación de eficacia.',
                ]);
            }

            /*
             * ============================================================
             * 2. Obtener espera de eficacia del ciclo actual
             * ============================================================
             */
            $espera = AcEsperaEficacia::query()
                ->where(
                    'accion_correctiva_id',
                    $accionCorrectiva->id
                )
                ->where(
                    'ciclo',
                    $accionCorrectiva->ciclo_actual
                )
                ->latest('id')
                ->first();

            if (!$espera) {
                throw ValidationException::withMessages([
                    'espera' =>
                    'No existe una espera de eficacia registrada '
                        . 'para el ciclo actual.',
                ]);
            }

            /*
             * ============================================================
             * 3. La espera debe estar lista para verificación
             * ============================================================
             *
             * Para pruebas permitimos que la fecha ya haya llegado.
             * No se permite verificar antes de la fecha programada.
             */
            $hoy = now()->startOfDay();

            if ($hoy->lt($espera->fecha_verificacion)) {
                throw ValidationException::withMessages([
                    'fecha_verificacion' =>
                    'Todavía no es posible realizar la verificación '
                        . 'de eficacia. La fecha programada es '
                        . $espera->fecha_verificacion->format('Y-m-d') . '.',
                ]);
            }

            /*
             * ============================================================
             * 4. Validar resultado
             * ============================================================
             */
            if (!$resultadoEficaz && $criteriosCumplidos) {
                throw ValidationException::withMessages([
                    'resultado_eficaz' =>
                    'Los criterios no pueden estar cumplidos '
                        . 'si el resultado de eficacia es negativo.',
                ]);
            }

            /*
             * ============================================================
             * 5. Evitar duplicar la verificación del ciclo
             * ============================================================
             */
            $existente = AcVerificacionEficacia::query()
                ->where(
                    'accion_correctiva_id',
                    $accionCorrectiva->id
                )
                ->where(
                    'ciclo',
                    $accionCorrectiva->ciclo_actual
                )
                ->exists();

            if ($existente) {
                throw ValidationException::withMessages([
                    'verificacion' =>
                    'Ya existe una verificación de eficacia '
                        . 'para el ciclo actual.',
                ]);
            }

            /*
             * ============================================================
             * 6. Registrar verificación
             * ============================================================
             */
            $verificacion = AcVerificacionEficacia::create([
                'accion_correctiva_id' => $accionCorrectiva->id,
                'ciclo' => $accionCorrectiva->ciclo_actual,
                'fecha_verificacion' => $hoy->toDateString(),
                'verificado_por_id' => $verificadoPorId,
                'criterios_cumplidos' => $criteriosCumplidos,
                'resultado_eficaz' => $resultadoEficaz,
                'resultado' => $resultado,
                'observaciones' => $observaciones,
            ]);

            /*
             * ============================================================
             * 7. Actualizar espera
             * ============================================================
             */
            $espera->update([
                'estado' => 'completada',
            ]);

            return $verificacion->fresh([
                'accionCorrectiva',
                'verificadoPor',
            ]);
        });
    }
}
