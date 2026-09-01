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
        ]);

        return match ($accionCorrectiva->estado?->codigo) {

            'borrador' => [
                [
                    'tipo' => 'primary',
                    'texto' => 'Abrir acción correctiva',
                    'accion' => 'abrir',
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
                ],
            ],

            'analisis' => [
                [
                    'tipo' => 'primary',
                    'texto' => 'Continuar análisis',
                    'accion' => 'analisis',
                ],
            ],

            'validacion_causa' => [
                [
                    'tipo' => 'primary',
                    'texto' => 'Validar causa raíz',
                    'accion' => 'validar_causa',
                ],
            ],

            'plan_accion' => $this->accionesPlanAccion(
                $accionCorrectiva
            ),

            'ejecucion' => [
                [
                    'tipo' => 'primary',
                    'texto' => 'Gestionar actividades',
                    'accion' => 'ejecucion',
                ],
            ],

            'verificacion_cierre' => [
                [
                    'tipo' => 'primary',
                    'texto' => 'Registrar verificación de cierre',
                    'accion' => 'verificacion_cierre',
                ],
            ],

            'espera_eficacia' => [
                [
                    'tipo' => 'warning',
                    'texto' => 'Verificar eficacia',
                    'accion' => 'espera_eficacia',
                ],
            ],

            'verificacion_eficacia' => [
                [
                    'tipo' => 'primary',
                    'texto' => 'Registrar verificación de eficacia',
                    'accion' => 'verificacion_eficacia',
                ],
            ],

            'cerrada' => [],

            'rechazada' => [
                [
                    'tipo' => 'danger',
                    'texto' => 'Revisar acción correctiva',
                    'accion' => 'revisar',
                ],
            ],

            default => [],
        };
    }

    private function accionesPlanAccion(
        AccionCorrectiva $accionCorrectiva
    ): array {
        $plan = $accionCorrectiva->planesAccion
            ->firstWhere('ciclo', $accionCorrectiva->ciclo_actual);

        /*
         * Todavía no existe un plan para el ciclo actual.
         */
        if (!$plan) {
            return [
                [
                    'tipo' => 'primary',
                    'texto' => 'Crear plan de acción',
                    'accion' => 'plan_accion',
                ],
            ];
        }

        /*
         * El plan ya fue completado.
         * La AC debe avanzar a ejecución.
         */
        if ($plan->estado === 'completado') {
            return [
                [
                    'tipo' => 'primary',
                    'texto' => 'Continuar ejecución',
                    'accion' => 'ejecucion',
                ],
            ];
        }

        /*
         * El plan todavía está en proceso.
         */
        return [
            [
                'tipo' => 'primary',
                'texto' => 'Gestionar plan de acción',
                'accion' => 'plan_accion',
            ],
        ];
    }
}