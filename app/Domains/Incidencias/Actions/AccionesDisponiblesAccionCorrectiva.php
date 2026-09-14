<?php

namespace App\Domains\Incidencias\Actions;

use App\Domains\Incidencias\Models\AccionCorrectiva;

class AccionesDisponiblesAccionCorrectiva
{
    public function ejecutar(AccionCorrectiva $accionCorrectiva): array
    {
        $accionCorrectiva->loadMissing([
            'estado',
            'planesAccion',
            'analisis.causasRaiz',
            'verificacionesEficacia',
        ]);

        return match ($accionCorrectiva->estado?->codigo) {

            'borrador' => [
                [
                    'tipo' => 'primary',
                    'texto' => 'Abrir acción correctiva',
                    'accion' => 'abrir',
                    'estado_destino' => 'abierta',
                ],
            ],

            'abierta' => [
                [
                    'tipo' => 'primary',
                    'texto' => 'Gestionar contención',
                    'accion' => 'contencion',
                ],
            ],

            'contencion' => [
                [
                    'tipo' => 'primary',
                    'texto' => 'Continuar análisis',
                    'accion' => 'analisis',
                    'estado_destino' => 'analisis',
                ],
            ],

            'analisis' => $this->accionesAnalisis(
                $accionCorrectiva
            ),

            'validacion_causa' => [
                [
                    'tipo' => 'primary',
                    'texto' => 'Continuar a plan de acción',
                    'accion' => 'plan_accion',
                    'estado_destino' => 'plan_accion',
                ],
            ],

            'plan_accion' => $this->accionesPlanAccion(
                $accionCorrectiva
            ),

            'ejecucion' => [
                [
                    'tipo' => 'primary',
                    'texto' => 'Continuar a verificación de cierre',
                    'accion' => 'verificacion_cierre',
                    'estado_destino' => 'verificacion_cierre',
                ],
            ],

            'verificacion_cierre' => [
                [
                    'tipo' => 'primary',
                    'texto' => 'Pasar a espera de eficacia',
                    'accion' => 'espera_eficacia',
                    'estado_destino' => 'espera_eficacia',
                ],
            ],

            'espera_eficacia' => [
                [
                    'tipo' => 'warning',
                    'texto' => 'Pasar a verificación de eficacia',
                    'accion' => 'espera_eficacia',
                    'estado_destino' => 'verificacion_eficacia',
                ],
            ],

            'verificacion_eficacia' => $this->accionesVerificacionEficacia(
                $accionCorrectiva
            ),

            'cerrada' => [],

            default => [],
        };
    }

    private function accionesVerificacionEficacia(
        AccionCorrectiva $accionCorrectiva
    ): array {
        $verificacion = $accionCorrectiva->verificacionesEficacia
            ->where('ciclo', $accionCorrectiva->ciclo_actual)
            ->sortByDesc('id')
            ->first();

        if (!$verificacion) {
            return [];
        }

        if ($verificacion->resultado_eficaz) {
            return [
                [
                    'tipo' => 'success',
                    'texto' => 'Cerrar acción correctiva',
                    'accion' => 'cerrar',
                    'estado_destino' => 'cerrada',
                ],
            ];
        }

        return [
            [
                'tipo' => 'warning',
                'texto' => 'Iniciar nuevo ciclo de análisis',
                'accion' => 'nuevo_ciclo',
                'estado_destino' => 'analisis',
            ],
        ];
    }

    private function accionesAnalisis(
        AccionCorrectiva $accionCorrectiva
    ): array {
        $analisis = $accionCorrectiva->analisis
            ->firstWhere('ciclo', $accionCorrectiva->ciclo_actual);

        if (!$analisis) {
            return [
                [
                    'tipo' => 'primary',
                    'texto' => 'Gestionar análisis',
                    'accion' => 'analisis',
                ],
            ];
        }

        $causaRaizAprobada = $analisis->causasRaiz
            ->where('estado_validacion', 'aprobada')
            ->isNotEmpty();

        if ($causaRaizAprobada) {
            return [
                [
                    'tipo' => 'success',
                    'texto' => 'Continuar a validación de causa',
                    'accion' => 'validacion_causa',
                    'estado_destino' => 'validacion_causa',
                ],
            ];
        }

        return [
            [
                'tipo' => 'primary',
                'texto' => 'Gestionar análisis',
                'accion' => 'analisis',
            ],
        ];
    }

    private function accionesPlanAccion(
        AccionCorrectiva $accionCorrectiva
    ): array {
        $plan = $accionCorrectiva->planesAccion
            ->firstWhere('ciclo', $accionCorrectiva->ciclo_actual);

        if (!$plan) {
            return [
                [
                    'tipo' => 'primary',
                    'texto' => 'Crear plan de acción',
                    'accion' => 'plan_accion',
                    'estado_destino' => 'plan_accion',
                ],
            ];
        }

        if ($plan->estado === 'completado') {
            return [
                [
                    'tipo' => 'primary',
                    'texto' => 'Continuar ejecución',
                    'accion' => 'ejecucion',
                    'estado_destino' => 'ejecucion',
                ],
            ];
        }

        return [
            [
                'tipo' => 'primary',
                'texto' => 'Gestionar plan de acción',
                'accion' => 'plan_accion',
                'estado_destino' => 'plan_accion',
            ],
        ];
    }
}