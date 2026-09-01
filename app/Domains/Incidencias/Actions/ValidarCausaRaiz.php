<?php

namespace App\Domains\Incidencias\Actions;

use App\Domains\Incidencias\Models\AcCausaRaiz;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ValidarCausaRaiz
{
    public function ejecutar(
        AcCausaRaiz $causaRaiz,
        int $usuarioId,
        bool $aprobada,
        ?string $comentarios = null
    ): AcCausaRaiz {
        return DB::transaction(function () use (
            $causaRaiz,
            $usuarioId,
            $aprobada,
            $comentarios
        ) {

            if ($causaRaiz->estado_validacion !== 'pendiente') {
                throw ValidationException::withMessages([
                    'causa_raiz' =>
                        'Esta causa raíz ya fue validada.',
                ]);
            }

            if (!$comentarios && !$aprobada) {
                throw ValidationException::withMessages([
                    'comentarios' =>
                        'Debe indicar el motivo del rechazo de la causa raíz.',
                ]);
            }

            $causaRaiz->update([
                'estado_validacion' => $aprobada
                    ? 'aprobada'
                    : 'rechazada',

                'validado_por_id' => $usuarioId,

                'fecha_validacion' => now()->toDateString(),

                'comentarios_validacion' => $comentarios,
            ]);

            return $causaRaiz->fresh([
                'propuestaPor',
                'validadoPor',
                'cincoPorques',
            ]);
        });
    }
}