<?php

namespace App\Domains\Incidencias\Actions;

use App\Domains\Incidencias\Models\AcCincoPorque;
use App\Domains\Incidencias\Models\AcAnalisis;
use App\Domains\Incidencias\Models\AcIdea;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class IniciarCincoPorques
{
    public function ejecutar(
        AcAnalisis $analisis,
        AcIdea $idea,
        string $titulo
    ): AcCincoPorque {
        return DB::transaction(function () use (
            $analisis,
            $idea,
            $titulo
        ) {

            /*
             * El análisis debe seguir abierto.
             */
            if ($analisis->estado !== 'en_proceso') {
                throw ValidationException::withMessages([
                    'analisis' =>
                        'No se pueden iniciar nuevos 5 Porqués en un análisis que ya fue completado.',
                ]);
            }

            /*
             * La idea debe pertenecer al análisis.
             */
            if ($idea->ac_analisis_id !== $analisis->id) {
                throw ValidationException::withMessages([
                    'idea' =>
                        'La causa seleccionada no pertenece al análisis actual.',
                ]);
            }

            /*
             * Solamente las ideas marcadas como causas
             * probables pueden alimentar los 5 Porqués.
             */
            if (!$idea->es_causa_probable) {
                throw ValidationException::withMessages([
                    'idea' =>
                        'La idea seleccionada no está marcada como causa probable.',
                ]);
            }

            /*
             * Evitar iniciar dos cadenas para la misma idea.
             */
            if (
                $analisis->cincoPorques()
                    ->where('ac_idea_id', $idea->id)
                    ->exists()
            ) {
                throw ValidationException::withMessages([
                    'idea' =>
                        'Esta causa potencial ya tiene una cadena de 5 Porqués.',
                ]);
            }

            return AcCincoPorque::create([
                'ac_analisis_id' => $analisis->id,
                'ac_idea_id' => $idea->id,
                'titulo' => $titulo,
                'causa_raiz_propuesta' => null,
                'estado' => 'en_proceso',
            ]);
        });
    }
}