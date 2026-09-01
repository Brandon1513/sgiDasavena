<?php

namespace App\Domains\Incidencias\Actions;

use App\Domains\Incidencias\Models\AcActividad;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CompletarActividad
{
    public function ejecutar(
        AcActividad $actividad,
        ?string $observaciones = null
    ): AcActividad {
        return DB::transaction(function () use (
            $actividad,
            $observaciones
        ) {

            $actividad->loadMissing([
                'evidencias',
                'planAccion.accionCorrectiva',
            ]);

            /*
             * 1. La actividad debe estar pendiente.
             */
            if ($actividad->estado === 'completada') {
                throw ValidationException::withMessages([
                    'actividad' =>
                        'La actividad ya se encuentra completada.',
                ]);
            }

            /*
             * 2. Debe existir al menos una evidencia.
             */
            if ($actividad->evidencias->isEmpty()) {
                throw ValidationException::withMessages([
                    'evidencias' =>
                        'No es posible completar la actividad. '
                        . 'Debe cargarse al menos una evidencia.',
                ]);
            }

            /*
             * 3. Registrar cumplimiento.
             */
            $actividad->update([
                'estado' => 'completada',
                'fecha_cumplimiento' => now()->toDateString(),
                'observaciones' => $observaciones ?? $actividad->observaciones,
            ]);

            /*
             * 4. Recalcular porcentaje de avance.
             */
            $plan = $actividad->planAccion;

            $totalActividades = $plan->actividades()->count();

            $actividadesCompletadas = $plan->actividades()
                ->where('estado', 'completada')
                ->count();

            $porcentaje = $totalActividades > 0
                ? round(($actividadesCompletadas / $totalActividades) * 100, 2)
                : 0;

            /*
             * 5. Actualizar porcentaje de la AC.
             */
            $plan->accionCorrectiva->update([
                'porcentaje_avance' => $porcentaje,
            ]);

            /*
             * 6. Si todas las actividades están completas,
             * marcar el plan como completado.
             */
            if (
                $totalActividades > 0 &&
                $actividadesCompletadas === $totalActividades
            ) {
                $plan->update([
                    'estado' => 'completado',
                    'fecha_cierre' => now()->toDateString(),
                ]);
            }

            return $actividad->fresh([
                'evidencias',
                'planAccion.accionCorrectiva',
            ]);
        });
    }
}