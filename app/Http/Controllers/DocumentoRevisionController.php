<?php
namespace App\Http\Controllers;

use App\Models\DocumentoVersion;
use App\Models\DocumentoRevision;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DocumentoRevisionController extends Controller
{
    public function create(DocumentoVersion $version)
    {
        $version->load('documento', 'ultimaRevision');
        return view('documentos.revisiones.create', compact('version'));
    }

    public function store(Request $request, DocumentoVersion $version)
    {
        $request->validate([
            'revision_actual' => 'required|string|max:50',
            'revision_anterior' => 'nullable|string|max:50',
            'fecha_revision' => 'required|date',
            'vigencia_revision_dias' => 'required|integer|min:1',
            'liga_archivo' => 'nullable|url',
            'lugar_almacenamiento' => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($request, $version) {

            // 1) Obsoletar revisión vigente anterior (si existe)
            DocumentoRevision::where('documento_version_id', $version->id)
                ->where('estatus', 'vigente')
                ->update(['estatus' => 'obsoleta']);

            // 2) Calcular vencimiento
            $fecha = Carbon::parse($request->fecha_revision)->startOfDay();
            $vence = $fecha->copy()->addDays((int)$request->vigencia_revision_dias)->toDateString();

            // 3) Crear nueva revisión vigente
            DocumentoRevision::create([
                'documento_version_id' => $version->id,
                'revision_actual' => $request->revision_actual,
                'revision_anterior' => $request->revision_anterior,
                'fecha_revision' => $fecha->toDateString(),
                'vigencia_revision_dias' => (int)$request->vigencia_revision_dias,
                'fecha_vencimiento_revision' => $vence,

                'liga_archivo' => $request->liga_archivo,
                'lugar_almacenamiento' => $request->lugar_almacenamiento,

                'estatus' => 'vigente',
                'registrado_por' => auth()->id(),
                'registrado_en' => now(),
            ]);
        });

        return redirect()
            ->route('documentos.show', $version->documento_id)
            ->with('success', 'Revisión agregada al histórico.');
    }
}
