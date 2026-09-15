<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * El controlador generaba el folio de las Acciones Correctivas calculando
 * MAX(codigo)+1 en PHP, sin tocar `consecutivos_acciones_correctivas`. Al
 * cambiar la creación para usar ese contador (con lockForUpdate, evitando
 * folios duplicados por creaciones concurrentes), el contador quedaba
 * desincronizado y podía repetir un folio que ya existe. Esta migración
 * sincroniza `ultimo_folio` con el máximo folio real ya usado por año.
 */
return new class extends Migration
{
    public function up(): void
    {
        $maximosPorAnio = DB::table('acciones_correctivas')
            ->selectRaw("SUBSTRING(codigo, 1, 2) as anio_corto, MAX(CAST(SUBSTRING(codigo, 4) AS UNSIGNED)) as maximo")
            ->where('codigo', 'like', '__-%')
            ->groupBy('anio_corto')
            ->get();

        foreach ($maximosPorAnio as $fila) {
            $anio = 2000 + (int) $fila->anio_corto;
            $maximo = (int) $fila->maximo;

            $actual = (int) DB::table('consecutivos_acciones_correctivas')
                ->where('anio', $anio)
                ->value('ultimo_folio');

            DB::table('consecutivos_acciones_correctivas')->updateOrInsert(
                ['anio' => $anio],
                [
                    'ultimo_folio' => max($maximo, $actual),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }

    public function down(): void
    {
        // Es una corrección de datos, no de esquema: no hay un "valor anterior"
        // significativo al que revertir.
    }
};
