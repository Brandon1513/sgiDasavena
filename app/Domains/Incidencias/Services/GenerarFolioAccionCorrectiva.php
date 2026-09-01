<?php

namespace App\Domains\Incidencias\Services;

use App\Domains\Incidencias\Models\ConsecutivoAccionCorrectiva;

class GenerarFolioAccionCorrectiva
{
    /**
     * Genera el siguiente folio de Acción Correctiva.
     *
     * La transacción debe ser controlada por el proceso
     * que está creando la Acción Correctiva.
     */
    public function generar(int $anio): string
    {
        $consecutivo = ConsecutivoAccionCorrectiva::query()
            ->where('anio', $anio)
            ->lockForUpdate()
            ->first();

        if (!$consecutivo) {
            $consecutivo = ConsecutivoAccionCorrectiva::create([
                'anio' => $anio,
                'ultimo_folio' => 0,
            ]);
        }

        $consecutivo->increment('ultimo_folio');

        $folio = $consecutivo->fresh()->ultimo_folio;

        $anioCorto = substr((string) $anio, -2);

        return sprintf(
            '%s-%03d',
            $anioCorto,
            $folio
        );
    }
}