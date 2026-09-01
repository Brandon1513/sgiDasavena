<?php

namespace App\Domains\Incidencias\Actions;

use App\Domains\Incidencias\Models\AcActividad;
use App\Domains\Incidencias\Models\AcPlanAccion;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AgregarActividadPlan
{
    public function ejecutar(
        AcPlanAccion $plan,
        array $datos
    ): AcActividad {
        return DB::transaction(function () use (
            $plan,
            $datos
        ) {

            if ($plan->estado !== 'en_proceso') {
                throw ValidationException::withMessages([
                    'plan' =>
                        'No se pueden agregar actividades a un plan cerrado.',
                ]);
            }

            if (empty($datos['descripcion'])) {
                throw ValidationException::withMessages([
                    'descripcion' =>
                        'La descripción de la actividad es obligatoria.',
                ]);
            }

            if (empty($datos['responsable_id'])) {
                throw ValidationException::withMessages([
                    'responsable_id' =>
                        'Debe asignarse un responsable.',
                ]);
            }

            if (empty($datos['fecha_compromiso'])) {
                throw ValidationException::withMessages([
                    'fecha_compromiso' =>
                        'La fecha de compromiso es obligatoria.',
                ]);
            }

            return AcActividad::create([
                'ac_plan_accion_id' => $plan->id,
                'descripcion' => $datos['descripcion'],
                'responsable_id' => $datos['responsable_id'],
                'fecha_compromiso' => $datos['fecha_compromiso'],
                'estado' => 'pendiente',
                'observaciones' => $datos['observaciones'] ?? null,
            ]);
        });
    }
}