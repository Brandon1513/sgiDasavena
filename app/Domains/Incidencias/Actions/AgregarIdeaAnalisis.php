<?php

namespace App\Domains\Incidencias\Actions;

use App\Domains\Incidencias\Models\AcAnalisis;
use App\Domains\Incidencias\Models\AcIdea;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AgregarIdeaAnalisis
{
    private const CATEGORIAS_ISHIKAWA = [
        'mano_obra',
        'metodo',
        'maquinaria',
        'materia_prima',
        'medicion',
        'medio_ambiente',
    ];

    public function ejecutar(
        AcAnalisis $analisis,
        array $datos
    ): AcIdea {
        return DB::transaction(function () use (
            $analisis,
            $datos
        ) {

            /*
             * El análisis debe estar en proceso.
             */
            if ($analisis->estado !== 'en_proceso') {
                throw ValidationException::withMessages([
                    'analisis' =>
                        'No se pueden agregar ideas a un análisis que ya fue completado.',
                ]);
            }

            /*
             * Validar categoría 6M.
             */
            if (!in_array(
                $datos['categoria_ishikawa'],
                self::CATEGORIAS_ISHIKAWA,
                true
            )) {
                throw ValidationException::withMessages([
                    'categoria_ishikawa' =>
                        'La categoría seleccionada no pertenece a las 6M de Ishikawa.',
                ]);
            }

            return AcIdea::create([
                'ac_analisis_id' => $analisis->id,
                'descripcion' => $datos['descripcion'],
                'categoria_ishikawa' => $datos['categoria_ishikawa'],
                'es_causa_probable' =>
                    $datos['es_causa_probable'] ?? false,
                'observaciones' =>
                    $datos['observaciones'] ?? null,
            ]);
        });
    }
}