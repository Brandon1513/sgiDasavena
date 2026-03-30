<?php 
namespace App\Imports;

use App\Models\Documento;
use App\Models\DocumentoVersion;
use App\Models\DocumentoRevision;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Carbon\Carbon;

class DocumentosImport implements ToCollection
{
    public function collection(Collection $rows)
    {
        $rows->shift(); // quitar encabezados

        foreach ($rows as $row) {

            $codigo   = trim($row[0] ?? '');
            $nombre   = trim($row[1] ?? '');
            $revision = trim($row[2] ?? '0');
            $version  = trim($row[3] ?? '1');
            $fecha    = $row[6] ?? null;
            $area     = trim($row[10] ?? null);
            $liga     = trim($row[12] ?? null);

            if (!$codigo) continue;

            // 🔹 DOCUMENTO
            $doc = Documento::firstOrCreate(
                ['codigo' => $codigo],
                [
                    'nombre' => $nombre,
                    'area'   => $area,
                    'liga'   => $liga // opcional si existe campo
                ]
            );

            // 🔹 VERSION
            $ver = DocumentoVersion::firstOrCreate(
                [
                    'documento_id' => $doc->id,
                    'version' => $version
                ],
                [
                    'estatus' => 'vigente'
                ]
            );

            // 🔹 REVISION (LA IMPORTANTE 🔥)
            DocumentoRevision::create([
                'documento_version_id' => $ver->id,
                'revision_actual' => $revision,
                'fecha_vencimiento_revision' => $this->parseFecha($fecha),
                'estatus' => 'vigente'
            ]);
        }
    }

    private function parseFecha($fecha)
    {
        if (!$fecha) return null;

        try {
            return Carbon::parse($fecha);
        } catch (\Exception $e) {
            return null;
        }
    }
}