<?php

namespace Database\Seeders;

use App\Domains\Incidencias\Models\EstadoAccionCorrectiva;
use Illuminate\Database\Seeder;

class EstadoAccionCorrectivaSeeder extends Seeder
{
    public function run(): void
    {
        $estados = [
            ['codigo' => 'borrador', 'nombre' => 'Borrador', 'orden' => 1],
            ['codigo' => 'abierta', 'nombre' => 'Abierta', 'orden' => 2],
            ['codigo' => 'contencion', 'nombre' => 'Contención', 'orden' => 3],
            ['codigo' => 'analisis', 'nombre' => 'Análisis de causa raíz', 'orden' => 4],
            ['codigo' => 'validacion_causa', 'nombre' => 'Validación de causa raíz', 'orden' => 5],
            ['codigo' => 'plan_accion', 'nombre' => 'Plan de acción', 'orden' => 6],
            ['codigo' => 'ejecucion', 'nombre' => 'Ejecución', 'orden' => 7],
            ['codigo' => 'verificacion_cierre', 'nombre' => 'Verificación de cierre', 'orden' => 8],
            ['codigo' => 'espera_eficacia', 'nombre' => 'Espera de verificación de eficacia', 'orden' => 9],
            ['codigo' => 'verificacion_eficacia', 'nombre' => 'Verificación de eficacia', 'orden' => 10],
            ['codigo' => 'cerrada', 'nombre' => 'Cerrada', 'orden' => 11],
            ['codigo' => 'rechazada', 'nombre' => 'Rechazada', 'orden' => 12],
        ];

        foreach ($estados as $estado) {
            EstadoAccionCorrectiva::updateOrCreate(
                ['codigo' => $estado['codigo']],
                [
                    'nombre' => $estado['nombre'],
                    'orden' => $estado['orden'],
                    'activo' => true,
                ]
            );
        }
    }
}