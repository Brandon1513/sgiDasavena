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
            dd('Archivo no encontrado en storage/app/documentos.csv');
        }

        $file = fopen($path, 'r');

        // Saltar encabezado
        fgetcsv($file);

        while (($row = fgetcsv($file)) !== false) {

            [
                $codigo,
                $nombre,
                $revision,
                $version,
                $fecha_version,
                $dias_vencer,
                $fecha_vencimiento,
                $vigencia,
                $tipo,
                $formato,
                $area,
                $coordinador,
                $liga,
                $almacenamiento
            ] = $row;

            //  FECHAS (excel o texto)
            $fecha_version = $this->parseExcelDate($fecha_version);
            $fecha_vencimiento = $this->parseExcelDate($fecha_vencimiento);

            //  REGLA DE NEGOCIO: TODO ENTRA COMO VIGENTE
            $estatus = 'vigente';

            //  DOCUMENTO
            $documento = DB::table('documentos')
                ->where('codigo', $codigo)
                ->first();

            if ($documento) {
                $documentoId = $documento->id;
            } else {
                $documentoId = DB::table('documentos')->insertGetId([
                    'codigo' => $codigo,
                    'nombre' => $nombre,
                    'tipo_documento' => $tipo,
                    'formato_el_pa' => $formato,
                    'area' => $area,
                    'estatus' => 'vigente',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
    
            //  VERSION
            $versionExistente = DB::table('documento_versiones')
                ->where('documento_id', $documentoId)
                ->where('version', $version)
                ->where('revision_actual', $revision)
                ->first();

            if ($versionExistente) {
                $versionId = $versionExistente->id;
            } else {

                $versionId = DB::table('documento_versiones')->insertGetId([
                    'documento_id' => $documentoId,
                    'version' => $version,
                    'revision_actual' => $revision,
                    'fecha_version' => $fecha_version,
                    'fecha_vencimiento_version' => $fecha_vencimiento,
                    'vigencia_version_dias' => max(0, (int)$vigencia), //  evita error negativos
                    'estatus' => $estatus,
                    'liga_archivo' => $liga,
                    'lugar_almacenamiento' => $almacenamiento,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            //  ACTUALIZAR VERSION VIGENTE
            DB::table('documentos')
                ->where('id', $documentoId)
                ->update([
                    'version_vigente_id' => $versionId
                ]);

            //  REVISION
            $existeRevision = DB::table('documento_revisiones')
                ->where('documento_version_id', $versionId)
                ->where('revision_actual', $revision)
                ->exists();

            if (!$existeRevision) {

                DB::table('documento_revisiones')->insert([
                    'documento_version_id' => $versionId,
                    'revision_actual' => $revision,
                    'fecha_revision' => $fecha_version,
                    'vigencia_revision_dias' => max(0, (int)$vigencia),
                    'fecha_vencimiento_revision' => $fecha_vencimiento,
                    'liga_archivo' => $liga,
                    'lugar_almacenamiento' => $almacenamiento,
                    'estatus' => 'vigente',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        fclose($file);

        echo " IMPORTACIÓN COMPLETA (TODO VIGENTE todo como debe de sin buscar las 5 saquen las horas extras) 🔥";
    }

    /**
     * Convertir fechas Excel o texto
     */
    private function parseExcelDate($value)
    {
        if (is_numeric($value)) {
            return Carbon::createFromDate(1900, 1, 1)->addDays($value - 2);
        }

        return $value ? Carbon::parse($value) : null;
    }
}