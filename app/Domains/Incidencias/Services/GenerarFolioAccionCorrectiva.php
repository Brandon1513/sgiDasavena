<?php

namespace App\Domains\Incidencias\Services;

use App\Domains\Incidencias\Models\ConsecutivoAccionCorrectiva;
use Illuminate\Support\Facades\DB;

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
        // INSERT IGNORE: si dos altas simultáneas son el primer folio del
        // año, solo una crea la fila; la otra no truena por la unicidad de
        // "anio" y ambas terminan compitiendo por el lockForUpdate de abajo.
        DB::table('consecutivos_acciones_correctivas')->insertOrIgnore([
            'anio' => $anio,
            'ultimo_folio' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $consecutivo = ConsecutivoAccionCorrectiva::query()
            ->where('anio', $anio)
            ->lockForUpdate()
            ->firstOrFail();

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