<?php

namespace App\Http\Controllers;

use App\Mail\DocumentoNecesitaActualizacionMailable;
use App\Models\Documento;
use App\Models\DocumentoRevision;
use App\Models\SolicitudFormato;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class DocumentoController extends Controller
{
    /**
     * Regla de acceso:
     * - administrador_sgi ve todos los documentos (evalúa y verifica).
     * - Cualquier otro rol (usuario, jefe, gerencia, administrador) solo ve
     *   los documentos de su propia área.
     * - Sin área asignada no ve ningún documento.
     *
     * La comparación vive en User::puedeVerArea() y en el scope
     * Documento::visiblePara() para que listado, detalle y calendario
     * apliquen exactamente la misma regla.
     */
    public function index()
    {
        $user = auth()->user();

        $docs = Documento::query()
            ->visiblePara($user)
            ->with(['versionVigente', 'versiones.revisiones'])
            ->latest('id')
            ->paginate(15);

        return view('documentos.index', compact('docs'));
    }

    public function show(Documento $documento)
    {
        $user = auth()->user();

        abort_unless(
            $user->puedeVerArea($documento->area),
            403,
            'No tienes permiso para ver documentos de otra área.'
        );

        $documento->load([
            'versionVigente',
            'versiones' => fn ($q) => $q->orderByDesc('id'),
        ]);

        $versiones = $documento->versiones;
        $vigente   = $documento->versionVigente;

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

    // Solicitud de actualización de un documento existente
    public function createUpdateRequest(Request $request, Documento $documento)
    {
        $user = auth()->user();

        abort_unless(
            $user->puedeVerArea($documento->area),
            403,
            'No puedes solicitar actualización de documentos de otra área.'
        );

        // Validación mínima: comentarios obligatorios
        $request->validate([
            'comentarios' => 'required|string|max:2000',
        ]);

        // Crea solicitud "actualizacion" amarrada al mismo código
        $sol = SolicitudFormato::create([
            'user_id'     => $user->id,
            'accion'      => 'actualizacion',
            'estado'      => 'pendiente',
            'jefe_id'     => $user->jefe_id,
            'comentarios' => $request->comentarios,

            // Identidad del documento
            'codigo_documento'     => $documento->codigo,
            'nombre_documento'     => $documento->nombre,
            'tipo_documento'       => $documento->tipo_documento,
            'formato_el_pa'        => $documento->formato_el_pa,
            'lugar_almacenamiento' => $documento->sharepoint_folder, // si aplica
        ]);

        return redirect()
            ->route('solicitudes.show', $sol->id)
            ->with('success', 'Solicitud de actualización creada.');
    }

    // Notificación de actualización (solo admin SGI puede enviar)
    public function notifyNeedsUpdate(Request $request, Documento $documento)
    {
        $user = auth()->user();

        if (! $user->hasRole('administrador_sgi')) {
            abort(403, 'No tienes permiso para notificar.');
        }

        $request->validate([
            'mensaje'    => 'required|string|max:1000',
            'destino'    => 'required|in:area,usuario,jefe', // 3 modos
            'usuario_id' => 'nullable|integer',
        ]);

        $mensaje = $request->mensaje;

        // Resolver destinatarios
        $emails = collect();

        if ($request->destino === 'area') {
            // Todos los usuarios activos del área del documento
            if ($documento->area) {
                $emails = User::where('activo', 1)
                    ->where('area', $documento->area)
                    ->whereNotNull('email')
                    ->pluck('email');
            }
        }

        if ($request->destino === 'usuario') {
            $u = User::find((int) $request->usuario_id);
            if ($u?->email) {
                $emails->push($u->email);
            }
        }

        if ($request->destino === 'jefe') {
            // Usuarios del área con rol jefe. whereHas no truena si el rol
            // no existe (User::role() lanza RoleDoesNotExist).
            if ($documento->area) {
                $emails = User::whereHas('roles', fn ($q) => $q->where('name', 'jefe'))
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
            try {
                Mail::to($email)->send(
                    new DocumentoNecesitaActualizacionMailable(
                        $documento,
                        $mensaje,
                        $user->name
                    )
                );
            } catch (\Throwable $e) {
                Log::error('No se pudo enviar la notificación de actualización de documento.', [
                    'documento_id' => $documento->id,
                    'email'        => $email,
                    'error'        => $e->getMessage(),
                ]);
            }
        }

        return back()->with('success', 'Notificación enviada correctamente.');
    }

    public function darDeBaja($id)
    {
        $user = auth()->user();

        if (! $user->hasAnyRole(['administrador_sgi', 'administrador'])) {
            abort(403, 'No tienes permiso para dar de baja documentos.');
        }

        $doc = DB::transaction(function () use ($id) {
            $doc = Documento::with('versiones')->findOrFail($id);

            // Documento
            $doc->update(['estatus' => 'baja']);

            // Versiones
            $doc->versiones()->update(['estatus' => 'obsoleto']);

            // Revisiones
            DocumentoRevision::whereIn('documento_version_id', $doc->versiones->pluck('id'))
                ->update(['estatus' => 'obsoleta']);

            return $doc;
        });

        // Las versiones con archivo ya publicado en SharePoint se mueven a
        // Obsoletos (en segundo plano, no bloquea la respuesta).
        foreach ($doc->versiones as $version) {
            if ($version->sp_item_id) {
                \App\Jobs\MoverVersionASharePointObsoletos::dispatch($doc->id, $version->id)->afterCommit();
            }
        }

        return back()->with('success', 'Documento dado de baja correctamente.');
    }

    public function edit(Documento $documento)
    {
        $user = auth()->user();

        // Solo admin SGI o admin
        if (! $user->hasAnyRole(['administrador_sgi', 'administrador'])) {
            abort(403, 'No tienes permiso para editar documentos.');
        }

        if ($documento->estatus === 'baja') {
            abort(403, 'No se pueden editar los datos oficiales de un documento dado de baja.');
        }

        return view('documentos.edit', compact('documento'));
    }

    // Procesa los cambios del formulario de edición
    public function update(Request $request, Documento $documento)
    {
        // Misma regla que edit(): antes update() no validaba el rol y se podía
        // llamar directo aunque la pantalla de edición estuviera bloqueada.
        $user = auth()->user();

        if (! $user->hasAnyRole(['administrador_sgi', 'administrador'])) {
            abort(403, 'No tienes permiso para editar documentos.');
        }

        $request->validate([
            // DOCUMENTO
            'codigo' => 'required|string|max:255',
            'nombre' => 'required|string|max:255',

            // OPCIONALES
            'tipo_documento'    => 'nullable|string|max:255',
            'formato_el_pa'     => 'nullable|string|max:50',
            'area'              => 'nullable|string|max:255',
            'sharepoint_folder' => 'nullable|string|max:500',
            'estatus'           => 'required|in:vigente,baja',

            // VERSION / REVISION
            'version'         => 'nullable|string|max:100',
            'revision_actual' => 'nullable|string|max:100',

            // FECHAS
            'fecha_version'  => 'nullable|date',
            'fecha_revision' => 'nullable|date',

            // VIGENCIAS
            'vigencia_version_dias'  => 'nullable|integer|min:1',
            'vigencia_revision_dias' => 'nullable|integer|min:1',

            // SHAREPOINT
            'sharepoint_path'    => 'nullable|string|max:500',
            'sharepoint_file_id' => 'nullable|string|max:255',
            'sp_drive_id'        => 'nullable|string|max:255',
            'sp_item_id'         => 'nullable|string|max:255',
            'sp_web_url'         => 'nullable|string|max:1000',
            'sp_folder_path'     => 'nullable|string|max:500',

            // OTROS
            'liga_archivo'         => 'nullable|string|max:1000',
            'lugar_almacenamiento' => 'nullable|string|max:255',
            'observaciones_sgi'    => 'nullable|string',
        ]);

        DB::transaction(function () use ($request, $documento) {

            // Documento maestro
            $documento->update([
                'codigo'            => $request->codigo,
                'nombre'            => $request->nombre,
                'tipo_documento'    => $request->tipo_documento,
                'formato_el_pa'     => $request->formato_el_pa,
                'area'              => $request->area,
                'sharepoint_folder' => $request->sharepoint_folder,
                'estatus'           => $request->estatus,
            ]);

            // Versión vigente (fallback a la última si no hay relación)
            $version = $documento->versionVigente
                ?? $documento->versiones()->latest('id')->first();

            if (! $version) {
                return;
            }

            // Fechas base
            $fechaVersion = $request->fecha_version
                ? \Carbon\Carbon::parse($request->fecha_version)->startOfDay()
                : now()->startOfDay();

            $fechaRevision = $request->fecha_revision
                ? \Carbon\Carbon::parse($request->fecha_revision)->startOfDay()
                : now()->startOfDay();

            // Recalcular vencimientos
            $fechaVencimientoVersion = $request->vigencia_version_dias
                ? $fechaVersion->copy()->addDays((int) $request->vigencia_version_dias)
                : null;

            $fechaVencimientoRevision = $request->vigencia_revision_dias
                ? $fechaRevision->copy()->addDays((int) $request->vigencia_revision_dias)
                : null;

            $version->update([
                // Versionado
                'version'         => $request->version ?? $version->version,
                'revision_actual' => $request->revision_actual ?? $version->revision_actual,

                // Fechas
                'fecha_version'  => $fechaVersion,
                'fecha_revision' => $fechaRevision,

                // Vigencias
                'vigencia_version_dias'  => $request->vigencia_version_dias,
                'vigencia_revision_dias' => $request->vigencia_revision_dias,

                // Vencimientos
                'fecha_vencimiento_version'  => $fechaVencimientoVersion,
                'fecha_vencimiento_revision' => $fechaVencimientoRevision,

                // Archivos
                'liga_archivo'         => $request->liga_archivo,
                'lugar_almacenamiento' => $request->lugar_almacenamiento,

                // SharePoint
                'sharepoint_path'    => $request->sharepoint_path,
                'sharepoint_file_id' => $request->sharepoint_file_id,
                'sp_drive_id'        => $request->sp_drive_id,
                'sp_item_id'         => $request->sp_item_id,
                'sp_web_url'         => $request->sp_web_url,
                'sp_folder_path'     => $request->sp_folder_path,

                // SGI
                'observaciones_sgi' => $request->observaciones_sgi,
            ]);

            // Asegurar versión vigente
            $documento->update(['version_vigente_id' => $version->id]);
        });

        return redirect()
            ->route('documentos.show', $documento->id)
            ->with('success', 'Documento y versión actualizados correctamente.');
    }
}