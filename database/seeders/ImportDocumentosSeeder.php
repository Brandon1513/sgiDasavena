<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ImportDocumentosSeeder extends Seeder
{
    public function run()
    {
        $path = storage_path('app/documentos.csv');

        if (!file_exists($path)) {
            dd('Archivo no encontrado');
        }

        $file = fopen($path, 'r');
        fgetcsv($file, 1000, ',');

        while (($row = fgetcsv($file, 1000, ',')) !== false) {

            if (count($row) < 18) continue;

            [
                $codigo,
                $nombre,
                $revision,
                $fecha_revision,
                $dias_rev_1,
                $fecha_venc_revision,
                $dias_rev_2,
                $version,
                $fecha_revision_version,
                $dias_version,
                $fecha_venc_version,
                $vigencia_version,
                $tipo,
                $formato_el_pa,
                $area,
                $nombre_cordinador,
                $liga,
                $almacenamiento
            ] = $row;

            // ============================
            // 🔧 HELPERS
            // ============================

            $parseFecha = function ($fecha) {

                if (!$fecha || $fecha === '#¡VALOR!') return null;

                $meses = [
                    'ene'=>'Jan','feb'=>'Feb','mar'=>'Mar','abr'=>'Apr','may'=>'May',
                    'jun'=>'Jun','jul'=>'Jul','ago'=>'Aug','sep'=>'Sep','oct'=>'Oct',
                    'nov'=>'Nov','dic'=>'Dec',
                ];

                $fecha = strtolower($fecha);

                foreach ($meses as $es => $en) {
                    $fecha = str_replace($es, strtolower($en), $fecha);
                }

                $fecha = ucfirst($fecha);

                try {
                    return Carbon::parse($fecha);
                } catch (\Exception $e) {
                    try {
                        return Carbon::createFromFormat('d-M-y', $fecha);
                    } catch (\Exception $e) {
                        try {
                            return Carbon::createFromFormat('m/d/Y', $fecha);
                        } catch (\Exception $e) {
                            return null;
                        }
                    }
                }
            };

            $int = fn($v) => is_numeric($v) ? (int)$v : 0;

            // ============================
            // 🛡️ VALIDACIÓN DE USUARIO (CORRECCIÓN)
            // ============================
            // Verificamos si es un número y si el ID existe en la tabla users
            $usuarioValido = is_numeric($nombre_cordinador) 
                ? DB::table('users')->where('id', $nombre_cordinador)->exists() 
                : false;

            // ============================
            // 📄 DOCUMENTO
            // ============================

            $documentoId = DB::table('documentos')->updateOrInsert(
                ['codigo' => $codigo],
                [
                    'nombre' => $nombre,
                    'tipo_documento' => $tipo,
                    'formato_el_pa' => $formato_el_pa,
                    'area' => $area,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );

            // 🔥 obtener ID real
            $documento = DB::table('documentos')->where('codigo', $codigo)->first();

            // ============================
            // 📦 VERSION
            // ============================

            DB::table('documento_versiones')->updateOrInsert(
                [
                    'documento_id' => $documento->id,
                    'version' => $version,
                ],
                [
                    'revision_actual' => $int($revision),
                    'fecha_version' => $parseFecha($fecha_revision_version),
                    'fecha_vencimiento_version' => $parseFecha($fecha_venc_version),
                    'fecha_vencimiento_revision' => $parseFecha($fecha_venc_revision),
                    'vigencia_version_dias' => max(0, $int($dias_version)),
                    'estatus' => 'vigente',
                    'liga_archivo' => $liga ?: null,
                    'lugar_almacenamiento' => $almacenamiento ?: null,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );

            $versionRow = DB::table('documento_versiones')
                ->where('documento_id', $documento->id)
                ->where('version', $version)
                ->first();

            // ============================
            // 🔁 REVISION
            // ============================

            DB::table('documento_revisiones')->updateOrInsert(
                [
                    'documento_version_id' => $versionRow->id,
                ],
                [
                    'revision_actual' => $int($revision),
                    'revision_anterior' => null,
                    'fecha_revision' => $parseFecha($fecha_revision),
                    // Agregamos max(0, ...) para evitar el error de rango numérico negativo
                    'vigencia_revision_dias' => max(0, $int($dias_rev_2) ?: 730),
                    'fecha_vencimiento_revision' => $parseFecha($fecha_venc_revision),
                    'liga_archivo' => $liga ?: null,
                    'lugar_almacenamiento' => $almacenamiento ?: null,
                    // Si el usuario no existe, mandamos NULL de verdad
                    'registrado_por' => $usuarioValido ? $nombre_cordinador : null,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }

        fclose($file);

        echo "✅ Datos actualizados correctamente (sin duplicados)";
    }
}