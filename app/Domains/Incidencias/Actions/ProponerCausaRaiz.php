<?php

namespace App\Domains\Incidencias\Actions;

use App\Domains\Incidencias\Models\AcAnalisis;
use App\Domains\Incidencias\Models\AcCausaRaiz;
use App\Domains\Incidencias\Models\AcCincoPorque;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProponerCausaRaiz
{
    public function ejecutar(
        AcAnalisis $analisis,
        AcCincoPorque $cincoPorques,
        string $descripcion,
        int $usuarioId
    ): AcCausaRaiz {
        return DB::transaction(function () use (
            $analisis,
            $cincoPorques,
            $descripcion,
            $usuarioId
        ) {

            if ($analisis->estado !== 'en_proceso') {
                throw ValidationException::withMessages([
                    'analisis' =>
                        'El análisis ya no se encuentra en proceso.',
                ]);
            }

            if ($cincoPorques->ac_analisis_id !== $analisis->id) {
                throw ValidationException::withMessages([
                    'cinco_porque' =>
                        'La cadena de 5 Porqués no pertenece al análisis actual.',
                ]);
            }

            $cincoPorques->load('pasos');

            if ($cincoPorques->pasos->count() !== 5) {
                throw ValidationException::withMessages([
                    'cinco_porque' =>
                        'La cadena de 5 Porqués debe contener exactamente 5 pasos antes de proponer una causa raíz.',
                ]);
            }

            if ($cincoPorques->causaRaiz()->exists()) {
                throw ValidationException::withMessages([
                    'causa_raiz' =>
                        'Esta cadena de 5 Porqués ya tiene una causa raíz propuesta.',
                ]);
            }

            return AcCausaRaiz::create([
                'ac_analisis_id' => $analisis->id,
                'ac_cinco_porque_id' => $cincoPorques->id,
                'descripcion' => $descripcion,
                'propuesta_por_id' => $usuarioId,
                'fecha_propuesta' => now()->toDateString(),
                'estado_validacion' => 'pendiente',
            ]);
        });
    }
}