<?php

namespace App\Http\Controllers;

use App\Mail\DocumentoNecesitaActualizacionMailable;
use App\Models\Documento;
use App\Models\SolicitudFormato;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Models\DocumentoRevision;
use Illuminate\Support\Facades\DB;



class DocumentoController extends Controller
{
    public function index()
    {
        // Ajusta: si filtras por área del usuario
        $user = auth()->user();

        $docs = Documento::query()
            ->when($user->hasRole('usuario') || $user->hasRole('jefe'), function ($q) use ($user) {
                // Si tu usuario tiene "area"
                if (!empty($user->area)) {
                    $q->where('area', $user->area);
                }
            })
            ->latest('id')
            ->paginate(15);

        return view('documentos.index', compact('docs'));
    }

    public function show(Documento $documento)
    {
        $documento->load([
            'versionVigente',
            'versiones' => fn($q) => $q->orderByDesc('id'),
        ]);

        $versiones = $documento->versiones;
        $vigente   = $documento->versionVigente;
        $user      = auth()->user();

        $usuarios = User::query()
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        $revisiones = DocumentoRevision::query()
            ->whereIn('documento_version_id', $versiones->pluck('id'))
            ->with(['version:id,documento_id,version'])
            ->orderByRaw("CASE WHEN estatus='vigente' THEN 0 ELSE 1 END")
            ->orderByDesc('id')
            ->get();

        $revisionesPorVersion = $revisiones->groupBy(function ($rev) {
            return $rev->version?->version ?? ('Versión ID ' . $rev->documento_version_id);
        });

        return view('documentos.show', compact(
            'documento',
            'user',
            'usuarios',
            'versiones',
            'vigente',
            'revisiones',
            'revisionesPorVersion',
        ));
    }
    // Historial completo (versiones + revisiones)
    public function createUpdateRequest(Request $request, Documento $documento)
    {
        $user = auth()->user();

        // Regla sugerida: solo del mismo "area"
        if (($user->hasRole('usuario') || $user->hasRole('jefe')) && !empty($user->area)) {
            if ($documento->area !== $user->area) {
                abort(403, 'No puedes solicitar actualización de documentos de otra área.');
            }
        }

        // Validación mínima: comentarios obligatorios
        $request->validate([
            'comentarios' => 'required|string|max:2000',
        ]);

        // Crea solicitud “actualizacion” amarrada al mismo código
        $sol = SolicitudFormato::create([
            'user_id' => $user->id,
            'accion' => 'actualizacion',
            'estado' => 'pendiente',
            'jefe_id' => $user->jefe_id,
            'comentarios' => $request->comentarios,

            // ✅ arrastramos identidad
            'codigo_documento' => $documento->codigo,
            'nombre_documento' => $documento->nombre,
            'tipo_documento' => $documento->tipo_documento,
            'formato_el_pa' => $documento->formato_el_pa,
            'lugar_almacenamiento' => $documento->sharepoint_folder, // si aplica
        ]);

        return redirect()
            ->route('solicitudes.show', $sol->id)
            ->with('success', 'Solicitud de actualización creada.');
    }



    //Notificación de actualización (solo admin puede enviar)
    public function notifyNeedsUpdate(Request $request, Documento $documento)
    {
        $user = auth()->user();
        if (!$user->hasRole('administrador_sgi')) {
            abort(403, 'No tienes permiso para notificar.');
        }

        $request->validate([
            'mensaje' => 'required|string|max:1000',
            'destino' => 'required|in:area,usuario,jefe', // 3 modos
            'usuario_id' => 'nullable|integer',
        ]);

        $mensaje = $request->mensaje;

        // Resolver destinatarios
        $emails = collect();

        if ($request->destino === 'area') {
            // todos los usuarios del área/departamento del documento
            if ($documento->area) {
                $emails = User::where('activo', 1)
                    ->where('area', $documento->area)
                    ->whereNotNull('email')
                    ->pluck('email');
            }
        }

        if ($request->destino === 'usuario') {
            $uid = (int) $request->usuario_id;
            $u = User::find($uid);
            if ($u?->email) $emails->push($u->email);
        }

        if ($request->destino === 'jefe') {
            // jefe del área: depende de cómo lo manejes.
            // Opción simple: usuarios del área con role('jefe')
            if ($documento->area) {
                $emails = User::role('jefe')
                    ->where('activo', 1)
                    ->where('area', $documento->area)
                    ->whereNotNull('email')
                    ->pluck('email');
            }
        }

        $emails = $emails->filter()->unique()->values();

        if ($emails->isEmpty()) {
            return back()->withErrors('No se encontraron destinatarios con email para ese criterio.');
        }

        foreach ($emails as $email) {
            Mail::to($email)->send(
                new DocumentoNecesitaActualizacionMailable(
                    $documento,
                    $mensaje,
                    $user->name
                )
            );
        }

        return back()->with('success', 'Notificación enviada correctamente.');
    }

    public function darDeBaja($id)
    {
        $user = auth()->user();

        if (!$user->hasRole('administrador_sgi') && !$user->hasRole('administrador')) {
            abort(403, 'No tienes permiso para dar de baja documentos.');
        }

        DB::transaction(function () use ($id, $user) {

            $doc = Documento::with('versiones')->findOrFail($id);

            // 🔴 Documento
            $doc->update([
                'estatus' => 'obsoleto',
                'fecha_baja' => now(),
                'baja_por' => $user->id,
            ]);

            // 🔴 Versiones
            $doc->versiones()->update([
                'estatus' => 'obsoleto'
            ]);

            // 🔴 Revisiones
            DocumentoRevision::whereIn(
                'documento_version_id',
                $doc->versiones->pluck('id')
            )->update([
                'estatus' => 'obsoleta'
            ]);
        });

        return back()->with('success', 'Documento dado de baja correctamente.');
    }

    public function edit(Documento $documento)
    {

        $user = auth()->User();
        //solo permitir a admin sgi o admin
        if (!$user->hasRole('administrador_sgi') && !$user->hasRole('administrador')) {
            abort(403, 'No tienes permiso para editar documentos.');
        }
        return view('documentos.edit', compact('documento'));
    }
    // Método para procesar los cambios


public function update(Request $request, Documento $documento)
{
    $request->validate([

        // DOCUMENTO
        'codigo' => 'required|string|max:255',
        'nombre' => 'required|string|max:255',

        // OPCIONALES
        'tipo_documento' => 'nullable|string|max:255',
        'formato_el_pa' => 'nullable|string|max:50',
        'area' => 'nullable|string|max:255',

        'sharepoint_folder' => 'nullable|string|max:500',

        'estatus' => 'required|in:vigente,baja',

        // VERSION
        'version' => 'nullable|string|max:100',

        // REVISION
        'revision_actual' => 'nullable|string|max:100',

        // FECHAS
        'fecha_version' => 'nullable|date',
        'fecha_revision' => 'nullable|date',

        // VIGENCIAS
        'vigencia_version_dias' => 'nullable|integer|min:1',
        'vigencia_revision_dias' => 'nullable|integer|min:1',

        // SHAREPOINT
        'sharepoint_path' => 'nullable|string|max:500',
        'sharepoint_file_id' => 'nullable|string|max:255',

        'sp_drive_id' => 'nullable|string|max:255',
        'sp_item_id' => 'nullable|string|max:255',
        'sp_web_url' => 'nullable|string|max:1000',
        'sp_folder_path' => 'nullable|string|max:500',

        // OTROS
        'liga_archivo' => 'nullable|string|max:1000',
        'lugar_almacenamiento' => 'nullable|string|max:255',

        'observaciones_sgi' => 'nullable|string',
    ]);

    DB::transaction(function () use ($request, $documento) {

        // =========================================
        // ACTUALIZAR DOCUMENTO MAESTRO
        // =========================================

        $documento->update([

            'codigo' => $request->codigo,

            'nombre' => $request->nombre,

            'tipo_documento' => $request->tipo_documento,

            'formato_el_pa' => $request->formato_el_pa,

            'area' => $request->area,

            'sharepoint_folder' => $request->sharepoint_folder,

            'estatus' => $request->estatus,
        ]);

        // =========================================
        // OBTENER VERSION VIGENTE
        // =========================================

        $version = $documento->versionVigente;

        // fallback por si no existe relación
        if (!$version) {

            $version = $documento->versiones()
                ->latest('id')
                ->first();
        }

        // =========================================
        // ACTUALIZAR VERSION
        // =========================================

        if ($version) {

            // =====================================
            // FECHAS BASE
            // =====================================

            $fechaVersion = $request->fecha_version
                ? \Carbon\Carbon::parse($request->fecha_version)
                    ->startOfDay()
                : now()->startOfDay();

            $fechaRevision = $request->fecha_revision
                ? \Carbon\Carbon::parse($request->fecha_revision)
                    ->startOfDay()
                : now()->startOfDay();

            // =====================================
            // RECALCULAR VENCIMIENTOS
            // =====================================

            $fechaVencimientoVersion = null;

            if ($request->vigencia_version_dias) {

                $fechaVencimientoVersion = $fechaVersion
                    ->copy()
                    ->addDays(
                        (int)$request->vigencia_version_dias
                    );
            }

            $fechaVencimientoRevision = null;

            if ($request->vigencia_revision_dias) {

                $fechaVencimientoRevision = $fechaRevision
                    ->copy()
                    ->addDays(
                        (int)$request->vigencia_revision_dias
                    );
            }

            // =====================================
            // ACTUALIZAR documento_versiones
            // =====================================

            $version->update([

                // VERSIONADO
               'version' => $request->version ?? $version->version,

                'revision_actual' => $request->revision_actual ?? $version->revision_actual,

                // FECHAS
                'fecha_version' => $fechaVersion,

                'fecha_revision' => $fechaRevision,

                // VIGENCIAS
                'vigencia_version_dias' => $request->vigencia_version_dias,

                'vigencia_revision_dias' => $request->vigencia_revision_dias,

                // VENCIMIENTOS
                'fecha_vencimiento_version'
                    => $fechaVencimientoVersion,

                'fecha_vencimiento_revision'
                    => $fechaVencimientoRevision,

                // ARCHIVOS
                'liga_archivo' => $request->liga_archivo,

                'lugar_almacenamiento'
                    => $request->lugar_almacenamiento,

                // SHAREPOINT
                'sharepoint_path'
                    => $request->sharepoint_path,

                'sharepoint_file_id'
                    => $request->sharepoint_file_id,

                'sp_drive_id'
                    => $request->sp_drive_id,

                'sp_item_id'
                    => $request->sp_item_id,

                'sp_web_url'
                    => $request->sp_web_url,

                'sp_folder_path'
                    => $request->sp_folder_path,

                // SGI
                'observaciones_sgi'
                    => $request->observaciones_sgi,
            ]);

            // =====================================
            // ASEGURAR VERSION VIGENTE
            // =====================================

            $documento->update([
                'version_vigente_id' => $version->id
            ]);
        }
    });

    return redirect()
        ->route('documentos.show', $documento->id)
        ->with(
            'success',
            'Documento y versión actualizados correctamente.'
        );
}



    
}
