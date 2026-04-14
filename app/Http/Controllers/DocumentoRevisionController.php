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

    public function marcarObsoleto(DocumentoRevision $revision)
    {
        $user = auth()->user();
        abort_unless($user->hasRole('administrador_sgi') || $user->hasRole('administrador'), 403);

        DB::transaction(function () use ($revision) {
            // Marcar la revisión actual como obsoleta
            $revision->update(['estatus' => 'obsoleta']);

            // Si no quedan revisiones vigentes, buscar la última para reactivarla
            if (!DocumentoRevision::where('documento_version_id', $revision->documento_version_id)
                ->where('estatus', 'vigente')
                ->exists()) {

                $ultima = DocumentoRevision::where('documento_version_id', $revision->documento_version_id)
                    ->orderByDesc('id')
                    ->first();

                if ($ultima) {
                    $ultima->update(['estatus' => 'vigente']);
                    
                    // Sincronizar la fecha de la versión con esta revisión reactivada
                    $version = DocumentoVersion::find($revision->documento_version_id);
                    if ($version) {
                        $version->update([
                            'fecha_vencimiento_revision' => $ultima->fecha_vencimiento_revision
                        ]);
                    }
                }
            }
        });

        return back()->with('success', 'Revisión marcada como obsoleta.');
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

            // 1. Marcar todas las revisiones anteriores de esta versión como obsoletas
            DocumentoRevision::where('documento_version_id', $version->id)
                ->where('estatus', 'vigente')
                ->update(['estatus' => 'obsoleta']);

            // 2. Calcular la nueva fecha de vencimiento
            $fecha = Carbon::parse($request->fecha_revision)->startOfDay();
            $vence = $fecha->copy()->addDays((int) $request->vigencia_revision_dias)->toDateString();

            // 3. Crear el registro en la tabla de revisiones (Histórico)
            DocumentoRevision::create([
                'documento_version_id' => $version->id,
                'revision_actual' => $request->revision_actual,
                'revision_anterior' => $request->revision_anterior,
                'fecha_revision' => $fecha->toDateString(),
                'vigencia_revision_dias' => (int) $request->vigencia_revision_dias,
                'fecha_vencimiento_revision' => $vence,
                'liga_archivo' => $request->liga_archivo,
                'lugar_almacenamiento' => $request->lugar_almacenamiento,
                'estatus' => 'vigente',
                'registrado_por' => auth()->id(),
                'registrado_en' => now(),
            ]);

            // 4. ACTUALIZACIÓN CRÍTICA PARA EL CALENDARIO:
            // Sincronizamos los datos de la revisión hacia la tabla padre (DocumentoVersion)
            // porque SolicitudesCalendarController lee de aquí.
            $version->update([
                'fecha_vencimiento_revision' => $vence,
                'lugar_almacenamiento' => $request->lugar_almacenamiento,
                // Si también quieres actualizar la fecha_revision en el padre:
                'fecha_revision' => $fecha->toDateString(),
            ]);
        });

        return redirect()
            ->route('documentos.show', $version->documento_id)
            ->with('success', 'Revisión agregada y calendario actualizado correctamente.');
    }
 
}