<?php

namespace App\Domains\Incidencias\Actions;

use App\Domains\Incidencias\Models\AcContencion;
use App\Domains\Incidencias\Models\AccionCorrectiva;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CrearContencion
{
    public function ejecutar(
        AccionCorrectiva $accionCorrectiva,
        array $datos
    ): AcContencion {
        return DB::transaction(function () use (
            $accionCorrectiva,
            $datos
        ) {

            $accionCorrectiva->loadMissing('estado');

            /*
             * La contención únicamente puede registrarse
             * cuando la AC se encuentra abierta o en contención.
             */
            if (!in_array(
                $accionCorrectiva->estado->codigo,
                ['abierta', 'contencion'],
                true
            )) {
                throw ValidationException::withMessages([
                    'accion_correctiva' =>
                        'No se pueden registrar acciones de contención en la etapa actual de la Acción Correctiva.',
                ]);
            }

            /*
             * Crear la acción de contención.
             */
            return AcContencion::create([
                'accion_correctiva_id' => $accionCorrectiva->id,
                'descripcion' => $datos['descripcion'],
                'responsable_id' => $datos['responsable_id'],
                'fecha_implementacion' => $datos['fecha_implementacion'],
                'estado' => $datos['estado'] ?? 'pendiente',
                'observaciones' => $datos['observaciones'] ?? null,
            ]);
        });
    }
}