<?php

namespace App\Domains\Incidencias\Actions;

use App\Domains\Incidencias\Enums\EstadoAccionCorrectiva;
use App\Domains\Incidencias\Models\AccionCorrectiva;
use App\Domains\Incidencias\Models\AcPlanAccion;
use App\Domains\Incidencias\Models\EstadoAccionCorrectiva as EstadoModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Domains\Incidencias\Models\AcVerificacionCierre;
use App\Domains\Incidencias\Models\AcVerificacionEficacia;

class CambiarEstadoAccionCorrectiva
{
    /**
     * Transiciones permitidas.
     */
    private const TRANSICIONES = [
        'borrador' => [
            'abierta',
        ],

        'abierta' => [
            'contencion',
        ],

        'contencion' => [
            'analisis',
        ],

        'analisis' => [
            'validacion_causa',
        ],

        'validacion_causa' => [
            'plan_accion',
        ],

        'plan_accion' => [
            'ejecucion',
        ],

        'ejecucion' => [
            'verificacion_cierre',
        ],

        'verificacion_cierre' => [
            'espera_eficacia',
        ],

        'espera_eficacia' => [
            'verificacion_eficacia',
        ],

        'verificacion_eficacia' => [
            'cerrada',
            'analisis',
        ],
    ];

    public function ejecutar(
        AccionCorrectiva $accionCorrectiva,
        EstadoAccionCorrectiva $nuevoEstado
    ): AccionCorrectiva {
        return DB::transaction(function () use (
            $accionCorrectiva,
            $nuevoEstado
        ) {

            /*
             * ============================================================
             * 1. Cargar relaciones necesarias
             * ============================================================
             */
            $accionCorrectiva->loadMissing([
                'estado',
                'contenciones',
            ]);

            /*
             * ============================================================
             * 2. Estado actual
             * ============================================================
             */
            $estadoActual = $accionCorrectiva->estado->codigo;

            /*
             * ============================================================
             * 3. Validar transición permitida
             * ============================================================
             */
            if (
                !isset(self::TRANSICIONES[$estadoActual]) ||
                !in_array(
                    $nuevoEstado->value,
                    self::TRANSICIONES[$estadoActual],
                    true
                )
            ) {
                throw ValidationException::withMessages([
                    'estado' => sprintf(
                        'No es posible cambiar una Acción Correctiva de "%s" a "%s".',
                        $accionCorrectiva->estado->nombre,
                        $nuevoEstado->value
                    ),
                ]);
            }

            /*
             * ============================================================
             * 4. CONTENCIÓN → ANÁLISIS
             *
             * Debe existir al menos una contención.
             * ============================================================
             */
            if (
                $estadoActual === EstadoAccionCorrectiva::CONTENCION->value &&
                $nuevoEstado === EstadoAccionCorrectiva::ANALISIS
            ) {
                if ($accionCorrectiva->contenciones->isEmpty()) {
                    throw ValidationException::withMessages([
                        'estado' =>
                        'No es posible avanzar al análisis de causa raíz. '
                            . 'Debe registrarse al menos una acción de contención.',
                    ]);
                }
            }

            /*
             * ============================================================
             * 5. ANÁLISIS → VALIDACIÓN DE CAUSA
             *
             * Debe existir una causa raíz aprobada.
             * ============================================================
             */
            if (
                $estadoActual === EstadoAccionCorrectiva::ANALISIS->value &&
                $nuevoEstado === EstadoAccionCorrectiva::VALIDACION_CAUSA
            ) {
                $analisis = $accionCorrectiva->analisis()
                    ->where('ciclo', $accionCorrectiva->ciclo_actual)
                    ->with('causasRaiz')
                    ->first();

                if (!$analisis) {
                    throw ValidationException::withMessages([
                        'estado' =>
                        'No es posible pasar a validación de causa. '
                            . 'Debe existir un análisis para el ciclo actual.',
                    ]);
                }

                $causaRaiz = $analisis->causasRaiz
                    ->where('estado_validacion', 'aprobada')
                    ->first();

                if (!$causaRaiz) {
                    throw ValidationException::withMessages([
                        'estado' =>
                        'No es posible pasar a validación de causa. '
                            . 'Debe existir una causa raíz aprobada.',
                    ]);
                }
            }

            /*
             * ============================================================
             * 6. VALIDACIÓN DE CAUSA → PLAN DE ACCIÓN
             *
             * Debe existir una causa raíz aprobada.
             * ============================================================
             */
            if (
                $estadoActual === EstadoAccionCorrectiva::VALIDACION_CAUSA->value &&
                $nuevoEstado === EstadoAccionCorrectiva::PLAN_ACCION
            ) {
                $analisis = $accionCorrectiva->analisis()
                    ->where('ciclo', $accionCorrectiva->ciclo_actual)
                    ->with('causasRaiz')
                    ->first();

                if (!$analisis) {
                    throw ValidationException::withMessages([
                        'estado' =>
                        'No es posible iniciar el Plan de Acción. '
                            . 'No existe un análisis para el ciclo actual.',
                    ]);
                }

                $causaRaiz = $analisis->causasRaiz
                    ->where('estado_validacion', 'aprobada')
                    ->first();

                if (!$causaRaiz) {
                    throw ValidationException::withMessages([
                        'estado' =>
                        'No es posible iniciar el Plan de Acción. '
                            . 'La causa raíz debe estar aprobada.',
                    ]);
                }
            }

            /*
             * ============================================================
             * 7. PLAN DE ACCIÓN → EJECUCIÓN
             *
             * Requisitos:
             *
             * - Existe Plan de Acción para el ciclo actual.
             * - El Plan está completado.
             * - Tiene actividades.
             * - Todas las actividades están completadas.
             * - La AC tiene 100% de avance.
             * ============================================================
             */
            if (
                $estadoActual === EstadoAccionCorrectiva::PLAN_ACCION->value &&
                $nuevoEstado === EstadoAccionCorrectiva::EJECUCION
            ) {
                $plan = AcPlanAccion::query()
                    ->where(
                        'accion_correctiva_id',
                        $accionCorrectiva->id
                    )
                    ->where(
                        'ciclo',
                        $accionCorrectiva->ciclo_actual
                    )
                    ->with('actividades')
                    ->first();

                /*
                 * No existe Plan de Acción.
                 */
                if (!$plan) {
                    throw ValidationException::withMessages([
                        'estado' =>
                        'No es posible iniciar la ejecución. '
                            . 'La Acción Correctiva no tiene un Plan de Acción '
                            . 'para el ciclo actual.',
                    ]);
                }

                /*
                 * El Plan todavía no está completado.
                 */
                if ($plan->estado !== 'completado') {
                    throw ValidationException::withMessages([
                        'estado' =>
                        'No es posible iniciar la ejecución. '
                            . 'El Plan de Acción aún no está completado.',
                    ]);
                }

                /*
                 * Debe existir al menos una actividad.
                 */
                if ($plan->actividades->isEmpty()) {
                    throw ValidationException::withMessages([
                        'estado' =>
                        'No es posible iniciar la ejecución. '
                            . 'El Plan de Acción debe tener al menos una actividad.',
                    ]);
                }

                /*
                 * Todas las actividades deben estar completadas.
                 */
                $hayActividadesPendientes = $plan->actividades
                    ->contains(
                        fn($actividad) =>
                        $actividad->estado !== 'completada'
                    );

                if ($hayActividadesPendientes) {
                    throw ValidationException::withMessages([
                        'estado' =>
                        'No es posible iniciar la ejecución. '
                            . 'Todas las actividades del Plan de Acción '
                            . 'deben estar completadas.',
                    ]);
                }

                /*
                 * La Acción Correctiva debe estar al 100%.
                 */
                if ((float) $accionCorrectiva->porcentaje_avance < 100) {
                    throw ValidationException::withMessages([
                        'estado' =>
                        'No es posible iniciar la ejecución. '
                            . 'La Acción Correctiva debe tener un avance del 100%.',
                    ]);
                }
            }
            /*
 * ============================================================
 * 8. EJECUCIÓN → VERIFICACIÓN DE CIERRE
 *
 * Debe existir una verificación de cierre aprobada
 * para el ciclo actual.
 * ============================================================
 */
            if (
                $estadoActual === EstadoAccionCorrectiva::EJECUCION->value &&
                $nuevoEstado === EstadoAccionCorrectiva::VERIFICACION_CIERRE
            ) {
                $verificacion = AcVerificacionCierre::query()
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
                        'estado' =>
                        'No es posible pasar a verificación de cierre. '
                            . 'Debe existir una verificación de cierre aprobada '
                            . 'para el ciclo actual.',
                    ]);
                }
            }

            if (
                $estadoActual === EstadoAccionCorrectiva::VERIFICACION_CIERRE->value &&
                $nuevoEstado === EstadoAccionCorrectiva::ESPERA_EFICACIA
            ) {
                $espera = \App\Domains\Incidencias\Models\AcEsperaEficacia::query()
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
                    ->latest('id')
                    ->first();

                if (!$espera) {
                    throw ValidationException::withMessages([
                        'estado' =>
                        'No es posible pasar a espera de eficacia. '
                            . 'Debe existir una espera de eficacia activa '
                            . 'para el ciclo actual.',
                    ]);
                }
            }

            /*
 * ============================================================
 * ESPERA DE EFICACIA → VERIFICACIÓN DE EFICACIA
 *
 * Debe existir una verificación de eficacia registrada
 * para el ciclo actual.
 * ============================================================
 */
            /*
 * ============================================================
 * ESPERA DE EFICACIA → VERIFICACIÓN DE EFICACIA
 *
 * Debe existir una espera activa para el ciclo actual
 * y debe haber llegado la fecha programada.
 * ============================================================
 */
            if (
                $estadoActual === EstadoAccionCorrectiva::ESPERA_EFICACIA->value &&
                $nuevoEstado === EstadoAccionCorrectiva::VERIFICACION_EFICACIA
            ) {
                $espera = \App\Domains\Incidencias\Models\AcEsperaEficacia::query()
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
                    ->latest('id')
                    ->first();

                if (!$espera) {
                    throw ValidationException::withMessages([
                        'estado' =>
                        'No es posible pasar a verificación de eficacia. '
                            . 'Debe existir una espera de eficacia activa '
                            . 'para el ciclo actual.',
                    ]);
                }

                if (now()->startOfDay()->lt($espera->fecha_verificacion)) {
                    throw ValidationException::withMessages([
                        'estado' =>
                        'Aún no es posible realizar la verificación de eficacia. '
                            . 'La fecha programada es '
                            . $espera->fecha_verificacion->format('Y-m-d') . '.',
                    ]);
                }
            }
            /*
 * ============================================================
 * VERIFICACIÓN DE EFICACIA → CERRADA
 *
 * Para cerrar la AC debe existir una verificación de eficacia
 * aprobada para el ciclo actual.
 * ============================================================
 */
            if (
                $estadoActual === EstadoAccionCorrectiva::VERIFICACION_EFICACIA->value &&
                $nuevoEstado === EstadoAccionCorrectiva::CERRADA
            ) {
                $verificacion = AcVerificacionEficacia::query()
                    ->where(
                        'accion_correctiva_id',
                        $accionCorrectiva->id
                    )
                    ->where(
                        'ciclo',
                        $accionCorrectiva->ciclo_actual
                    )
                    ->where(
                        'resultado_eficaz',
                        true
                    )
                    ->latest('id')
                    ->first();

                if (!$verificacion) {
                    throw ValidationException::withMessages([
                        'estado' =>
                        'No es posible cerrar la Acción Correctiva. '
                            . 'Debe existir una verificación de eficacia aprobada '
                            . 'para el ciclo actual.',
                    ]);
                }
            }

            /*
 * ============================================================
 * VERIFICACIÓN DE EFICACIA → ANÁLISIS
 *
 * Si la eficacia fue rechazada, la AC debe regresar
 * a análisis para iniciar un nuevo ciclo.
 * ============================================================
 */
            if (
                $estadoActual === EstadoAccionCorrectiva::VERIFICACION_EFICACIA->value &&
                $nuevoEstado === EstadoAccionCorrectiva::ANALISIS
            ) {
                $verificacion = AcVerificacionEficacia::query()
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

                if (!$verificacion) {
                    throw ValidationException::withMessages([
                        'estado' =>
                        'No es posible regresar al análisis. '
                            . 'Debe existir una verificación de eficacia.',
                    ]);
                }

                if ($verificacion->resultado_eficaz) {
                    throw ValidationException::withMessages([
                        'estado' =>
                        'La Acción Correctiva no puede regresar al análisis '
                            . 'porque la verificación de eficacia fue aprobada.',
                    ]);
                }
            }

            /*
             * ============================================================
             * 9. Obtener estado destino
             * ============================================================
             */
            $estado = EstadoModel::query()
                ->where('codigo', $nuevoEstado->value)
                ->where('activo', true)
                ->first();

            if (!$estado) {
                throw ValidationException::withMessages([
                    'estado' => sprintf(
                        'El estado "%s" no está disponible.',
                        $nuevoEstado->value
                    ),
                ]);
            }

            /*
 * ============================================================
 * 10. Cambiar estado
 * ============================================================
 */

            $datosActualizacion = [
                'estado_id' => $estado->id,
            ];

            /*
 * Si la eficacia fue rechazada y regresamos
 * al análisis, iniciar un nuevo ciclo.
 */
            if (
                $estadoActual === EstadoAccionCorrectiva::VERIFICACION_EFICACIA->value &&
                $nuevoEstado === EstadoAccionCorrectiva::ANALISIS
            ) {
                $datosActualizacion['ciclo_actual'] =
                    $accionCorrectiva->ciclo_actual + 1;

                $datosActualizacion['porcentaje_avance'] = 0;
            }

            /*
 * Si la Acción Correctiva se cierra,
 * registrar automáticamente la fecha de cierre.
 */
            if (
                $nuevoEstado === EstadoAccionCorrectiva::CERRADA
            ) {
                $datosActualizacion['fecha_cierre'] =
                    now()->toDateString();

                $datosActualizacion['porcentaje_avance'] = 100;
            }

            $accionCorrectiva->update($datosActualizacion);

            /*
             * ============================================================
             * 11. Devolver AC actualizada
             * ============================================================
             */
            return $accionCorrectiva->fresh([
                'estado',
                'origen',
                'responsable',
                'contenciones',
                'analisis',
            ]);
        });
    }
}
