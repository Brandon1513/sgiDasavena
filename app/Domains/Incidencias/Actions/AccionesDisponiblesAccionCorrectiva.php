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
                    'estado_destino' => 'abierta',
                ],
            ],

            'abierta' => [
                [
                    'tipo' => 'primary',
                    'texto' => 'Gestionar contención',
                    'accion' => 'contencion',
                    'estado_destino' => 'contencion',
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

            'analisis' => [
                [
                    'tipo' => 'primary',
                    'texto' => 'Continuar análisis',
                    'accion' => 'analisis',
                    'estado_destino' => 'analisis',
                ],
            ],

            'validacion_causa' => [
                [
                    'tipo' => 'primary',
                    'texto' => 'Continuar al plan de acción',
                    'accion' => 'validar_causa',
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

            'verificacion_eficacia' => [
                [
                    'tipo' => 'primary',
                    'texto' => 'Continuar verificación de eficacia',
                    'accion' => 'verificacion_eficacia',
                    'estado_destino' => 'verificacion_eficacia',
                ],
            ],

            'cerrada' => [],

            'rechazada' => [
                [
                    'tipo' => 'danger',
                    'texto' => 'Revisar acción correctiva',
                    'accion' => 'revisar',
                    'estado_destino' => 'analisis',
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
