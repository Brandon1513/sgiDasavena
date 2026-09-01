<?php

namespace App\Domains\Incidencias\Actions;

use App\Domains\Incidencias\Models\AcVerificacionCierre;
use App\Domains\Incidencias\Models\AccionCorrectiva;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RegistrarVerificacionCierre
{
    public function ejecutar(
        AccionCorrectiva $accionCorrectiva,
        int $verificadoPorId,
        bool $accionesImplementadas,
        bool $evidenciasCompletas,
        bool $implementacionConforme,
        ?string $resultado = null,
        ?string $observaciones = null
    ): AcVerificacionCierre {
        return DB::transaction(function () use (
            $accionCorrectiva,
            $verificadoPorId,
            $accionesImplementadas,
            $evidenciasCompletas,
            $implementacionConforme,
            $resultado,
            $observaciones
        ) {

            /*
             * ============================================================
             * 1. La AC debe estar en ejecución
             * ============================================================
             */
            $accionCorrectiva->loadMissing([
                'estado',
            ]);

            if (
                $accionCorrectiva->estado->codigo !== 'ejecucion'
            ) {
                throw ValidationException::withMessages([
                    'estado' =>
                        'La verificación de cierre solamente puede '
                        . 'registrarse cuando la Acción Correctiva está en ejecución.',
                ]);
            }

            /*
             * ============================================================
             * 2. Verificar que el Plan de Acción esté completo
             * ============================================================
             */
            $plan = \App\Domains\Incidencias\Models\AcPlanAccion::query()
                ->where(
                    'accion_correctiva_id',
                    $accionCorrectiva->id
                )
                ->where(
                    'ciclo',
                    $accionCorrectiva->ciclo_actual
                )
                ->with('actividades.evidencias')
                ->first();

            if (!$plan) {
                throw ValidationException::withMessages([
                    'plan' =>
                        'No existe un Plan de Acción para el ciclo actual.',
                ]);
            }

            if ($plan->estado !== 'completado') {
                throw ValidationException::withMessages([
                    'plan' =>
                        'No es posible registrar la verificación de cierre. '
                        . 'El Plan de Acción aún no está completado.',
                ]);
            }

            /*
             * ============================================================
             * 3. Verificar actividades
             * ============================================================
             */
            if ($plan->actividades->isEmpty()) {
                throw ValidationException::withMessages([
                    'actividades' =>
                        'El Plan de Acción no tiene actividades registradas.',
                ]);
            }

            $actividadesPendientes = $plan->actividades
                ->filter(
                    fn ($actividad) =>
                        $actividad->estado !== 'completada'
                );

            if ($actividadesPendientes->isNotEmpty()) {
                throw ValidationException::withMessages([
                    'actividades' =>
                        'Existen actividades del Plan de Acción que todavía '
                        . 'no están completadas.',
                ]);
            }

            /*
             * ============================================================
             * 4. Verificar evidencias
             * ============================================================
             */
            $actividadesSinEvidencia = $plan->actividades
                ->filter(
                    fn ($actividad) =>
                        $actividad->evidencias->isEmpty()
                );

            if ($actividadesSinEvidencia->isNotEmpty()) {
                throw ValidationException::withMessages([
                    'evidencias' =>
                        'Todas las actividades completadas deben contar '
                        . 'con al menos una evidencia.',
                ]);
            }

            /*
             * ============================================================
             * 5. Verificar las tres condiciones de cierre
             * ============================================================
             */
            if (!$accionesImplementadas) {
                throw ValidationException::withMessages([
                    'acciones_implementadas' =>
                        'Debe confirmarse que las acciones fueron implementadas.',
                ]);
            }

            if (!$evidenciasCompletas) {
                throw ValidationException::withMessages([
                    'evidencias_completas' =>
                        'Debe confirmarse que las evidencias están completas.',
                ]);
            }

            if (!$implementacionConforme) {
                throw ValidationException::withMessages([
                    'implementacion_conforme' =>
                        'Debe confirmarse que la implementación fue conforme al plan.',
                ]);
            }

            /*
             * ============================================================
             * 6. Resultado
             * ============================================================
             */
            $resultadoVerificacion = 'aprobada';

            /*
             * ============================================================
             * 7. Crear registro de verificación
             * ============================================================
             */
            return AcVerificacionCierre::create([
                'accion_correctiva_id' => $accionCorrectiva->id,
                'ciclo' => $accionCorrectiva->ciclo_actual,
                'fecha_verificacion' => now()->toDateString(),
                'verificado_por_id' => $verificadoPorId,
                'acciones_implementadas' => true,
                'evidencias_completas' => true,
                'implementacion_conforme' => true,
                'resultado' => $resultado,
                'resultado_verificacion' => $resultadoVerificacion,
                'observaciones' => $observaciones,
            ]);
        });
    }
}