<?php

namespace App\Http\Controllers;

use App\Models\DocumentoVersion;
use App\Models\DocumentoRevision;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Models\User;
use App\Mail\DocumentoObsoletoMailable;

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

    if (!$user->hasRole('administrador_sgi') && !$user->hasRole('administrador')) {
        abort(403);
    }

    // 1. marcar como obsoleta
    $revision->update([
        'estatus' => 'obsoleta'
    ]);

    // 2. obtener documento
    $documento = $revision->version?->documento;

    // 🔥 DEBUG (si quieres probar)
    // dd($documento);

    // 3. enviar correo a admins SGI
    if ($documento) {
        try {
            $admins = User::role('administrador_sgi')->get();

            foreach ($admins as $admin) {
                if ($admin->email) {
                    Mail::to($admin->email)->send(
                        new DocumentoObsoletoMailable($documento, $admin)
                    );
                }
            }

        } catch (\Exception $e) {
            \Log::error('Error correo obsoleto: ' . $e->getMessage());
        }
    }

    return back()->with('success', 'Revisión marcada como obsoleta');
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

    public function version()
{
    return $this->belongsTo(DocumentoVersion::class, 'documento_version_id');
}
 
}