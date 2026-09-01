<?php

namespace App\Domains\Incidencias\Actions;

use App\Domains\Incidencias\Models\AccionCorrectiva;
use App\Domains\Incidencias\Models\EstadoAccionCorrectiva;
use App\Domains\Incidencias\Services\GenerarFolioAccionCorrectiva;
use Illuminate\Support\Facades\DB;

class CrearAccionCorrectiva
{
    public function __construct(
        private GenerarFolioAccionCorrectiva $generarFolio
    ) {
    }

    /**
     * Crea una Acción Correctiva completa.
     *
     * @param array{
     *     fecha_apertura:string,
     *     origen_id:int,
     *     responsable_id:int,
     *     descripcion:string
     * } $datos
     */
    public function ejecutar(array $datos): AccionCorrectiva
    {
        return DB::transaction(function () use ($datos) {

            $estadoInicial = EstadoAccionCorrectiva::query()
                ->where('codigo', 'borrador')
                ->where('activo', true)
                ->firstOrFail();

            $fechaApertura = $datos['fecha_apertura'];

            $anio = (int) date('Y', strtotime($fechaApertura));

            $codigo = $this->generarFolio->generar($anio);

            return AccionCorrectiva::create([
                'codigo' => $codigo,
                'fecha_apertura' => $fechaApertura,
                'origen_id' => $datos['origen_id'],
                'responsable_id' => $datos['responsable_id'],
                'estado_id' => $estadoInicial->id,
                'descripcion' => $datos['descripcion'],
                'porcentaje_avance' => 0,
                'fecha_cierre' => null,
                'ciclo_actual' => 1,
            ]);
        });
    }
}